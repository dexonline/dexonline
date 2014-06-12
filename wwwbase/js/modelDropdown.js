/**
 * Arguments for the LOC version select:
 * data-loc-version: mandatory
 *
 * Arguments for the model type select:
 * data-model-type: mandatory
 * data-canonical="1": optional; show only canonical model types
 * data-selected="...": optional; model type to select initially
 * data-verbose="1": optional; show the full model type name
 *
 * Arguments for the model number select:
 * data-model-number: mandatory
 * data-all-option="1": optional; show the "All" option
 * data-selected="...": optional; model number to select initially
 **/

$(function() {
  $('select[data-model-type]').change(modelTypeChange);
  $('*[data-loc-version]').change(locVersionChange).change();
});

function locVersionChange() {
  var span = $(this).closest('*[data-model-dropdown]');
  updateModelTypeList(span);
}

function modelTypeChange() {
  var span = $(this).closest('*[data-model-dropdown]');
  updateModelList(span);
}

function updateModelTypeList(span) {
  var select = span.children('select[data-model-type]');
  var locVersion = span.children('*[data-loc-version]').val();
  var canonical = select.data('canonical');
  $.get(wwwRoot + 'ajax/getModelTypesForLocVersion.php',
        { locVersion: locVersion, canonical: canonical },
        null, 'json')
    .success(function(data) {
      select.empty();

      $.each(data, function(index, dict) {
        var display = select.data('verbose')
          ? dict.code + ' (' + dict.description + ')'
          : dict.code;
        select.append($("<option></option>").attr("value", dict.code).text(display));
      });

      // Use the stored model type, but only when loading the page
      if (select.data('selected')) {
        select.val(select.data('selected'));
        select.removeAttr('data-selected');
        select.removeData('selected');
      }

      // Now update the model list since the model type list has changed.
      updateModelList(span);
    })
    .fail('Nu pot descărca lista de tipuri de modele.');
}

function updateModelList(span) {
  var select = span.children('select[data-model-number]');
  var locVersion = span.children('*[data-loc-version]').val();
  var modelType = span.children('select[data-model-type]').val();
  $.get(wwwRoot + 'ajax/getModelsForLocVersionModelType.php',
        { locVersion: locVersion, modelType: modelType },
        null, 'json')
    .success(function(data) {
      select.empty();
      if (select.data('allOption')) {
        select.append($('<option></option>').attr('value', -1).text('Toate'));
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
