function updateModelTypeList(locVersionSelect, modelTypeListId) {
  var value = locVersionSelect.options[locVersionSelect.selectedIndex].value;
  url = wwwRoot + 'ajax/getModelTypesForLocVersion.php?locVersion=' + value;
  if (window.location.toString().indexOf('/admin/') != -1) {
    url = '../' + url;
  }
  makeGetRequest(url, populateModelTypeList, modelTypeListId);
  return false;
}

function updateModelListWithLocVersion(mtSelect, modelListId) {
  var lvSelect = document.getElementById('locVersionListId');
  var lv = lvSelect.options[lvSelect.selectedIndex].value;
  var mt = mtSelect.options[mtSelect.selectedIndex].value;
  url = wwwRoot + 'ajax/getModelsForLocVersionModelType.php' +
    '?locVersion=' + lv +
    '&modelType=' + mt;
  makeGetRequest(url, populateModelList, [true, modelListId]);
  return false;
}

function updateModelList(modelTypeSelect, modelListId) {
  var value = modelTypeSelect.options[modelTypeSelect.selectedIndex].value;
  makeGetRequest(wwwRoot + 'ajax/getModelsForLocVersionModelType.php?modelType=' +
                 value, populateModelList, [false, modelListId]);
  return false;
}

function populateModelTypeList(httpRequest, modelTypeListId) {
  if (httpRequest.readyState == 4) {
    if (httpRequest.status == 200) {
      var select = document.getElementById(modelTypeListId);
      select.options.length = 0;

      result = httpRequest.responseText;
      var lines = result.split('\n');

      for (var i = 0; i < lines.length && lines[i]; i += 2) {
        var val = lines[i];
        var descr = lines[i + 1];
        var display = val + ' (' + descr + ')';
        select.options[select.options.length] = new Option(display, val);
      }

      // Now update the model list since the model type list has changed.
      var mtSelect = document.getElementById('modelTypeListId');
      updateModelListWithLocVersion(mtSelect, 'modelListId');
    } else {
      alert('Nu pot descărca lista de tipuri de modele!');
    }
  }
}

function populateModelList(httpRequest, argArray) {
  var createExtraOption = argArray[0];
  var modelListId = argArray[1];
  if (httpRequest.readyState == 4) {
    if (httpRequest.status == 200) {
      var select = document.getElementById(modelListId);
      select.options.length = 0;

      if (createExtraOption) {
        select.options[0] = new Option("Toate", -1);
      }

      result = httpRequest.responseText;
      var lines = result.split('\n');

      for (var i = 0; i < lines.length && lines[i]; i += 3) {
        var id = lines[i];
        var number = encodeURIComponent(lines[i + 1]);
        var exponent = lines[i + 2];
        var display = lines[i + 1] + (id == '0' ? '*' : '') + ' (' + exponent + ')';
        select.options[select.options.length] = new Option(display, number);
      }
    } else {
      alert('Nu pot descărca lista de exponenți pentru modele!');
    }
  }
}

function blUpdateParadigmVisibility(radioButton) {
  // Locate the div containing one sub-div for each paradigm
  var components = radioButton.name.split('_', 2);
  var lexemId = components[1];
  var modelId = radioButton.value;
  var parentDiv = document.getElementById('paradigms_' + lexemId);

  var expectedDivId = 'paradigm_' + lexemId + '_' + modelId;
  for (var i = 0; i < parentDiv.childNodes.length; i++) {
    var div = parentDiv.childNodes[i];
    if (div.nodeName == 'DIV') {
      div.style.display = (div.id == expectedDivId) ? 'block' : 'none';
    }
  }

  return true;
}

function blUpdateDefVisibility(anchor) {
  var components = anchor.id.split('_', 2);
  var lexemId = components[1];
  var div = document.getElementById('definitions_' + lexemId);
  if (div.style.display == 'none') {
    div.style.display = 'block';
    anchor.innerHTML = 'ascunde definițiile';
  } else {
    div.style.display = 'none';
    anchor.innerHTML = 'arată definițiile';
  }
  return false;
}

function showDiv(divId) {
  var div = document.getElementById(divId);
  div.style.display = 'block';
}

function hideDiv(divId) {
  var div = document.getElementById(divId);
  div.style.display = 'none';
}

function mlUpdateDefVisibility(lexemId, divId) {
  var div = document.getElementById(divId);
  // If the definitions are already loaded, then just toggle the div's visibility.
  if (!div.defsLoaded) {
    var url = wwwRoot + 'ajax/getDefinitionsForLexem.php?lexemId=' + lexemId;
    makeGetRequest(url, populateDefinitionList, divId);
  } else if (div.style.display == 'none') {
    div.style.display = 'block';
  } else {
    div.style.display = 'none';
  }
  return false;
}

