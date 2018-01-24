$(function() {
  // If the textarea value doesn't change, don't recompute the preview.
  var rep = '';

  function init() {
    window.setInterval(updatePreview, 5000);

    initSelect2('#lexemeIds', 'ajax/getLexemesById.php', {
      ajax: { url: wwwRoot + 'ajax/getLexemes.php' },
      createTag: allowNewOptions,
      minimumInputLength: 1,
      tags: true,
    }).done(function() {
      $('#lexemeIds').select2('focus');
    });
  }

  function updatePreview() {
    var newRep = $('#defTextarea').val();
    if (newRep != rep) {
      rep = newRep;
      var sourceId = $('#sourceDropDown').val();
      $.post(wwwRoot + 'ajax/htmlize.php', { internalRep: rep, sourceId: sourceId })
        .done(function(data) { $('#previewDiv').html(data); })
        .fail(updatePreviewFail);
    }
  }

  function updatePreviewFail() {
    $('#previewDiv').html('Este o problemă la comunicarea cu serverul. Voi reîncerca în 5 secunde.');
  }

  init();
});
