$(function() {

  const BUTTON_ID = 'scrollTopButton';
  const THRESHOLD = 100; // distance from the top that causes the button to appear

  function init() {
    // create the button
    var button = $('<button><i class="glyphicon glyphicon-chevron-up"></i></button>')
        .attr({
          id: BUTTON_ID,
          title: 'înapoi sus',
        });
    $('body').append(button);

    window.onscroll = scrollHandler;
    button.click(function() { $(window).scrollTop(0); });
  }

  function scrollHandler() {
    if ($(window).scrollTop() >= THRESHOLD) {
      $('#' + BUTTON_ID).show();
    } else {
      $('#' + BUTTON_ID).hide();
    }
  }

  init();

});
