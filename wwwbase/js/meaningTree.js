$(function() {

  var stem = null;
  var anyChanges = false;
  var editable = $('#editable').length;

  function init() {
    if (editable) {
      initEditable();
    }
    meaningTreeRenumber();
  }

  function initEditable() {
    $('#meaningTree li, #stemNode li').click(meaningClick);
    $('#addMeaningButton').click(addMeaning);
    $('#addSubmeaningButton').click(addSubmeaning);
    $('#deleteMeaningButton').click(deleteMeaning);
    $('#meaningUpButton').click(meaningUp);
    $('#meaningDownButton').click(meaningDown);
    $('#meaningLeftButton').click(meaningLeft);
    $('#meaningRightButton').click(meaningRight);

    stem = $('#stemNode li').detach();

    initSelect2('#editorSources', 'ajax/getSourcesById.php', {
      placeholder: 'adaugă o sursă...',
      width: '100%',
    });

    initSelect2('#editorTags', 'ajax/getTagsById.php', {
      ajax: { url: wwwRoot + 'ajax/getTags.php' },
      minimumInputLength: 1,
      placeholder: 'adaugă o etichetă...',
      width: '100%',
    });

    initSelect2('.editorRelation', 'ajax/getTreesById.php', {
      ajax: { url: wwwRoot + 'ajax/getTrees.php' },
      minimumInputLength: 1,
      width: '100%',
    });

    $('#relationType').change(selectRelationType).change();
    $('#editorRep, #editorEtymology, #editorComment, #editorSources, ' +
      '#editorTags, .editorRelation').bind(
        'change keyup input paste', function() {
          anyChanges = true;
        });

    $('#editorRep').textcomplete([
      {
        match: /(([a-zăâîșț]+)\[[0-9.]*)$/i,
        search: meaningMention,
        template: function(obj) {
          return '<b>' + obj.description + ' ' + obj.breadcrumb + ':</b> ' + obj.meaning;
        },
        replace: function(value) {
          return '$2[' + value.meaningId + ']';
        },
        index: 1,
        maxCount: 5,
      }
    ]);

    $('#editMeaningAcceptButton').click(acceptMeaningEdit);
    $('#editMeaningCancelButton').click(endMeaningEdit);

    $('form').submit(saveEverything);
  }

  function meaningTreeRenumberHelper(node, prefix) {
    node.children('li').each(function(i) {
      var c = $(this).children('.meaningContainer');
      var s = prefix + (prefix ? '.' : '') + (i + 1);
      c.find('.bc').text(s);
      $(this).children('ul').each(function() {
        meaningTreeRenumberHelper($(this), s);
      });
    });
  }

  function meaningTreeRenumber() {
    $('.meaningTree').each(function() {
      meaningTreeRenumberHelper($(this), '');
    });
  }

  function addMeaning() {
    if (!meaningEditorUnchanged()) {
      return false;
    }
    var newNode = stem.clone(true);
    var node = $('#meaningTree li.selected');
    if (node.length) {
      newNode.insertAfter(node);
    } else {
      newNode.appendTo($('#meaningTree'));
    }
    newNode.click();
    meaningTreeRenumber();
  }

  function addSubmeaning() {
    if (!meaningEditorUnchanged()) {
      return false;
    }
    var newNode = stem.clone(true);
    var ul = ensureUl($('#meaningTree li.selected'));
    newNode.appendTo(ul);
    newNode.click();
    meaningTreeRenumber();
  }

  function deleteMeaning() {
    if (!meaningEditorUnchanged()) {
      return false;
    }
    var node = $('#meaningTree li.selected');
    var numChildren = node.children('ul').children().length;
    if (!numChildren || confirm('Confirmați ștergerea sensului și a tuturor subsensurilor?')) {
      node.remove();
      enableMeaningActions(false);
      meaningTreeRenumber();
    }
  }

  // The selected node becomes his father's next sibling.
  // If the selected node is a root node, nothing happens.
  function meaningLeft() {
    var node = $('#meaningTree li.selected');
    var parentLi = node.parent().parent('li');
    if (parentLi.length) {
      node.insertAfter(parentLi);
      meaningTreeRenumber();
    }
  }

  // The selected node becomes the last child of its previous sibling.
  // If the selected node has no previous sibling, nothing happens.
  function meaningRight() {
    var node = $('#meaningTree li.selected');
    if (node.prev().length) {
      var ul = ensureUl(node.prev());
      node.appendTo(ul);
      meaningTreeRenumber();
    }
  }

  // The selected node swaps places with its previous sibling.
  // If the selected node has no previous sibling, nothing happens.
  function meaningUp() {
    var node = $('#meaningTree li.selected');
    node.insertBefore(node.prev());
    meaningTreeRenumber();
  }

  // The selected node swaps places with its next sibling.
  // If the selected node has no next sibling, nothing happens.
  function meaningDown() {
    var node = $('#meaningTree li.selected');
    node.insertAfter(node.next());
    meaningTreeRenumber();
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
    if (meaningEditorUnchanged()) {
      $('#meaningTree li.selected').removeClass('selected');
      $(this).addClass('selected');
      enableMeaningActions(true);
      beginMeaningEdit();
    }
  }

  function enableMeaningActions(enabled) {
    $('#addSubmeaningButton, #deleteMeaningButton, #meaningUpButton, ' +
      '#meaningDownButton, #meaningLeftButton, #meaningRightButton')
      .prop('disabled', !enabled);
  }

  function meaningEditorUnchanged(node) {
    if (!anyChanges) {
      return true;
    }
    if (confirm('Aveți deja un sens în curs de modificare. Confirmați renunțarea la modificări?')) {
      anyChanges = false;
      return true;
    }
    return false;
  }

  function beginMeaningEdit() {
    var c = $('#meaningTree li.selected > .meaningContainer');

    $('#editorRep, #editorEtymology, #editorComment, #editorSources, ' +
      '#editorTags, #relationType, .editorRelation, #editMeaningAcceptButton, ' +
      '#editMeaningCancelButton').removeProp('disabled');
    $('#editorRep').val(c.find('.internalRep').text());
    $('#editorEtymology').val(c.find('.internalEtymology').text());
    $('#editorComment').val(c.find('.internalComment').text());

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

    $.when(
      $('#editorSources').trigger('change'),
      refreshSelect2('#editorTags', 'ajax/getTagsById.php'),
      refreshSelect2('.editorRelation', 'ajax/getTreesById.php')
    ).done(function() {
      anyChanges = false;
    });
  }

  function acceptMeaningEdit() {
    var c = $('#meaningTree li.selected > .meaningContainer');

    // Update internal and HTML definition
    var internalRep = $('#editorRep').val();
    c.find('.internalRep').text(internalRep);
    $.post(wwwRoot + 'ajax/htmlize.php',
           { internalRep: internalRep, sourceId: 0 },
           function(data) { c.find('.htmlRep').html(data); }
          );

    // Update internal and HTML etymology
    var internalEtymology = $('#editorEtymology').val();
    c.find('.internalEtymology').text(internalEtymology);
    $.post(wwwRoot + 'ajax/htmlize.php',
           { internalRep: internalEtymology, sourceId: 0 },
           function(data) { c.find('.htmlEtymology').html(data); }
          );

    // Update internal and HTML comment
    var internalComment = $('#editorComment').val();
    c.find('.internalComment').text(internalComment);
    $.post(wwwRoot + 'ajax/htmlize.php',
           { internalRep: internalComment, sourceId: 0 },
           function(data) { c.find('.htmlComment').html(data); },
           'text');

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

    anyChanges = false;
  }

  function endMeaningEdit() {
    $('#editorRep, #editorEtymology, #editorComment, #editorSources, ' +
      '#editorTags, #relationType, .editorRelation, #editMeaningAcceptButton, ' +
      '#editMeaningCancelButton').prop('disabled', true);
    $('#editorRep').val('');
    $('#editorEtymology').val('');
    $('#editorComment').val('');
    $('#editorSources option:selected').removeAttr('selected');
    $('#editorSources').trigger('change');
    $('#editorTags option:selected').removeAttr('selected');
    $('#editorTags').trigger('change');
    $('.editorRelation').html('').trigger('change');
    anyChanges = false;
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
        'level': level,
        'breadcrumb': c.find('.bc').text(),
        'internalRep': c.find('.internalRep').text(),
        'internalEtymology': c.find('.internalEtymology').text(),
        'internalComment': c.find('.internalComment').text(),
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
    if (anyChanges) {
      acceptMeaningEdit();
    }

    // convert meanings to JSON
    var results = new Array();
    meaningTreeWalk($('#meaningTree'), results, 0);
    $('input[name=jsonMeanings]').val(JSON.stringify(results));
  }

  function meaningMention(term, callback) {
    parts = term.split('[');
    $.getJSON(wwwRoot + 'ajax/meaningMention', { form: parts[0], qualifier: parts[1] })
      .done(function(resp) {
        callback(resp); // `resp` must be an Array
      })
      .fail(function () {
        callback([]); // Callback must be invoked even if something went wrong.
      });
  }

  init();
});