function populateDefinitionList(httpRequest, divId) {
  if (httpRequest.readyState == 4) {
    if (httpRequest.status == 200) {
      result = httpRequest.responseText;
      var lines = result.split('\n');
      var div = document.getElementById(divId);

      for (var i = 0; i < lines.length && lines[i]; i += 4) {
        var defId = lines[i];
        var source = lines[i + 1];
        var status = lines[i + 2];
        var defText = lines[i + 3];
        div.innerHTML += defText + "<br/>";
        div.innerHTML += '<span class="defDetails">Id: ' + defId + ' | Sursa: ' + source + ' | Starea: ' + status + '</span><br/>';
      }

      div.style.display = "block";
      div.defsLoaded = true;
    } else {
      alert('Nu pot descărca definițiile!');
    }
  }
}

function apSelectLetter(lexemId, cIndex) {
  if (cIndex != -1) {
    var span = document.getElementById('letter_' + lexemId + '_' + cIndex);
    span.innerHTML = "'" + span.innerHTML;
    var checkbox = document.getElementById('noAccent_' + lexemId);
    checkbox.checked = false;
  }

  var input = document.getElementById('position_' + lexemId);

  if (input.value != -1) {
    var oldSpan = document.getElementById('letter_' + lexemId + '_' +
                                          input.value);
    oldSpan.innerHTML = oldSpan.innerHTML.substring(1);
  }
  input.value = cIndex;
}

// On the abbreviation review page, we must check that the user made selections for all the ambiguous abbreviations
// before we activate the submit button.
function initAbbrevCounter(size) {
  abbrev_clickTracker = Array(size);
  abbrev_numLeft = size;
}

function pushAbbrevButton(id, state) {
  var span = $('#abrevText_' + id);
  if (state) { // Long word
    span.css('border-bottom', '2px solid green');
    span.removeClass('abbrev');
  } else {
    span.css('border-bottom', '2px solid red');
    span.addClass('abbrev');
  }
  if (!abbrev_clickTracker[id]) {
    abbrev_clickTracker[id] = 1;
    abbrev_numLeft--;
    if (!abbrev_numLeft) {
      $('#submitButton').removeAttr('disabled');
    }
  }
}

/********************* Meaning editor code **************************/

me_anyChanges = false;

function meaningEditorInit() {
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
  $('#editorSources').multiselect({
    header: false,
    minWidth: 312,
    noneSelectedText: 'alegeți zero sau mai multe surse',
    selectedList: 5,
  });
  $('#editorSources').multiselect('disable');
  $('#editorTags').multiselect({
    header: false,
    minWidth: 312,
    noneSelectedText: 'alegeți zero sau mai multe etichete',
    selectedList: 5,
  });
  $('#editorTags').multiselect('disable');
  $('#editorInternalRep, #editorInternalComment, #editorSources, #editorTags, #editorSynonym').bind('change keyup input paste', function() {
    me_anyChanges = true;
  });
  $('#editorSynonym').combobox({
    url: wwwRoot + 'ajax/getLexems.php?easyui=1',
    valueField: 'id',
    textField: 'text',
    mode: 'remote',
    hasDownArrow: false,
    onLoadSuccess: editorSynonymOnLoad,
    onSelect: editorSynonymSelect,
    keyHandler: $.extend({}, $.fn.combobox.defaults.keyHandler, {
      enter: editorSynonymEnter,
    }),
  });
  $('#editorSynonym').combobox('disable');
  $('#editorSynonymList').on('click', '.link', deleteSynonymClick);
  $('#editMeaningAcceptButton').click(acceptMeaningEdit);
  $('#editMeaningCancelButton').click(endMeaningEdit);
  $('#dexEditSaveButton').click(dexEditSaveEverything);
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
  $('#meaningTree').tree('append', {
    parent: parent,
    data: [{ 'text': $('#stemNode').html() }]
  });
}

