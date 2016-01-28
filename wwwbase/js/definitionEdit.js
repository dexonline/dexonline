similarRecord = [];

function definitionEditInit() {
  // Show/hide elements related to the similar source and definition
  var comment = $('#similarRecord').html().replace(/^<!--(.*)-->$/, '$1');
  similarRecord = JSON.parse(comment);
  definitionEditUpdateFields(similarRecord);

  $('#lexemIds').select2({
    ajax: struct_lexemAjax,
    createSearchChoice: allowNewLexems,
    escapeMarkup: function(m) { return m; },
    initSelection: select2InitSelectionAjax,
    formatSelection: formatLexemWithEditLink,
    minimumInputLength: 1,
    multiple: true,
    width: '600px',
  });

  $('.associateHomonymLink').click(associateHomonym);
  $('#lexemIds, #sourceDropDown').change(definitionEditUpdateFieldsJson);
  $('#refreshButton').click(definitionEditUpdateFieldsJson);
  $('#similarDiff ins, #similarDiff del').click(definitionCopyFromSimilar);
}

function associateHomonym() {
  var lexemId = $(this).data('hid');
  var values = $('#lexemIds').select2('val');
  if (values.indexOf(lexemId) == -1) {
    values.push(lexemId);
    $('#lexemIds').select2('val', values);
  }
  return false;
}

function definitionEditUpdateFieldsJson() {
  var data = {
    definitionId: $('input[name="definitionId"]').val(),
    definitionInternalRep: $('textarea[name="internalRep"]').val(),
    commentInternalRep: $('textarea[name="commentContents"]').val(),
    sourceId: $('#sourceDropDown').val(),
    lexemIds: $('#lexemIds').val(),
  };
  $.post(wwwRoot + 'ajax/getSimilarRecord.php', data, definitionEditUpdateFields, 'json');
}

function definitionEditUpdateFields(data) {
  if (data.source) {
    $('.similarSourceName').text(data.source.shortName);
  }
  if (data.definition) {
    $('#similarDefinitionEdit').show();
    $('#similarDefinitionEdit').attr('href', '?definitionId=' + data.definition.id);
    $('#similarRep').html(data.definition.htmlRep);
  } else {
    $('#similarDefinitionEdit').hide();
    $('#similarRep').html('');
  }
  $('#similarDiff').html(data.htmlDiff);
  $('#similarIdentical').toggle(data.identical);
  var existsAndIsDifferent = (data.definition != null) && !data.identical;
  $('#similarNotIdentical').toggle(existsAndIsDifferent);
  $('#similarDiff').toggle(existsAndIsDifferent);

  $('#similarSourceMessageYes, #similarSourceMessageNoSource, #similarSourceMessageNoDefinition').hide();
  if (data.source && data.definition) {
    $('#similarSourceMessageYes').show();
  } else if (data.source) {
    $('#similarSourceMessageNoDefinition').show();
  } else {
    $('#similarSourceMessageNoSource').show();
  }

  if (typeof data.htmlRep != 'undefined') {
    $('#defPreview').html(data.htmlRep);
  }
  if (typeof data.commentHtmlRep != 'undefined') {
    $('#commentPreview').html(data.commentHtmlRep);
  }
}

function formatLexemWithEditLink(lexem) {
  if (startsWith(lexem.id, '@')) {
    // don't show an edit link for soon-to-be created lexems
    return lexem.text;
  } else {
    return lexem.text + ' <a class="select2Edit" href="lexemEdit.php?lexemId=' + lexem.id + '">&nbsp;</a>';
  }
}

function definitionCopyFromSimilar() {
  var ins = ($(this).prop('tagName') == 'INS') ? 1 : 0;
  var defId = $('input[name=definitionId]').val();
  var similarId = similarRecord.definition.id;
  var url = wwwRoot + 'admin/editSimilarDefinition.php' +
      '?defId=' + defId +
      '&similarId=' + similarId +
      '&sstart=' + $(this).parent().data('start1') +
      '&slen=' + $(this).parent().data('len1') +
      '&dstart=' + $(this).parent().data('start2') +
      '&dlen=' + $(this).parent().data('len2') +
      '&ins=' + ins;
  window.location = url;
}
