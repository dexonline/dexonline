$(function() {
  var stem = null;

  var lexemStruct = {
    ajax: struct_lexemAjax,
    allowClear: true,
    createSearchChoice: allowNewLexems,
    initSelection: select2InitSelectionAjaxSingle,
    minimumInputLength: 1,
    placeholder: 'caută un lexem',
    width: '300px',
  };

  var modelStruct = {
    ajax: struct_modelAjax,
    initSelection: select2InitSelectionAjaxModel,
    minimumInputLength: 1,
    multiple: true,
    placeholder: 'caută un model',
    width: '300px',
  };

  function init() {
    $('.detLexem')
      .select2(lexemStruct)
      .change(detLexemChange);
    $('.detModels')
      .select2(modelStruct)
      .change(detModelChange);
    $('.detShortcutI3').click(detShortcutI3);
    $('#detAddRow').click(detAddRow);
    stem = $('#detStemRow').detach().removeAttr('id');
    stem.find('.detLexem').select2('destroy');
    stem.find('.detModels').select2('destroy');
  }

  function detAddRow() {
    var r = stem.clone(true).appendTo('#detLexems');
    r.find('.detLexem').select2(lexemStruct);
    r.find('.detModels').select2(modelStruct);
    return false;
  }

  function detLexemChange() {
    // Refresh the model list
    var lexemId = $(this).val();
    var data = [];
    $.ajax({
      url: wwwRoot + 'ajax/getModelsByLexemId.php?id=' + lexemId,
      dataType: 'json',
      async: false,
      success: function(models) {
        $.each(models, function(index, t) {
          var id = t.modelType + t.modelNumber;
          var text = t.modelType + t.modelNumber + ' (' + t.exponent + ')';
          data.push({ id: id, text: text });
        });
      },
    });
    var m = $(this).closest('tr').find('.detModels');
    if (data.length || !lexemId) {
      // Existing lexem entered or lexem deleted
      m.select2('data', data);
    } else {
      // New lexem entered
      m.select2('val', ['I3']);
    }

    // Disable the save button
    $('#butSave').prop('disabled', true);
  }

  function detModelChange() {
    // Disable the save button
    $('#butSave').prop('disabled', true);
  }

  function detShortcutI3() {
    var m = $(this).closest('tr').find('.detModels');
    m.select2('val', ['I3']);

    // Disable the save button
    $('#butSave').prop('disabled', true);

    return false;
  }

  init();
});
