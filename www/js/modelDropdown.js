/**
 * Arguments for the model type select:
 * data-model-type: mandatory
 *
 * Arguments for the model number select:
 * data-model-number: mandatory
 * data-all-option="text|value": optional; show an "all" option
 * data-selected="...": optional; model number to select initially
 **/

$(function() {
  $('select[data-model-type]').change(modelTypeChange).change();
});

function modelTypeChange() {
  var span = $(this).closest('*[data-model-dropdown]');
  updateModelList(span);
}

function updateModelList(span) {
  var select = span.children('select[data-model-number]');
  var modelType = span.children('select[data-model-type]').val();
  $.get(wwwRoot + 'ajax/getModelsForModelType.php',
        { modelType: modelType },
        null, 'json')
    .success(function(data) {
      select.empty();
      var allOption = select.data('allOption');
      if (allOption) {
        var parts = allOption.split('|')
        select.append($('<option></option>').attr('value', parts[1]).text(parts[0]));
      }
      $.each(data, function(index, dict) {
        var display = dict.number + ' (' + dict.exponent + ')';
        select.append($('<option></option>').attr('value', dict.number).text(display));
      });

      // Use the stored model number, but only when loading the page
      if (select.data('selected')) {
        select.val(select.data('selected'));
        select.removeAttr('data-selected');
        select.removeData('selected');
      }
    })
    .fail('Nu pot descărca lista de modele.');
  return false;
}
