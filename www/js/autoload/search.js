$(function() {

  function init() {
    $('#toggleNotRecommended').click(function() {
      $('li.notRecommended').toggleClass('notRecommendedShown notRecommendedHidden');
      return false;
    });

    $('#toggleElision').click(function() {
      $('li.elision').toggleClass('elisionShown elisionHidden');
      return false;
    });

    $('.toggleVariantParadigms').click(function() {
      $(this).siblings('.variantParadigm').stop().slideToggle();
    });

    $('button[data-bs-toggle="tab"]').on('click', pushHistory);
    window.addEventListener('popstate', popHistory);

    new bootstrap.Popover($('#tabAdvertiser'), {
      container: 'body',
      content: $('#tabAdvertiserContent').html(),
      // otherwise clicking on links won't work
      delay: { 'show': 0, 'hide': 100 },
      html: true,
      placement: 'bottom',
      trigger: 'focus',
    });
    $('#tabAdvertiser').click(function() { return false; });

    moveBanner();
  }

  function moveBanner() {
    var placement = $('.banner-section').data('placement');

    switch (placement) {
    case 'default':
      break;

    case 'dynamic':
      var h = $(window).height();
      var pos = null;

      // Move the banner down a few definitions or meanings, but
      // * not lower than 2/3 of the window height;
      // * only if followed by more definitions;
      // * only if there are no images to show.
      var selector =
          '#resultsTab .primaryMeaning:not(:first), ' +
          'h4.etymology, ' +
          '#resultsTab .defWrapper:not(:first)';
      $(selector).slice(0,3).each(function() {
        var top = $(this).offset().top;
        if (top + 100 < 2 * h / 3) {
          pos = $(this);
        }
      });

      if (pos && !$('#gallery').length) {
        $('.banner-section').insertBefore(pos);
      } else {
        $('.banner-section').show();
      }
      break;
    }
  }

  /**
   * Pushes the anchor's data-permalink URL onto the history stack.
   */
  function pushHistory(e) {
    var btn = $(e.target);
    var url = btn.data('permalink');

    if (url) {
      var href = btn.attr('data-bs-target');
      history.pushState(href, document.title, url);
    }
  }

  function popHistory(e) {
    var state = e.state || '#resultsTab'; // it's null for the original page
    var btn = $('button[data-bs-target="' + state + '"]');
    var tab = new bootstrap.Tab(btn);
    tab.show();
  }

  init();

});