function addSubmeaning() {
  if (!meaningEditorUnchanged()) {
    return false;
  }
  var node = $('#meaningTree').tree('getSelected');
  if (node) {
    $('#meaningTree').tree('append', {
      parent: node.target,
      data: [{ 'text': $('#stemNode').html() }]
    });
  }
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

function editorSynonymOnLoad() {
  var data = $('#editorSynonym').combobox('getData');
  var text = $('#editorSynonym').combobox('getText');
  if (data && data[0] && text && (data[0].text == text)) {
    $('#editorSynonym').combobox('setValue', data[0].id);
  }
}

function editorSynonymSelect(record) {
  $('#editorSynonym').combobox('textbox').focus();
}

function editorSynonymEnter() {
  var value = $('#editorSynonym').combobox('getValue');
  var text = $('#editorSynonym').combobox('getText');
  var exists = $('#editorSynonymList').find(".id:contains('" + value + "')").length;
  if (value && !isNaN(value) && !exists) {
    editorAddSynonymTag(value, text);
    $('#editorSynonym').combobox('hidePanel');
    $('#editorSynonym').combobox('clear');
  }
}

function editorAddSynonymTag(id, text) {
  var div = $('#stemSynonym').clone().removeAttr('id');
  div.children('.id').text(id);
  div.children('.name').text(text);
  div.appendTo('#editorSynonymList');
}

function deleteSynonymClick() {
  $(this).closest('.synonymTag').remove();
}

function meaningEditorUnchanged(node) {
  return !me_anyChanges || confirm('Aveți deja un sens în curs de modificare. Confirmați renunțarea la modificări?');
}

function beginMeaningEdit() {
  me_anyChanges = false;
  var domNode = $('#meaningTree').tree('getSelected').target;
  var node = $(domNode);
  $('#editorInternalRep, #editorInternalComment, #editMeaningAcceptButton, #editMeaningCancelButton').removeAttr('disabled');
  $('#editorInternalRep').val(node.find('span.internalRep').text());
  $('#editorInternalComment').val(node.find('span.internalComment').text());
  $('#editorSources').val(node.find('span.sourceIds').text().split(','));
  $('#editorSources').multiselect('refresh');
  $('#editorSources').multiselect('enable');
  $('#editorTags').val(node.find('span.meaningTagIds').text().split(','));
  $('#editorTags').multiselect('refresh');
  $('#editorTags').multiselect('enable');
  $('#editorSynonym').combobox('enable');
  $('#editorSynonymList').text('');
  var synonymIds = node.find('span.synonymIds').text().split(',');
  node.find('span.synonyms .tag').each(function(index, value) {
    editorAddSynonymTag(synonymIds[index], $(this).text());
  });
}

function acceptMeaningEdit() {
  me_anyChanges = false;
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
  var synonymIds = $('#editorSynonymList').find('.id').map(function() { return $(this).text(); }).get().join(',');
  node.find('span.synonymIds').text(synonymIds);
  node.find('span.synonyms').text('');
  $('#editorSynonymList').find('.name').each(function() {
    node.find('span.synonyms').append('<span class="tag">' + $(this).text() + '</span>');
  });
  $('#editorSynonym').combobox('setText', '');
  $('#editorSynonym').combobox('setValue', '');
}

function endMeaningEdit() {
  me_anyChanges = false;
  $('#editorInternalRep, #editorInternalComment, #editMeaningAcceptButton, #editMeaningCancelButton').attr('disabled', 'disabled');
  $('#editorInternalRep').val('');
  $('#editorInternalComment').val('');
  $('#editorSources').val([]);
  $('#editorSources').multiselect('refresh');
  $('#editorSources').multiselect('disable');
  $('#editorTags').val([]);
  $('#editorTags').multiselect('refresh');
  $('#editorTags').multiselect('disable');
  $('#editorSynonymList').text('');
  $('#editorSynonym').combobox('setText', '');
  $('#editorSynonym').combobox('setValue', '');
  $('#editorSynonym').combobox('disable');
}

// Iterate a meaning tree node recursively and collect meaning-related fields
function dexEditTreeWalk(node, results, level) {
  var jqNode = $(node.target);
  results.push({ 'id': jqNode.find('span.id').text(),
                 'level' : level,
                 'internalRep': jqNode.find('span.internalRep').text(),
                 'internalComment': jqNode.find('span.internalComment').text(),
                 'sourceIds': jqNode.find('span.sourceIds').text(),
                 'meaningTagIds': jqNode.find('span.meaningTagIds').text(),
                 'synonymIds': jqNode.find('span.synonymIds').text(),
               });
  var children = $('#meaningTree').tree('getChildren', node.target);
  for (var i = 0; i < children.length; i++) {
    dexEditTreeWalk(children[i], results, level + 1);
  }
}

function dexEditSaveEverything() {
  if (!meaningEditorUnchanged()) {
    return false;
  }
  var results = new Array();
  var roots = $('#meaningTree').tree('getRoots');
  for (var i = 0; i < roots.length; i++) {
    dexEditTreeWalk(roots[i], results, 0);
  }
  $('input[name=jsonMeanings]').val(JSON.stringify(results));
  $('#meaningForm').submit();
}
