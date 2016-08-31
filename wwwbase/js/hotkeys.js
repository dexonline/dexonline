$(function() {

  function init() {
    // Allow hotkeys while an input field has focus
    $.hotkeys.options.filterInputAcceptingElements = false;
    $.hotkeys.options.filterContentEditable = false;
    $.hotkeys.options.filterTextInputs = false;

    $(document).bind('keydown', 'alt+a', redirectToAdmin);
    $(document).bind('keydown', 'alt+l', quickNavLexemFocus);
    $(document).bind('keydown', 'alt+d', quickNavDefFocus);
    $(document).bind('keydown', 'alt+shift+l', lexemSearchFocus);
    $(document).bind('keydown', 'alt+shift+d', defSearchFocus);

    $(document).bind('keydown', 'alt+r', clickRefreshButton);
    $(document).bind('keydown', 'alt+s', clickSaveButton);
  }

  function redirectToAdmin() {
    console.log('foo');
    window.location = wwwRoot + 'admin';
  }

  function quickNavLexemFocus() {
    $('.quickNav #lexemId').select2('open');
    return false;
  }

  function quickNavDefFocus() {
    $('.quickNav #definitionId').select2('open');
    return false;
  }

  function lexemSearchFocus() {
    $('#lexemForm').focus();
    return false;
  }

  function defSearchFocus() {
    $('#definitionName').focus();
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

  init();

});
