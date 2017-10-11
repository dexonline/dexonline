$(function() {
  similarRecord = [];
  var internalRep = $('#internalRep');
  var comment = $('#comment');

  function init() {
    // Show/hide elements related to the similar source and definition
    var c = $('#similarRecord').html().replace(/^<!--(.*)-->$/, '$1');
    similarRecord = JSON.parse(c);
    updateFields(similarRecord);

    initSelect2('#entryIds', 'ajax/getEntriesById.php', {
      ajax: { url: wwwRoot + 'ajax/getEntries.php' },
      createTag: allowNewOptions,
      minimumInputLength: 1,
      tags: true,
      templateSelection: formatEntryWithEditLink,
    });
            
    initSelect2('#tagIds', 'ajax/getTagsById.php', {
      ajax: { url: wwwRoot + 'ajax/getTags.php' },
      minimumInputLength: 1,
    });
            
    $('#entryIds, #sourceDropDown').change(updateFieldsJson);
    $('#refreshButton').click(updateFieldsJson);
    $('#similarDiff ins, #similarDiff del').click(definitionCopyFromSimilar);
  }

  function updateFieldsJson() {
    var data = {
      definitionId: $('input[name="definitionId"]').val(),
      definitionInternalRep: internalRep.val(),
      commentInternalRep: comment.val(),
      sourceId: $('#sourceDropDown').val(),
      entryIds: $('#entryIds').val(),
    };
    $.post(wwwRoot + 'ajax/getSimilarRecord.php', data, updateFields, 'json');
  }

  function updateFields(data) {
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

  init();
});
