det_lexemStruct = {
  ajax: struct_lexemAjax,
  allowClear: true,
  createSearchChoice: allowNewLexems,
  initSelection: select2InitSelectionAjaxSingle,
  minimumInputLength: 1,
  placeholder: 'caută un lexem',
  width: '300px',
};

det_modelStruct = {
  ajax: struct_modelAjax,
  initSelection: select2InitSelectionModelAjax,
  minimumInputLength: 1,
  multiple: true,
  placeholder: 'caută un model',
  width: '300px',
};

function deToolInit() {
  $('.detLexem').select2(det_lexemStruct);
  $('.detModels').select2(det_modelStruct);
  $('#detAddRow').click(detAddRow);
  $('#butNext').click(detNextDefinition);
}

function detAddRow() {
  $('#detLexemStem').select2('destroy');
  $('#detModelsStem').select2('destroy');
  var r = $('#detRowStem').clone(true).attr('id', '').css('display', '');
  $('#detLexems').append(r);
  r.find('.detLexem').select2(det_lexemStruct);
  r.find('.detModels').select2(det_modelStruct);
  return false;
}

function detNextDefinition() {
  window.location = wwwRoot + 'admin/deTool.php?definitionId=' + $(this).data('definitionId');
  return false;
}
