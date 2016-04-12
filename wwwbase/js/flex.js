function mlUpdateDefVisibility(lexemId, divId) {
  var div = $('#' + divId);
  // If the definitions are already loaded, then just toggle the div's visibility.
  if (trim(div.html()) == '') {
    $.get(wwwRoot + 'ajax/getDefinitionsForLexem.php?lexemId=' + lexemId)
      .done(function(data) { div.html(data).slideToggle(); })
      .fail('Nu pot descărca lista de definiții.');
  } else {
    div.slideToggle();
  }
  return false;
}
