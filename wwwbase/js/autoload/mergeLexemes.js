$(function() {
  function init() {
    $('.defToggle').click(toggleDefinitions);
  }

  function toggleDefinitions() {
    var lexemId = $(this).data('lexemId');
    var div = $('#' + $(this).data('divId'));

    // If the definitions are already loaded, then just toggle the div's visibility.
    if (trim(div.html()) == '') {
      $.get(wwwRoot + 'ajax/getDefinitionsForLexem.php?lexemId=' + lexemId)
        .done(function(data) { div.html(data).stop().slideDown(); })
        .fail('Nu pot descărca lista de definiții.');
    } else {
      div.stop().slideToggle();
    }
    return false;
  }

  init();
});
