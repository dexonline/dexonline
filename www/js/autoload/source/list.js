$(function() {

  $('.sourceName').each(function() {
    new bootstrap.Popover($(this), {
      container: 'body',
      content: $(this).next().html(),
      delay: { 'show': 200, 'hide': 0 },
      html: true,
      placement: 'bottom',
      title: $(this).html(),
      trigger: 'hover',
    });
  });

  // if there is a highlighted row, scroll to it
  var h = $('#highlightedSource');
  if (h.length) {
    $('html,body').animate({
      scrollTop: h.offset().top - $(window).height()/2
    }, 1000);
  }

});
