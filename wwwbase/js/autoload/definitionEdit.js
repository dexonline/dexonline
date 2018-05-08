$(function() {
  similarRecord = [];
  var internalRep = $('#internalRep');
  var lastDiffClicked = null; // keep track of the last <ins> or <del> the user clicked

  function init() {
    // Show/hide elements related to the similar source and definition
    var c = $('#similarRecord').html();

    // unescape HTML entities
    c = $("<div/>").html(c).text();

    similarRecord = JSON.parse(c);
    updateFields(similarRecord);

    initSelect2('#entryIds', 'ajax/getEntriesById.php', {
      ajax: { url: wwwRoot + 'ajax/getEntries.php' },
      createTag: allowNewOptions,
      minimumInputLength: 1,
      tags: true,
      templateSelection: formatEntryWithEditLink,
    });

    $('#entryIds, #sourceDropDown').change(updateFieldsJson);
    $('#refreshButton').click(updateFieldsJson);

    /****************** popover initialization ******************/
    $('ins, del').popover({
      container: 'body',
      content: popoverContent,
      html: true,
      placement: 'bottom',
      trigger: 'click',
    });

    // when showing a popover, hide other popovers
    $('ins, del').on('show.bs.popover', function() {
      $('ins, del').not(this).popover('hide');
    })

    // workaround for a popover bug
    // https://github.com/twbs/bootstrap/issues/16732
    $('ins, del').on('hidden.bs.popover', function (e) {
      $(e.target).data("bs.popover").inState.click = false;
    });

    $(document).on('click', '.diffButton', diffClick);
    $(document).on('click', '.diffCancel', diffCancelClick);
  }

  function updateFieldsJson() {
    var data = {
      definitionId: $('input[name="definitionId"]').val(),
      internalRep: internalRep.val(),
      sourceId: $('#sourceDropDown').val(),
      entryIds: $('#entryIds').val(),
    };
    $.post(wwwRoot + 'ajax/getSimilarRecord.php', data, updateFields, 'json');
  }

  function updateFields(data) {
    if (data.source) {
      $('.similarSourceName').text(data.source.shortName);
    }
    if (data.sim) {
      $('#similarDefinitionEdit').show();
      $('#similarDefinitionEdit').attr('href', '?definitionId=' + data.sim.id);
      $('#similarRep').html(data.simHtml);
    } else {
      $('#similarDefinitionEdit').hide();
      $('#similarRep').html('');
    }
    $('#similarDiff').html(data.simDiff);

    $('#similarIdentical').toggle(data.identical);
    var existsAndIsDifferent = (data.sim != null) && !data.identical;
    $('#similarNotIdentical').toggle(existsAndIsDifferent);
    $('#similarDiff').toggle(existsAndIsDifferent);

    $('#similarSourceMessageYes, #similarSourceMessageNoSource, #similarSourceMessageNoDefinition').hide();
    if (data.source && data.sim) {
      $('#similarSourceMessageYes').show();
    } else if (data.source) {
      $('#similarSourceMessageNoDefinition').show();
    } else {
      $('#similarSourceMessageNoSource').show();
    }

    if (typeof data.html != 'undefined') {
      $('#defPreview').html(data.html);
    }
    if (typeof data.footnoteHtml != 'undefined') {
      $('#footnotes').html(data.footnoteHtml);
    }
  }

  function popoverContent() {
    lastDiffClicked = $('#similarDiff').find('ins, del').index($(this));

    var p = $('#diffPopover').clone();

    // fix button names
    var ins = $(this).prop('tagName') == 'INS';
    var sourceNames = p.find('.similarSourceName');
    var toReplace = ins ? sourceNames.last() : sourceNames.first();
    toReplace.text('această definiție');

    return p.html();
  }

  function diffClick(e) {
    var ins = ($(this).prop('tagName') == 'INS') ? 1 : 0;
    var defId = $('input[name=definitionId]').val();
    var similarId = similarRecord.sim.id;
    var action = $(this).data('insert');

    var pattern = '%sadmin/editSimilarDefinition.php?defId=%s&similarId=%s&rank=%s&action=%s';
    var url = sprintf(pattern, wwwRoot, defId, similarId, lastDiffClicked, action);
    window.location = url;
  }

  function diffCancelClick() {
    $('ins, del').popover('hide');
    return false;
  }

  init();
});
