/**
 * Arguments for the model type select:
 * data-model-type: mandatory
 *
 * Arguments for the model number select:
 * data-model-number: mandatory
 * data-all-option="text|value": optional; show an "all" option
 * data-selected="...": optional; model number to select initially
 **/
var modelNumber;

$(function() {
  $('select[data-model-type]').on('focus', function() {
    setLastOptions($(this)); // Store some values before change occures
  }).on('change', function(evt, setPrevious = false) { // setPrevious true from lexeme>edit.js>similarLexemeChange()
    if (canonicalChanged($(this).find(':selected').data('canonical'))) {
      updateModelNumberList($(this).val(), setPrevious);
    } else {
      modelNumber.val(modelNumber.data('prevNumber'));
    }
    updateRestrictionMenu($(this));
  });
});

function setLastOptions(select) {
  select.data('prevCanonical', select.find(':selected').data('canonical'));
  modelNumber = select.closest('*[data-model-dropdown]').children('select[data-model-number]');
  modelNumber.data('prevNumber', modelNumber.val());
}

function canonicalChanged(selectedCanonical) {
  return selectedCanonical !== $(this).data('prevCanonical');
}

function updateModelNumberList(modelType, setPrevious) {
  $.get(wwwRoot + 'ajax/getModelsForModelType.php',
        { modelType: modelType },
        null, 'json')
    .success(function(data) {
      modelNumber.empty();
      var allOption = modelNumber.data('all-option');
      if (allOption) {
        modelNumber.append($('<option></option>').attr('value', '').text(allOption));
      }
      $.each(data, function(index, dict) {
        var display = dict.number + ' (' + dict.exponent + ')';
        modelNumber.append($('<option></option>').attr('value', dict.number).text(display));
      });

      modelNumber.val(setPrevious ? modelNumber.data('prevNumber') : allOption ? '' : data[0]['number']);
    })
    .fail('Nu pot descărca lista de modele.');

  return false;
}

function updateRestrictionMenu(select) {
  var restrict = select.closest('*[data-model-dropdown]').find('input[name=restriction]');
  if (restrict.length) { if (typeof loadRestrictionMenu === 'function') { loadRestrictionMenu(select.find(':selected').data('canonical'), restrict.val());} }
}
