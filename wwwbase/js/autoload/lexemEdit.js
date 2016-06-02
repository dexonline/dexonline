$(function() {
  var anyChanges = false;
  var COOKIE_NAME = 'lexemEdit';
  $.cookie.json = true;

  var lexemSourceOptions = {
    ajax: { url: wwwRoot + 'ajax/getSources.php' },
    minimumInputLength: 1,
    placeholder: 'surse care atestă flexiunea',
    width: '250px',
  };
  var similarLexemOptions = {
    ajax: { url: wwwRoot + 'ajax/getLexems.php' },
    minimumInputLength: 1,
    placeholder: 'sau indicați un lexem similar',
    width: '250px',
  };

  function init() {
    $('#meaningTree li, #stemNode li').click(meaningClick);
    $('#addMeaningButton').click(addMeaning);
    $('#addSubmeaningButton').click(addSubmeaning);
    $('#deleteMeaningButton').click(deleteMeaning);
    $('#meaningUpButton').click(meaningUp);
    $('#meaningDownButton').click(meaningDown);
    $('#meaningLeftButton').click(meaningLeft);
    $('#meaningRightButton').click(meaningRight);
    meaningTreeRenumber();

    initSelect2('#editorSources', 'ajax/getSourcesById.php', {
      placeholder: 'adaugă o sursă...',
      width: '315px',
    });

    $('#editorTags').select2({
      placeholder: 'adaugă o etichetă...',
      width: '315px',
    });

    initSelect2('.editorRelation', 'ajax/getLexemsById.php', {
      ajax: { url: wwwRoot + 'ajax/getLexems.php' },
      minimumInputLength: 1,
      width: '275px',
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
          return '<b>' + obj.lexem + ' ' + obj.breadcrumb + ':</b> ' + obj.meaning;
        },
        replace: function(value) {
          return '$2[' + value.meaningId + ']';
        },
        index: 1,
        maxCount: 5
      }
    ]);

    initSelect2('#entryId', 'ajax/getEntriesById.php', {
      ajax: { url: wwwRoot + 'ajax/getEntries.php' },
      allowClear: true,
      minimumInputLength: 1,
      templateSelection: formatEntryWithEditLink,
      width: '180px',
    });

    initSelect2('#variantIds', 'ajax/getLexemsById.php', {
      ajax: { url: wwwRoot + 'ajax/getLexems.php' },
      minimumInputLength: 1,
      width: '180px',
    });

    initSelect2('#variantOfId', 'ajax/getLexemsById.php', {
      ajax: { url: wwwRoot + 'ajax/getLexems.php' },
      allowClear: true,
      minimumInputLength: 1,
      placeholder: '(opțional)',
      width: '180px',
    });

    initSelect2('#structuristId', 'ajax/getUsersById.php', {
      ajax: createUserAjaxStruct(PRIV_STRUCT),
      allowClear: true,
      minimumInputLength: 3,
      placeholder: '(opțional)',
      width: '180px',
    });
    
    $('#associateDefinitionId').select2({
      ajax: { url: wwwRoot + 'ajax/wotdGetDefinitions.php', },
      templateResult: function(item) {
        return item.text + ' (' + item.source + ') [' + item.id + ']';
      },
      minimumInputLength: 1,
      placeholder: 'asociază o definiție',
      width: '400px',
    });

    $('#editMeaningAcceptButton').click(acceptMeaningEdit);
    $('#editMeaningCancelButton').click(endMeaningEdit);
    $('.lexemEditSaveButton').click(saveEverything);
    $('.toggleRepLink').click(toggleRepClick);
    $('.toggleRepSelect').click(toggleRepChange);
    $('.toggleStructuredLink').click(toggleStructuredClick);
    $('#defFilterSelect').click(defFilterChange);

    initSelect2('.paradigmFields .sourceIds', 'ajax/getSourcesById.php', lexemSourceOptions);

    $('.similarLexem')
      .select2(similarLexemOptions)
      .on('change', similarLexemChange);

    $('.mergeLexem').click(mergeLexemButtonClick);

    $('.fakeCheckbox').click(toggleIsLoc);

    wmInit();
  }

  function addMeaning() {
    if (!meaningEditorUnchanged()) {
      return false;
    }
    var newNode = $('#stemNode li').clone(true);
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
    var newNode = $('#stemNode li').clone(true);
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
    return !anyChanges || confirm('Aveți deja un sens în curs de modificare. Confirmați renunțarea la modificări?');
  }

  function meaningTreeRenumberHelper(node, prefix) {
    node.children('li').each(function(i) {
      var c = $(this).children('.meaningContainer');
      var s = prefix + (prefix ? '.' : '') + (i + 1);
      c.find('.breadcrumb').text(s);
      $(this).children('ul').each(function() {
        meaningTreeRenumberHelper($(this), s);
      });
    });
  }

  function meaningTreeRenumber() {
    meaningTreeRenumberHelper($('#meaningTree'), '');
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

    $('#editorTags option').prop('selected', false);
    c.find('.tagIds span').each(function() {
      var id = $(this).text();
      $('#editorTags option[value="' + id + '"]').prop('selected', true);
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
      $('#editorTags').trigger('change'),
      $('#editorSources').trigger('change'),
      refreshSelect2('.editorRelation', 'ajax/getLexemsById.php')
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
      c.find('.sources').append('<span class="tag">' + $(this).text() + '</span>');
      c.find('.sourceIds').append('<span>' + $(this).val() + '</span>');
    });

    // Update tags and tag IDs
    c.find('.tagIds, .tags').text('');
    $('#editorTags option:selected').each(function() {
      c.find('.tags').append('<span class="tag">' + $(this).text() + '</span>');
      c.find('.tagIds').append('<span>' + $(this).val() + '</span>');
    });

    // Update relations and lexem IDs
    c.find('.relationIds').each(function() {
      var ids = $(this).text('');
      var type = ids.attr('data-type');
      var tags = c.find('.relation[data-type="' + type + '"]').text('');

      $('.relationWrapper[data-type="' + type + '"] option:selected').each(function() {
        tags.append('<span class="tag">' + $(this).text() + '</span>');
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

      results.push({ 'id': c.find('.id').text(),
                     'level': level,
                     'breadcrumb': c.find('.breadcrumb').text(),
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

    // allow disabled selects to submit (they should have been readonly,
    // not disabled, but Select2 4.0 doesn't use readonly).
    $('#variantOfId, #variantIds').prop('disabled', false);

    $('#meaningForm').submit();
  }

  /* Definitions can be shown as internal or HTML notation, with abbreviations expanded or collapsed. This gives rise to four combinations, coded on
   * two bits each. Clicking on the "show / hide HTML" and show / hide abbreviations" fiddles some bits and sets the "visible" class on the
   * appropriate div. */
  function toggleRepClick() {
    // Hide the old definition
    var oldActive = $(this).closest('.defDetails').prevAll('[data-active]');
    oldActive.slideToggle().removeAttr('data-active');

    // Recalculate the code and show the new definition
    var code = oldActive.attr('data-code') ^ $(this).attr('data-order');
    var newActive = $(this).closest('.defDetails').prevAll('[data-code=' + code + ']');
    newActive.slideToggle().attr('data-active', '');

    // Toggle the link text and data-value attribute
    var tmp = $(this).text();
    $(this).text($(this).attr('data-other-text'));
    $(this).attr('data-other-text', tmp);
    $(this).attr('data-value', 1 - $(this).attr('data-value'));
    return false;
  }

  /* User has selected a value from the text/html select. Toggle all definitions that aren't in that state already. */
  function toggleRepChange() {
    var order = $(this).attr('data-order');
    var value = 1 - $(this).val(); // Links that have this BAD value need to be clicked.
    $('.toggleRepLink[data-order=' + order + '][data-value=' + value + ']').click();
  }

  function toggleStructuredClick() {
    var parent = $(this).closest('.defWrapper');
    var id = parent.attr('id').split('_')[1];
    if (parent.hasClass('structured')) {
      $(this).text('nestructurată');
      parent.removeClass('structured');
      parent.addClass('unstructured');
      var value = 0;
    } else {
      $(this).text('structurată');
      parent.removeClass('unstructured');
      parent.addClass('structured');
      var value = 1;
    }
    $.get(wwwRoot + 'ajax/setDefinitionStructured.php?id=' + id + '&value=' + value);
    return false;
  }

  function defFilterChange() {
    if ($(this).val() == 'structured') {
      $('.defWrapper.unstructured').hide('slow');
    } else {
      $('.defWrapper.unstructured').show('slow');
    }
    if ($(this).val() == 'unstructured') {
      $('.defWrapper.structured').hide('slow');
    } else {
      $('.defWrapper.structured').show('slow');
    }
  }

  function mergeLexemButtonClick() {
    var id = $(this).attr('id').split('_')[1];
    $('input[name=mergeLexemId]').val(id);
  }

  function similarLexemChange() {
    var lexemId = $(this).find('option:selected').val();
    var url = wwwRoot + 'ajax/getModelByLexemId.php?id=' + lexemId;
    $.get(url)
      .done(function(data) {
        $('select[name="modelType"]').data('selected', data.modelType);
        $('select[name="modelNumber"]').data('selected', data.modelNumber);
        updateModelTypeList($('*[data-model-dropdown]'));
        $('input[name="restriction"]').val(data.restriction);
      });
  }

  function toggleIsLoc() {
    var hidden = $(this).prev();
    hidden.val(1 - hidden.val());
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

  // Initializes the window manager
  function wmInit() {
    $('.box').each(function() {
      // Convert each box to a window
      var $w = $().WM('open');
      $w.find('.titlebartext').text($(this).attr('data-title'));
      $w.attr('data-id', $(this).attr('data-id'));
      $w.find('.windowcontent').append($(this).children());
      $('#wmCanvas').append($w);
    });
    wmSetCoordinates();

    // Set some handlers for moving, resizing, and resetting the interface
    $('#resizerproxy').mouseup(wmSetCookie);
    $('#moverproxy').mouseup(wmSetCookie);
    $('.minimizebut, .maximizebut, .restorebut').click(wmSetCookie);
    $('.windowtitlebar').dblclick(wmSetCookie);
    $('#interfaceResetLink').click(wmInterfaceReset);
  }

  // Sets the coordinates for each window based on the cookie (if available) or on HTML5 attributes of the original box.
  // Everything is relative to the coords of #wmCanvas.
  function wmSetCoordinates() {
    var props = ['left', 'top', 'width', 'height'];
    var cookie = $.cookie(COOKIE_NAME);
    var canvasShift = {
      left: parseInt($('#wmCanvas').offset().left),
      top: parseInt($('#wmCanvas').offset().top),
      width: 0,
      height: 0,
    };

    $('.window').each(function() {
      var id = $(this).attr('data-id');
      if (cookie) {
        for (var i = 0; i < props.length; i++) {
          var coord = cookie[id][props[i]] + canvasShift[props[i]];
          $(this).css(props[i], coord + 'px');
        }
        if (cookie[id].minimized) {
          $(this).WM('minimize');
        }
      } else {
        // No cookie - load the corresponding box
        var box = $('.box[data-id="' + id + '"]');
        for (var i = 0; i < props.length; i++) {
          if (typeof(box.attr('data-' + props[i])) != 'undefined') {
            var value = parseInt(box.attr('data-' + props[i]));
            if (props[i] == 'left') {
              value += canvasShift.left;
            } else if (props[i] == 'top') {
              value += canvasShift.top;
            }
            $(this).css(props[i], value + 'px');
          }
        }
        if (box.attr('data-minimized')) {
          $(this).WM('minimize');
        } else if ($(this).hasClass('minimized')) {
          $(this).WM('restore');
        }
      }
    });
    if (cookie) {
      $('.window[data-id="' + cookie.focused + '"]').WM('raise');
    } else {
      // Raise minimized windows for clean interfaces, so that the users know they're there
      $('.window.minimized').each(function() { $(this).WM('raise'); });
    }
  }

  function wmSetCookie() {
    var data = {};
    $('.window').each(function() {
      var params = { minimized: $(this).hasClass('minimized') };
      if (params.minimized) {
        var p = $(this).data('oldPos');
        params.left = p.left - $('#wmCanvas').offset().left;
        params.top = p.top - $('#wmCanvas').offset().top;
        params.width = p.width;
        params.height = p.height;
      } else {
        params.left = $(this).offset().left - $('#wmCanvas').offset().left;
        params.top = $(this).offset().top - $('#wmCanvas').offset().top;
        params.width = $(this).width();
        params.height = $(this).height();
      }
      data[$(this).attr('data-id')] = params;
    });
    data['focused'] = $('.window.focused').attr('data-id');
    $.cookie(COOKIE_NAME, data, { expires: 365 });
  }

  function wmInterfaceReset() {
    $.removeCookie(COOKIE_NAME);
    wmSetCoordinates();
    return false;
  }

  function confirmDissociateDefinition(id) {
    return confirm('Doriți să disociați definiția ' + id + ' de acest lexem?');
  }

  init();
});
