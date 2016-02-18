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
    $('.lexem')
      .select2(lexemStruct)
      .change(lexemChange);
    $('.models')
      .select2(modelStruct)
      .change(modelChange);
    $('.shortcutI3').click(shortcutI3);
    $('#addRow').click(addRow);
    stem = $('#stem').detach().removeAttr('id');
    stem.find('.lexem').select2('destroy');
    stem.find('.models').select2('destroy');
  }

  function addRow() {
    var r = stem.clone(true).appendTo('#lexemsTable');
    r.find('.lexem').select2(lexemStruct);
    r.find('.models').select2(modelStruct);
    return false;
  }

  function lexemChange() {
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
    var m = $(this).closest('tr').find('.models');
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

  function modelChange() {
    // Disable the save button
    $('#butSave').prop('disabled', true);
  }

  function shortcutI3() {
    var m = $(this).closest('tr').find('.models');
    m.select2('val', ['I3']);

    // Disable the save button
    $('#butSave').prop('disabled', true);

    return false;
  }

  init();
});
