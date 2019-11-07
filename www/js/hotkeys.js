$(function() {

  function init() {
    // Allow hotkeys while an input field has focus
    $.hotkeys.options.filterInputAcceptingElements = false;
    $.hotkeys.options.filterContentEditable = false;
    $.hotkeys.options.filterTextInputs = false;

    $(document).bind('keydown', 'alt+l', quickNavLexemeFocus);
    $(document).bind('keydown', 'alt+d', quickNavDefFocus);
    $(document).bind('keydown', 'alt+i', quickNavEntryFocus);

    $(document).bind('keydown', 'alt+a', function() { redirect('admin'); });
    $(document).bind('keydown', 'alt+n', function() { redirect('editare-definitie'); });
    $(document).bind('keydown', 'alt+r', clickRefreshButton);
    $(document).bind('keydown', 'alt+s', clickSaveButton);
    $(document).bind('keydown', 'alt+v', clickRecentPagesLink);

    $(document).bind('keydown', 'alt+c', function() { redirect('tabel-cz'); });
    $(document).bind('keydown', 'alt+z', function() { redirect('imagini-cz'); });
    $(document).bind('keydown', 'alt+x', function() { redirect('alocare-autori'); });
    $(document).bind('keydown', 'alt+t', function() { toggleMode('structure'); });
    $(document).bind('keydown', 'alt+w', function() { toggleMode('wotd'); });
    $(document).bind('keydown', 'alt+shift+w', function() { toggleMode('granularity'); });

    $(document).bind('keydown', 'alt+p', clickPreviewTags);

    $(document).bind('keydown', 'alt+q', showCharMap);

    $('a.hotkeyLink').click(hotkeyLinkClick);
  }

  /* get a reference for the anonymous function to toggle keydown binding in the modal */
  var showCharMap = function(evt) { Charmap.show(evt.target, showCharMap); };

  function redirect(path) {
    window.location = wwwRoot + path;
  }

  function toggleMode(mode) {
    window.location = wwwRoot + 'toggleMode?mode=' + mode;
  }

  function quickNavLexemeFocus() {
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
