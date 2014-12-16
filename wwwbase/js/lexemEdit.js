var struct_anyChanges = false;
var COOKIE_NAME = 'lexemEdit';
$.cookie.json = true;

function lexemEditInit() {
  $('#meaningTree li, #stemNode li').click(meaningClick);
  $('#addMeaningButton').click(addMeaning);
  $('#addSubmeaningButton').click(addSubmeaning);
  $('#deleteMeaningButton').click(deleteMeaning);
  $('#meaningUpButton').click(meaningUp);
  $('#meaningDownButton').click(meaningDown);
  $('#meaningLeftButton').click(meaningLeft);
  $('#meaningRightButton').click(meaningRight);
  meaningTreeRenumber();

  $('#editorSources').select2({
    matcher: sourceMatcher,
    placeholder: 'adaugă o sursă...',
    width: '315px',
  });
  $('#editorSources').select2('enable', false);

  $('#editorTags').select2({
    placeholder: 'adaugă o etichetă...',
    width: '315px',
  });
  $('#editorTags').select2('enable', false);

  $('.editorRelation').select2({
    ajax: struct_lexemAjax,
    initSelection: select2InitSelection,
    minimumInputLength: 1,
    multiple: true,
    width: '275px',
  });
  $('.editorRelation').select2('enable', false);

  $('#relationType').change(selectRelationType).change();
  $('#editorInternalRep, #editorInternalComment, #editorSources, #editorTags, .editorRelation').bind(
    'change keyup input paste', function() { struct_anyChanges = true; });

  $('#editorInternalRep').textcomplete([
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

  $('#variantIds').select2({
    ajax: struct_lexemAjax,
    initSelection: select2InitSelectionAjax,
    minimumInputLength: 1,
    multiple: true,
    width: '173px',
  });

  $('#variantOfId').select2({
    ajax: struct_lexemAjax,
    allowClear: true,
    initSelection: select2InitSelectionAjaxSingle,
    minimumInputLength: 1,
    placeholder: '(opțional)',
    width: '173px',
  });

  $('#associateDefinitionId').select2({
    ajax: struct_definitionAjax,
    formatResult: function(item) {
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

  struct_lexemSourceIds = {
    data: sourceMap,
    matcher: sourceMatcher,
    multiple: true,
    placeholder: 'surse care atestă flexiunea',
    width: '250px',
  };
  // Disable the select2 when the HTML select is disabled. This doesn't happen by itself.
  $('#paradigmTabs .lexemSourceIds')
    .select2(struct_lexemSourceIds);

  struct_similarLexem = {
    ajax: struct_lexemAjax,
    minimumInputLength: 1,
    placeholder: 'sau indicați un lexem similar',
    width: '250px',
  };
  $('#paradigmTabs .similarLexem').select2(struct_similarLexem).on('change', similarLexemChange);

  $('.mergeLexem').click(mergeLexemButtonClick);

  var t = $('#paradigmTabs');
  t.tabs();
  if (canEdit.paradigm) {
    t.find('.ui-tabs-nav').sortable({
      axis: "x",
      stop: reorderLexemModelTabs
    });
  }
  $('#addLexemModel').click(addLexemModelTab);
  t.on('click', '.ui-icon-close', closeLexemModelTab);
  t.on('click', '.fakeCheckbox', toggleIsLoc);

  wmInit();
}

function sourceMatcher(term, text) {
  term = term.replace('ş', 'ș').replace('Ş', 'Ș').replace('ţ', 't').replace('Ţ', 'Ț');
  return text.toUpperCase().indexOf(term.toUpperCase()) != -1;
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
  $('#addSubmeaningButton, #deleteMeaningButton, #meaningUpButton, #meaningDownButton, #meaningLeftButton, #meaningRightButton')
    .prop('disabled', !enabled);
}

function meaningEditorUnchanged(node) {
  return !struct_anyChanges || confirm('Aveți deja un sens în curs de modificare. Confirmați renunțarea la modificări?');
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
  struct_anyChanges = false;
  var c = $('#meaningTree li.selected > .meaningContainer');

  $('#editorInternalRep, #editorInternalComment, #relationType, #editMeaningAcceptButton, #editMeaningCancelButton').removeProp('disabled');
  $('#editorInternalRep').val(c.find('.internalRep').text());
  $('#editorInternalComment').val(c.find('.internalComment').text());
  $('#editorSources').select2('val', c.find('.sourceIds').text().split(','));
  $('#editorSources').select2('enable');
  $('#editorTags').select2('val', c.find('.meaningTagIds').text().split(','));
  $('#editorTags').select2('enable');

  c.find('.relationIds').each(function() {
    var type = $(this).attr('data-type');
    var relationIds = $(this).text().split(',');
    var relations = c.find('.relation[data-type="' + type + '"] .tag');
    $('.relationWrapper[data-type="' + type + '"] .editorRelation').select2('data', relations.map(function(index) {
      return { id: relationIds[index], text: $(this).text() };
    })).select2('enable');
  });
}

function acceptMeaningEdit() {
  struct_anyChanges = false;
  var c = $('#meaningTree li.selected > .meaningContainer');

  // Update internal and HTML definition
  var internalRep = $('#editorInternalRep').val();
  c.find('.internalRep').text(internalRep);
  $.post(wwwRoot + 'ajax/htmlize.php',
         { internalRep: internalRep, sourceId: 0 },
         function(data) { c.find('.htmlRep').html(data); }
        );

  // Update internal and HTML comment
  var internalComment = $('#editorInternalComment').val();
  c.find('.internalComment').text(internalComment);
  $.post(wwwRoot + 'ajax/htmlize.php',
         { internalRep: internalComment, sourceId: 0 },
         function(data) { c.find('.htmlComment').html(data); },
        'text');

  // Update sources and sourceIds
  var sourceIds = $('#editorSources').val();
  c.find('.sourceIds').text(sourceIds ? sourceIds.join(',') : '');
  c.find('.sources').text('');
  $('#editorSources option:selected').each(function() {
    c.find('.sources').append('<span class="tag">' + $(this).text() + '</span>');
  });

  // Update meaning tags and meaningIds
  var meaningTagIds = $('#editorTags').val();
  c.find('.meaningTagIds').text(meaningTagIds ? meaningTagIds.join(',') : '');
  c.find('.meaningTags').text('');
  $('#editorTags option:selected').each(function() {
    c.find('.meaningTags').append('<span class="tag">' + $(this).text() + '</span>');
  });

  // Update relation tags and relationIds
  c.find('.relationIds').each(function() {
    var type = $(this).attr('data-type');
    var relationData = $('.relationWrapper[data-type="' + type + '"] .editorRelation').select2('data');
    $(this).text($.map(relationData, function(rec, i) { return rec.id; }));
    var relations = c.find('.relation[data-type="' + type + '"]');
    relations.text('');
    $.map(relationData, function(rec, i) {
      relations.append('<span class="tag">' + rec.text + '</span>');
    });
  });
}

function endMeaningEdit() {
  struct_anyChanges = false;
  $('#editorInternalRep, #editorInternalComment, #editMeaningAcceptButton, #editMeaningCancelButton').prop('disabled', 'disabled');
  $('#relationType').attr('disabled', 'disabled');
  $('#editorInternalRep').val('');
  $('#editorInternalComment').val('');
  $('#editorSources').select2('val', []);
  $('#editorSources').select2('enable', false);
  $('#editorTags').select2('val', []);
  $('#editorTags').select2('enable', false);
  $('.editorRelation').select2('data', []);
  $('.editorRelation').select2('enable', false);
}

function selectRelationType() {
  $('.relationWrapper').hide();
  $('.relationWrapper[data-type="' + $(this).val() + '"]').show();
}

// Iterate a meaning tree node (<ul> element) recursively and collect meaning-related fields
function meaningTreeWalk(node, results, level) {
  node.children('li').each(function() {
    var c = $(this).children('.meaningContainer');
    // Collect the relationIds
    var relationIds = [];
    c.find('.relationIds').each(function() {
      relationIds[$(this).attr('data-type')] = $(this).text();
    });
    results.push({ 'id': c.find('.id').text(),
                   'level': level,
                   'breadcrumb': c.find('.breadcrumb').text(),
                   'internalRep': c.find('.internalRep').text(),
                   'internalComment': c.find('.internalComment').text(),
                   'sourceIds': c.find('.sourceIds').text(),
                   'meaningTagIds': c.find('.meaningTagIds').text(),
                   'relationIds': relationIds,
                 });
    $(this).children('ul').each(function() {
      meaningTreeWalk($(this), results, level + 1);
    });
  });
}

function saveEverything() {
  if (struct_anyChanges) {
    acceptMeaningEdit();
  }
  var results = new Array();
  meaningTreeWalk($('#meaningTree'), results, 0);
  $('input[name=jsonMeanings]').val(JSON.stringify(results));
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

function similarLexemChange(e) {
  var mtSelect = $(this).siblings('select[name="modelType[]"]');
  var mnSelect = $(this).prevAll('select[name="modelNumber[]"]');
  var restriction = $(this).siblings('input[name="restriction[]"]');
  var span = $(this).closest('*[data-model-dropdown]');

  var url = wwwRoot + 'ajax/getModelByLexemId.php?id=' + e.val;
  $.get(url, null, null, 'json')
    .done(function(data) {
      mtSelect.data('selected', data.modelType);
      mnSelect.data('selected', data.modelNumber);
      updateModelTypeList(span);
      restriction.val(data.restriction);
    });
}

function addLexemModelTab() {
  var tabIndex = $('#paradigmTabs > ul li').length;
  var tabId = 'lmTab_' + randomDigits(9);
  var tabContents = $('#lmTab_stem').clone(true).attr('id', tabId);
  var li = $('#paradigmTabs > ul li').clone().first();
  li.find('a').text('nou').attr('href', '#' + tabId);
  $('#paradigmTabs > ul').append(li);
  $('#paradigmTabs').append(tabContents);
  $('#paradigmTabs').tabs('refresh');
  $('#paradigmTabs').tabs('option', 'active', tabIndex);
  $('#' + tabId).find('select[data-model-type]').val('T'); // clone() doesn't copy selectedness
  $('#' + tabId).find('.similarLexem').select2(struct_similarLexem).on('change', similarLexemChange);
  $('#' + tabId).find('.lexemSourceIds')
    .select2(struct_lexemSourceIds)
    .select2('readonly', $('.lexemSourceIds').is('[readonly]'));
  return false;
}

function closeLexemModelTab() {
  var tabId = $(this).prev('a').attr('href');

  if ($('#paradigmTabs > ul li').length == 1) {
    alert('Nu puteți șterge unicul model.');
  } else {
    $(this).closest('li').remove();
    $(tabId).remove();
    $('#paradigmTabs').tabs('refresh');
  }
}

// Order the tab panels in accordance with the <li> order. This doesn't sem to happen automatically
function reorderLexemModelTabs() {
  var current = $('#paradigmTabs > ul');
  $('#paradigmTabs > ul li a').each(function() {
    var tabId = $(this).attr('href');
    var tab = $(tabId);
    tab.insertAfter(current);
    current = tab;
  });
  $('#paradigmTabs').tabs("refresh");
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

// Sets the coordinates for each window based on the cookie (if available) or on HTML5 attributes of the original box
function wmSetCoordinates() {
  var props = ['left', 'top', 'width', 'height'];
  var cookie = $.cookie(COOKIE_NAME);

  $('.window').each(function() {
    var id = $(this).attr('data-id');
    if (cookie) {
      for (var i = 0; i < props.length; i++) {
        $(this).css(props[i], cookie[id][props[i]] + 'px');
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
            value += parseInt($('#wmCanvas').offset().left);
          } else if (props[i] == 'top') {
            value += parseInt($('#wmCanvas').offset().top);
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
      params.left = p.left;
      params.top = p.top;
      params.width = p.width;
      params.height = p.height;
    } else {
      params.left = $(this).offset().left;
      params.top = $(this).offset().top;
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
