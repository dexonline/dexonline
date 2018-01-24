$(function() {
  function init() {
    $('.defToggle').click(toggleDefinitions);
  }

  function toggleDefinitions() {
    var lexemeId = $(this).data('lexemeId');
    var div = $('#' + $(this).data('divId'));

    // If the definitions are already loaded, then just toggle the div's visibility.
    if (trim(div.html()) == '') {
      $.get(wwwRoot + 'ajax/getDefinitionsForLexem.php?lexemeId=' + lexemeId)
        .done(function(data) { div.html(data).stop().slideDown(); })
        .fail('Nu pot descărca lista de definiții.');
    } else {
      div.stop().slideToggle();
    }
    return false;
  }

  init();
});
