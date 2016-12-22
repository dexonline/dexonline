$(function() {

  $('.notRecommendedShowHide').click(function() {
    $('span.notRecommended').toggleClass("toggleOff");
    $(this).text($(this).text() == '(arată)' ? '(ascunde)' : '(arată)');
  });

});
