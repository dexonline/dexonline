$(function() {
  // Was there a key press since the last preview update?
  var keyPressed = false;

  function init() {
    window.setInterval(updatePreview, 5000);
    $('#defTextarea').on('input propertychange', defChanged);
    contribInit();
  }

  function defChanged() {
    keyPressed = true;
  }

  function updatePreview() {
    if (keyPressed) {
      keyPressed = false;
      var internalRep = $('#defTextarea').val();
      var sourceId = $('#sourceDropDown').val();
      $.post(wwwRoot + 'ajax/htmlize.php', { internalRep: internalRep, sourceId: sourceId })
        .done(function(data) { $('#previewDiv').html(data); })
        .fail(updatePreviewFail);
    }
  }

  function updatePreviewFail() {
    $('#previewDiv').html('Este o problemă la comunicarea cu serverul. Voi reîncerca în 5 secunde.');
  }

  init();
});
