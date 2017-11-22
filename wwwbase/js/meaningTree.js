$(function() {

  // when adding (sub)meanings of this type, we'll copy some extra
  // information from the selected meaning.
  const EXAMPLE_TYPE = 2;

  var stem = null;
  var anyChanges = false;
  var editable = $('#editable').length;
  var clickedButton = null; // which submit button was clicked?

  function init() {
    if (editable) {
      initEditable();
    }
    renumber();
  }

  function initEditable() {
    $('#meaningTree li, #stemNode li').click(meaningClick);
    $('#addMeaningButton').click(addMeaning);
    $('#addSubmeaningButton').click(addSubmeaning);
    $(document).on('click', '.deleteMeaningConfirmButton', deleteMeaning);
    $(document).on('click', '.deleteMeaningCancelButton', hidePopover);
    $('#meaningUpButton').click(meaningUp);
    $('#meaningDownButton').click(meaningDown);
    $('#meaningLeftButton').click(meaningLeft);
    $('#meaningRightButton').click(meaningRight);
    $('.deleteMeaningMention').click(deleteMeaningMention);

    stem = $('#stemNode li').detach();

    initSelect2('#editorSources', 'ajax/getSourcesById.php', {
      placeholder: 'adaugă o sursă...',
      width: '100%',
    });

    $('#relationType').change(selectRelationType).change();

    $('.editorObj').bind(
      'change keyup input paste', function() {
        anyChanges = true;
      });
    $('.meaningAction').click(function() {
      anyChanges = true;
    });

    $('#editorRep').textcomplete([
      {
        match: /(([-a-zăâîșț]+)((\[[0-9.]*)|(\[\[)))$/i,
        search: meaningMention,
        template: function(obj) {
          if (obj.treeDescription) {
            return 'arbore: <b>' + obj.treeDescription + '</b> ' +
              'intrare: <b>' + obj.entryDescription + '</b>';
          } else {
            return '<b>' + obj.description + ' ' + obj.breadcrumb + '</b> ' + obj.meaning;
          }
        },
        replace: function(value) {
          if (value.treeId) {
            return '$2[[' + value.treeId + ']]';
          } else {
            return '$2[' + value.meaningId + ']';
          }
        },
        index: 1,
      },
    ], {
      maxCount: 1000,
    });

    $('form').submit(saveEverything);
    $('button[type="submit"]').click(function() {
      clickedButton = $(this);
    });

    window.onbeforeunload = function(e) {
      return anyChanges
        ? 'Confirmați părăsirea paginii? Aveți modificări nesalvate.'
        : null;
    }
  }

  function renumberHelper(node, prefix) {
    var count = 0;
    node.children('li').each(function() {
      var c = $(this).children('.meaningContainer');
      var type = c.children('.type').text();

      var s;
      if (type == 0) {
        s = prefix + (++count) + '.';
        c.find('.bc').text(s);
        c.find('.typeName').empty();
      } else {
        s = MEANING_TYPE_NAMES[type];
        c.find('.bc').empty();
        c.find('.typeName').text(s);
      }

      $(this).children('ul').each(function() {
        renumberHelper($(this), s);
      });
    });
  }

  function renumber() {
    $('.meaningTree').each(function() {
      renumberHelper($(this), '');
    });
  }

  // Scrolls the .treeWrapper to bring the selected node into view
  function scroll() {
    var sel = $('#meaningTree li.selected');
    if (sel.length) {
      var w = $('.treeWrapper');

      // distance from selected meaning to tree wrapper
      var dist = sel.offset().top - w.offset().top + w.scrollTop();
      var height = w.height();

      // scroll the selected meaning so it's visible within .treeWrapper, at 1/3 from the top.
      w.animate({
        scrollTop: dist - height / 3,
      });
    }
  }

  // copy some data when we add a (sub)meaning
  function copyMeaningData(src, dest) {
    var type = src.find('> .meaningContainer > .type').html();
    dest.find('.type').html(type);

    if (type == EXAMPLE_TYPE) {
      const classes = [ '.sources', '.sourceIds'];
      classes.forEach(function(x) {
        var html = src.find('> .meaningContainer > ' + x).html();
        dest.find(x).html(html);
      });
    };
  }

  function addMeaning() {
    acceptMeaningEdit();
    var newNode = stem.clone(true);
    var sel = $('#meaningTree li.selected');
    if (sel.length) {
      copyMeaningData(sel, newNode);
      newNode.insertAfter(sel);
    } else {
      newNode.appendTo($('#meaningTree'));
    }
    newNode.click();
    renumber();
    scroll();
  }

  function addSubmeaning() {
    acceptMeaningEdit();
    var newNode = stem.clone(true);
    var sel = $('#meaningTree li.selected');

    copyMeaningData(sel, newNode);

    var ul = ensureUl(sel);
    newNode.prependTo(ul);
    newNode.click();
    renumber();
    scroll();
  }

  function deleteMeaning() {
    hidePopover();
    acceptMeaningEdit();
    $('#meaningTree li.selected').remove();
    $('.meaningAction').prop('disabled', true);
    clearEditor();
    renumber();
  }

  // The selected node becomes his father's next sibling.
  // If the selected node is a root node, nothing happens.
  function meaningLeft() {
    var node = $('#meaningTree li.selected');
    var parentLi = node.parent().parent('li');
    if (parentLi.length) {
      node.insertAfter(parentLi);
      renumber();
      scroll();
    }
  }

  // The selected node becomes the last child of its previous sibling.
  // If the selected node has no previous sibling, nothing happens.
  function meaningRight() {
    var node = $('#meaningTree li.selected');
    if (node.prev().length) {
      var ul = ensureUl(node.prev());
      node.appendTo(ul);
      renumber();
      scroll();
    }
  }

  // The selected node swaps places with its previous sibling.
  // If the selected node has no previous sibling, nothing happens.
  function meaningUp() {
    var node = $('#meaningTree li.selected');
    node.insertBefore(node.prev());
    renumber();
    scroll();
  }

  // The selected node swaps places with its next sibling.
  // If the selected node has no next sibling, nothing happens.
  function meaningDown() {
    var node = $('#meaningTree li.selected');
    node.insertAfter(node.next());
    renumber();
    scroll();
  }

  /* Ensures the node has a <ul> child, creates it if it doesn't, and returns the <ul> child. */
  function ensureUl(node) {
    if (!node.children('ul').length) {
      node.append('<ul></ul>');
    }
    return node.children('ul');
  }

  function meaningClick(event) {
    event.stopPropagation();
    acceptMeaningEdit();
    $('#meaningTree li.selected').removeClass('selected');
    $(this).addClass('selected');
    $('.meaningAction').prop('disabled', false);
    beginMeaningEdit();
  }

  // disable the delete button when the meaning or its submeanings have mentions
  function updateDeleteButtonState() {
    var noDelete = $('#meaningTree li.selected').find('[data-no-delete]').length;

    if (noDelete) {
      $('#deleteMeaningButton').prop({
        disabled: true,
        title: 'Acest sens nu poate fi șters deoarece există mențiuni despre el.',
      });
    } else {
      $('#deleteMeaningButton').removeProp('disabled').prop('title', '');
    }
  }

  function beginMeaningEdit() {
    var c = $('#meaningTree li.selected > .meaningContainer');

    $('.editorObj, .frequentObjects button').removeProp('disabled');
    updateDeleteButtonState();

    var type = c.find('.type').text();
    $('.editorType[value="' + type + '"]').prop('checked', true);

    $('#editorRep').val(c.find('.internalRep').text());

    $('#editorSources option').prop('selected', false);
    c.find('.sourceIds span').each(function() {
      var id = $(this).text();
      $('#editorSources option[value="' + id + '"]').prop('selected', true);
    });

    $('#editorTags').empty();
    c.find('.tagIds span').each(function() {
      var id = $(this).text();
      $('#editorTags').append(new Option('', id, true, true));
    });

    $('.editorRelation').html('');
    c.find('.relationIds').each(function() {
      var type = $(this).data('type');
      var select = $('.relationWrapper[data-type="' + type + '"] .editorRelation');
      $(this).find('span').each(function() {
        var t = $(this).text();
        select.append(new Option(t, t, true, true));
      });
    });

    $('#editorSources').trigger('change');
    refreshSelect2('#editorTags', 'ajax/getTagsById.php');
    refreshSelect2('.editorRelation', 'ajax/getTreesById.php');
  }

  function acceptMeaningEdit() {
    var c = $('#meaningTree li.selected > .meaningContainer');

    // Update the meaning type
    var type = parseInt($('.editorType:checked').val());
    c.find('.type').text(type);

    // Update internal and HTML definition
    var internalRep = $('#editorRep').val();
    c.find('.internalRep').text(internalRep);
    $.post(wwwRoot + 'ajax/htmlize.php',
           { internalRep: internalRep, sourceId: 0 },
           function(data) { c.find('.htmlRep').html(data); }
          );

    // Update sources and source IDs
    c.find('.sourceIds, .sources').text('');
    $('#editorSources option:selected').each(function() {
      c.find('.sources').append('<span class="meaningTag">' + $(this).text() + '</span>');
      c.find('.sourceIds').append('<span>' + $(this).val() + '</span>');
    });

    // Update tags and tag IDs
    c.find('.tagIds, .tags').text('');
    $('#editorTags option:selected').each(function() {
      c.find('.tags').append('<span class="meaningTag">' + $(this).text() + '</span>');
      c.find('.tagIds').append('<span>' + $(this).val() + '</span>');
    });

    // Update relations and tree IDs
    c.find('.relationIds').each(function() {
      var ids = $(this).text('');
      var type = ids.attr('data-type');
      var tags = c.find('.relation[data-type="' + type + '"]').text('');

      $('.relationWrapper[data-type="' + type + '"] option:selected').each(function() {
        tags.append('<span class="meaningTag">' + $(this).text() + '</span>');
        ids.append('<span>' + $(this).val() + '</span>');
      });
    });

    renumber();
  }

  function clearEditor() {
    $('.editorObj, .frequentObjects button').prop('disabled', true);
    $('.editorType').prop('checked', false);
    $('#editorRep').val('');
    $('#editorSources option:selected').removeAttr('selected');
    $('#editorSources').trigger('change');
    $('#editorTags option:selected').removeAttr('selected');
    $('#editorTags').trigger('change');
    $('.editorRelation').html('').trigger('change');
  }

  function selectRelationType() {
    $('.relationWrapper').hide();
    $('.relationWrapper[data-type="' + $(this).val() + '"]').show();
  }

  // Iterate a meaning tree node (<ul> element) recursively and collect meaning-related fields
  function meaningTreeWalk(node, results, level) {
    node.children('li').each(function() {
      var c = $(this).children('.meaningContainer');

      // Collect source, tag and relation IDs
      var sourceIds = c.find('.sourceIds span').map(function() {
        return $(this).text();
      }).get();

      var tagIds = c.find('.tagIds span').map(function() {
        return $(this).text();
      }).get();

      var relationIds = [];
      c.find('.relationIds').each(function() {
        relationIds[$(this).attr('data-type')] = $(this).find('span').map(function() {
          return $(this).text();
        }).get();
      });

      results.push({
        'id': c.find('.id').text(),
        'type': c.find('.type').text(),
        'level': level,
        'breadcrumb': c.find('.bc').text(),
        'internalRep': c.find('.internalRep').text(),
        'sourceIds': sourceIds,
        'tagIds': tagIds,
        'relationIds': relationIds,
      });
      $(this).children('ul').each(function() {
        meaningTreeWalk($(this), results, level + 1);
      });
    });
  }

  function saveEverything() {
    // allow saves, but still give warnings on other submit buttons
    if (clickedButton.attr('name') == 'saveButton') {
      window.onbeforeunload = null;

      acceptMeaningEdit();

      // convert meanings to JSON
      var results = new Array();
      meaningTreeWalk($('#meaningTree'), results, 0);
      $('input[name=jsonMeanings]').val(JSON.stringify(results));
    }
  }

  function meaningMention(term, callback) {
    parts = term.split('[');

    var url = (parts.length == 2)
        ? wwwRoot + 'ajax/meaningMention.php'
        : wwwRoot + 'ajax/treeMention.php';

    $.getJSON(url, { form: parts[0], qualifier: parts[1] })
      .done(function(resp) {
        callback(resp); // `resp` must be an Array
      })
      .fail(function () {
        callback([]); // Callback must be invoked even if something went wrong.
      });
  }

  function deleteMeaningMention() {
    if (confirm('Confirmați ștergerea mențiunii?')) {
      var mentionId = $(this).data('mentionId');
      var meaningId = $(this).data('meaningId');
      var table = $(this).closest('table');

      // make Ajax call to delete the mention
      $.get(sprintf('%sajax/deleteMeaningMention.php?id=%s', wwwRoot, mentionId));

      // delete the row
      $(this).closest('tr').hide('normal', function() {
        $(this).remove();

        // remove the 'data-no-delete' attribute from the meaning if there are no more mentions
        // of this meaning
        var remaining = table.find(sprintf('a[data-meaning-id="%d"]', meaningId)).length;
        if (!remaining) {
          var div = $(sprintf('#meaningTree div[data-meaning-id="%d"]', meaningId));
          div.removeAttr('data-no-delete');

          // enable the delete meaning button if the currently selected row became deletable
          updateDeleteButtonState();
        }
      });

    }
    return false;
  }

  // Compensate for a bug in Bootstrap 3.3.7:
  // https://github.com/twbs/bootstrap/issues/16732
  function hidePopover() {
    $('.popover').fadeOut('slow').popover('hide');
    if ($.fn.popover.Constructor.VERSION == "3.3.7") {
      $('[data-toggle="popover"]').on("hidden.bs.popover", function() {
        $(this).data("bs.popover").inState.click = false
      });
    }
  }

  init();
});
