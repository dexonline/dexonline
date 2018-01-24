$(function() {

  function init() {
    // Allow hotkeys while an input field has focus
    $.hotkeys.options.filterInputAcceptingElements = false;
    $.hotkeys.options.filterContentEditable = false;
    $.hotkeys.options.filterTextInputs = false;

    $(document).bind('keydown', 'alt+l', quickNavLexemFocus);
    $(document).bind('keydown', 'alt+d', quickNavDefFocus);
    $(document).bind('keydown', 'alt+i', quickNavEntryFocus);

    $(document).bind('keydown', 'alt+a', redirectToAdmin);
    $(document).bind('keydown', 'alt+r', clickRefreshButton);
    $(document).bind('keydown', 'alt+s', clickSaveButton);
    $(document).bind('keydown', 'alt+v', clickRecentPagesLink);

    $(document).bind('keydown', 'alt+c', redirectToWotDTable);
    $(document).bind('keydown', 'alt+z', redirectToWotDImage);
    $(document).bind('keydown', 'alt+x', redirectToWotDAuthorAssign);
    $(document).bind('keydown', 'alt+t', function() { toggleMode('structure'); });
    $(document).bind('keydown', 'alt+w', function() { toggleMode('wotd'); });
    $(document).bind('keydown', 'alt+shift+w', function() { toggleMode('granularity'); });

    $(document).bind('keydown', 'alt+p', clickPreviewTags);

    $(document).bind('keydown', 'alt+q', function(evt) { Charmap.show('#modal-charmap', evt.target); });

    $('a.hotkeyLink').click(hotkeyLinkClick);
  }

  function redirectToAdmin() {
    window.location = wwwRoot + 'admin';
  }

  function redirectToWotDTable() {
    window.location = wwwRoot + 'admin/wotdTable.php';
  }

  function redirectToWotDImage() {
    window.location = wwwRoot + 'admin/wotdImages.php';
  }

  function redirectToWotDAuthorAssign() {
    window.location = wwwRoot + 'alocare-autori.php';
  }

  function toggleMode(mode) {
    window.location = wwwRoot + 'admin/toggleMode.php?mode=' + mode;
  }

  function quickNavLexemFocus() {
    $('.quickNav #lexemeId').select2('open');
    return false;
  }

  function quickNavDefFocus() {
    $('.quickNav #definitionId').select2('open');
    return false;
  }

  function quickNavEntryFocus() {
    $('.quickNav #entryId').select2('open');
    return false;
  }

  function clickRefreshButton() {
    $('button[name="refreshButton"]').click();
    return false;
  }

  function clickSaveButton() {
    $('button[name="saveButton"]').click();
    return false;
  }

  function clickRecentPagesLink() {
    $('#recentPagesLink').click();
    return false;
  }

  function clickPreviewTags() {
    $('#previewTags').click();
    return false;
  }

  function hotkeyLinkClick() {
    var mode = $(this).data('mode');
    toggleMode(mode);
  }

  init();

});
