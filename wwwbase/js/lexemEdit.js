struct_anyChanges = false;

function lexemEditInit() {
  $('#meaningTree').tree({
    animate: true,
    dnd: true,
    lines: true,
    onBeforeSelect: meaningEditorUnchanged,
    onSelect: beginMeaningEdit,
  });
  $('#addMeaningButton').click(addMeaning);
  $('#addSubmeaningButton').click(addSubmeaning);
  $('#deleteMeaningButton').click(deleteMeaning);

  $('#editorSources').select2({
    placeholder: 'adaugă o sursă...',
    width: '315px',
  });
  $('#editorSources').select2('enable', false);

  $('#editorTags').select2({
    placeholder: 'adaugă o etichetă...',
    width: '315px',
  });
  $('#editorTags').select2('enable', false);

  $('#editorSynonyms').select2({
    ajax: struct_lexemAjax,
    initSelection: select2InitSelection,
    minimumInputLength: 1,
    multiple: true,
    placeholder: 'adaugă un sinonim...',
    width: '315px',
  });
  $('#editorSynonyms').select2('enable', false);

  $('#editorAntonyms').select2({
    ajax: struct_lexemAjax,
    initSelection: select2InitSelection,
    minimumInputLength: 1,
    multiple: true,
    placeholder: 'adaugă un antonim...',
    width: '315px',
  });
  $('#editorAntonyms').select2('enable', false);

  $('#editorInternalRep, #editorInternalComment, #editorSources, #editorTags, #editorSynonyms, #editorAntonyms').bind(
    'change keyup input paste', function() { struct_anyChanges = true; });

  $('#variantIds').select2({
    ajax: struct_lexemAjax,
    initSelection: select2InitSelectionAjax,
    minimumInputLength: 1,
    multiple: true,
    width: '333px',
  });

  $('#variantOfId').select2({
    ajax: struct_lexemAjax,
    allowClear: true,
    initSelection: select2InitSelectionAjaxSingle,
    minimumInputLength: 1,
    placeholder: 'alegeți un lexem (opțional)',
    width: '333px',
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
  $('.toggleInternalHtmlLink').click(toggleInternalHtmlClick);
  $('.boxTitle').click(boxTitleClick);

  $('#lexemSourceIds').select2({
    placeholder: 'surse care atestă flexiunea',
    width: '333px',
  });
  // Disable the select2 when the HTML select is disabled. This doesn't happen by itself.
  $('#lexemSourceIds').select2('readonly', $('#lexemSourceIds').is('[readonly]'));
  $('#similarLexemId').select2({
    ajax: struct_lexemAjax,
    minimumInputLength: 1,
    placeholder: 'sau indicați un lexem similar',
    width: '300px',
  }).on('change', similarLexemChange);

  $('.mergeLexem').click(mergeLexemButtonClick);
  $('.similarLink').click(similarLinkClick);
}

function addMeaning() {
  if (!meaningEditorUnchanged()) {
    return false;
  }
  var node = $('#meaningTree').tree('getSelected');
  var parent;
  if (node) {
    var parentNode = $('#meaningTree').tree('getParent', node.target);
    parent = parentNode ? parentNode.target : null;
  } else {
    parent = null;
  }
  appendAndSelectNode(parent);
}

function addSubmeaning() {
  if (!meaningEditorUnchanged()) {
    return false;
  }
  var node = $('#meaningTree').tree('getSelected');
  if (node) {
    appendAndSelectNode(node.target);
  }
}

function appendAndSelectNode(target) {
  var randomId = Math.floor(Math.random() * 1000000000) + 1;
  $('#meaningTree').tree('append', {
    parent: target,
    data: [{ 'id' : randomId, 'text': $('#stemNode').html() }]
  });

  // Now find and select it
  var node = $('#meaningTree').tree('find', randomId);
  $('#meaningTree').tree('select', node.target);
}

function deleteMeaning() {
  if (!meaningEditorUnchanged()) {
    return false;
  }
  var node = $('#meaningTree').tree('getSelected');
  if (node) {
    var numChildren = $('#meaningTree').tree('getChildren', node.target).length;
    if (!numChildren || confirm('Confirmați ștergerea sensului și a tuturor subsensurilor?')) {
      $('#meaningTree').tree('remove', node.target);
    }
  }
}

function meaningEditorUnchanged(node) {
  return !struct_anyChanges || confirm('Aveți deja un sens în curs de modificare. Confirmați renunțarea la modificări?');
}

function beginMeaningEdit() {
  struct_anyChanges = false;
  var domNode = $('#meaningTree').tree('getSelected').target;
  var node = $(domNode);
  $('#editorInternalRep, #editorInternalComment, #editMeaningAcceptButton, #editMeaningCancelButton').removeAttr('disabled');
  $('#editorInternalRep').val(node.find('span.internalRep').text());
  $('#editorInternalComment').val(node.find('span.internalComment').text());
  $('#editorSources').select2('val', node.find('span.sourceIds').text().split(','));
  $('#editorSources').select2('enable');
  $('#editorTags').select2('val', node.find('span.meaningTagIds').text().split(','));
  $('#editorTags').select2('enable');

  var synonymIds = node.find('span.synonymIds').text().split(',');
  var synonyms = node.find('.synonyms .tag');
  $('#editorSynonyms').select2('data', synonyms.map(function(index) {
    return { id: synonymIds[index], text: $(this).text() };
  }));
  $('#editorSynonyms').select2('enable');

  var antonymIds = node.find('span.antonymIds').text().split(',');
  var antonyms = node.find('.antonyms .tag');
  $('#editorAntonyms').select2('data', antonyms.map(function(index) {
    return { id: antonymIds[index], text: $(this).text() };
  }));
  $('#editorAntonyms').select2('enable');
}

function acceptMeaningEdit() {
  struct_anyChanges = false;
  var domNode = $('#meaningTree').tree('getSelected').target;
  var node = $(domNode);

  // Update internal and HTML definition
  var internalRep = $('#editorInternalRep').val();
  node.find('span.internalRep').text(internalRep);
  $.post(wwwRoot + 'ajax/htmlize.php',
         { internalRep: internalRep, sourceId: 0 },
         function(data) { node.find('span.htmlRep').html(data); },
        'text');

  // Update internal and HTML comment
  var internalComment = $('#editorInternalComment').val();
  node.find('span.internalComment').text(internalComment);
  $.post(wwwRoot + 'ajax/htmlize.php',
         { internalRep: internalComment, sourceId: 0 },
         function(data) { node.find('span.htmlComment').html(data); },
        'text');

  // Update sources and sourceIds
  var sourceIds = $('#editorSources').val();
  node.find('span.sourceIds').text(sourceIds ? sourceIds.join(',') : '');
  node.find('span.sources').text('');
  $('#editorSources option:selected').each(function() {
    node.find('span.sources').append('<span class="tag">' + $(this).text() + '</span>');
  });

  // Update meaning tags and meaningIds
  var meaningTagIds = $('#editorTags').val();
  node.find('span.meaningTagIds').text(meaningTagIds ? meaningTagIds.join(',') : '');
  node.find('span.meaningTags').text('');
  $('#editorTags option:selected').each(function() {
    node.find('span.meaningTags').append('<span class="tag">' + $(this).text() + '</span>');
  });

  // Update synonym tags and synonymIds
  var synonymData = $('#editorSynonyms').select2('data');
  node.find('span.synonymIds').text($.map(synonymData, function(rec, i) { return rec.id; }));
  node.find('span.synonyms').text('');
  $.map(synonymData, function(rec, i) {
    node.find('span.synonyms').append('<span class="tag">' + rec.text + '</span>');
  });

  // Update antonym tags and antonymIds
  var antonymData = $('#editorAntonyms').select2('data');
  node.find('span.antonymIds').text($.map(antonymData, function(rec, i) { return rec.id; }));
  node.find('span.antonyms').text('');
  $.map(antonymData, function(rec, i) {
    node.find('span.antonyms').append('<span class="tag">' + rec.text + '</span>');
  });

  // Now update the tree node
  $('#meaningTree').tree('update', { target: domNode, text: node.find('.tree-title').html() });
}

function endMeaningEdit() {
  struct_anyChanges = false;
  $('#editorInternalRep, #editorInternalComment, #editMeaningAcceptButton, #editMeaningCancelButton').attr('disabled', 'disabled');
  $('#editorInternalRep').val('');
  $('#editorInternalComment').val('');
  $('#editorSources').select2('val', []);
  $('#editorSources').select2('enable', false);
  $('#editorTags').select2('val', []);
  $('#editorTags').select2('enable', false);
  $('#editorSynonyms').select2('data', []);
  $('#editorSynonyms').select2('enable', false);
  $('#editorAntonyms').select2('data', []);
  $('#editorAntonyms').select2('enable', false);
}

// Iterate a meaning tree node recursively and collect meaning-related fields
function meaningTreeWalk(node, results, level) {
  var jqNode = $(node.target);
  results.push({ 'id': jqNode.find('span.id').text(),
                 'level' : level,
                 'internalRep': jqNode.find('span.internalRep').text(),
                 'internalComment': jqNode.find('span.internalComment').text(),
                 'sourceIds': jqNode.find('span.sourceIds').text(),
                 'meaningTagIds': jqNode.find('span.meaningTagIds').text(),
                 'synonymIds': jqNode.find('span.synonymIds').text(),
                 'antonymIds': jqNode.find('span.antonymIds').text(),
               });
  var children = $('#meaningTree').tree('getChildren', node.target);
  for (var i = 0; i < children.length; i++) {
    meaningTreeWalk(children[i], results, level + 1);
  }
}

function saveEverything() {
  if (struct_anyChanges) {
    acceptMeaningEdit();
  }
  var results = new Array();
  var roots = $('#meaningTree').tree('getRoots');
  for (var i = 0; i < roots.length; i++) {
    meaningTreeWalk(roots[i], results, 0);
  }
  $('input[name=jsonMeanings]').val(JSON.stringify(results));
  $('#meaningForm').submit();
}

function toggleInternalHtmlClick() {
  var text = $(this).text();
  $(this).text((text == 'arată html') ? 'arată text' : 'arată html');
  $(this).closest('.defDetails').prevAll('.defInternalRep:last').slideToggle();
  $(this).closest('.defDetails').prevAll('.defHtmlRep:last').slideToggle();
  return false;
}

function boxTitleClick() {
  $(this).next('.boxContents').slideToggle();
}

function mergeLexemButtonClick() {
  var id = $(this).attr('id').split('_')[1];
  $('input[name=mergeLexemId]').val(id);
}

/* Set the model type, model number and restriction values */
function similarLinkClick() {
  var parts = $(this).attr('id').split('_');
  updateParadigm(parts[1], parts[2], parts[3]);
  return false;
}

function similarLexemChange(e) {
  var url = wwwRoot + 'ajax/getModelByLexemId.php?id=' + e.val;
  $.get(url, null, null, 'json')
    .done(function(data) {
      updateParadigm(data.modelType, data.modelNumber, data.restriction);
    });
}

function updateParadigm(modelType, modelNumber, restriction) {
  $('#modelTypeListId').val(modelType); // Does not trigger the onchange event
  updateModelList(false, modelNumber);
  $('input[name=restr\\[\\]]').each(function() {
    $(this).prop('checked', restriction.indexOf($(this).val()) != -1);
  });
}
