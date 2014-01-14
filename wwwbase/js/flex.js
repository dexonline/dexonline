// If there are error messages, show them in a dialog
$(function() {
  $('#flashMessage').dialog({
    buttons: {
      Ok: function() {
        $(this).dialog('close');
      }
    },
    modal: true,
    title: 'Eroare',
    width: 500,
  });
});

function updateModelTypeList() {
  var locVersion = $('#locVersionListId').val();
  var url = wwwRoot + 'ajax/getModelTypesForLocVersion.php?locVersion=' + locVersion;
  $.get(url, null, null, 'json')
    .done(populateModelTypeList)
    .fail('Nu pot descărca lista de tipuri de modele.');
  return false;
}

/**
 * listAllOption -- whether to prepend an option for "list all"
 * selectedOption -- value to select once loading is complete (optional)
**/
function updateModelList(listAllOption, selectedOption) {
  $.get(wwwRoot + 'ajax/getModelsForLocVersionModelType.php',
        { locVersion: $('#locVersionListId').val(), modelType: $('#modelTypeListId').val() },
        null, 'json')
    .done(function(data) { populateModelList(data, listAllOption, selectedOption); })
    .fail('Nu pot descărca lista de modele.');
  return false;
}

function populateModelTypeList(data) {
  var select = $('#modelTypeListId');
  select.empty();

  $.each(data, function(index, dict) {
    var display = dict.code + ' (' + dict.description + ')';
    select.append($("<option></option>").attr("value", dict.code).text(display));
  });

  // Now update the model list since the model type list has changed.
  updateModelList(true);
}

function populateModelList(data, listAllOption, selectedOption) {
  var select = $('#modelListId');
  select.empty();

  if (listAllOption) {
    select.append($('<option value="-1">Toate</option>'));
  }
  $.each(data, function(index, dict) {
    var display = dict.number + ' (' + dict.exponent + ')';
    select.append($("<option></option>").attr("value", dict.number).text(display));
  });
  if (selectedOption) {
    select.val(selectedOption);
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
  var div = $('#' + divId);
  // If the definitions are already loaded, then just toggle the div's visibility.
  if (trim(div.html()) == '') {
    $.get(wwwRoot + 'ajax/getDefinitionsForLexem.php?lexemId=' + lexemId)
      .done(function(data) { div.html(data).slideToggle(); })
      .fail('Nu pot descărca lista de definiții.');
  } else {
    div.slideToggle();
  }
  return false;
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
