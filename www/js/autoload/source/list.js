$(function() {

  // if there is a highlighted row, scroll to it
  var h = $('#highlightedSource');
  if (h.length) {
    $('html,body').animate({
      scrollTop: h.offset().top - $(window).height()/2
    }, 1000);
  }

});
