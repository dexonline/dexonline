$(function() {

  const THRESHOLD = 100; // distance from the top that causes the button to appear

  var button;

  function init() {
    // create the button
    button = $(
      '<button id="scrollTopButton" title="înapoi sus" class="btn btn-secondary" type="button">' +
        '<i class="material-icons">expand_less</i>' +
        '</button>'
    ).appendTo($('body'));

    window.onscroll = scrollHandler;
    button.click(function() { $(window).scrollTop(0); });
  }

  function scrollHandler() {
    if ($(window).scrollTop() >= THRESHOLD) {
      button.show();
    } else {
      button.hide();
    }
  }

  init();

});
