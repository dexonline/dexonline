$(function() {

  function init() {
    // Allow hotkeys while an input field has focus
    $.hotkeys.options.filterInputAcceptingElements = false;
    $.hotkeys.options.filterContentEditable = false;

    $(document).bind('keydown', 'alt+a', redirectToAdmin);
    $(document).bind('keydown', 'alt+l', quickNavLexemFocus);
    $(document).bind('keydown', 'alt+d', quickNavDefFocus);
    $(document).bind('keydown', 'alt+shift+l', lexemSearchFocus);
    $(document).bind('keydown', 'alt+shift+d', defSearchFocus);
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
  }

  function defSearchFocus() {
    $('#definitionName').focus();
  }

  init();

});
