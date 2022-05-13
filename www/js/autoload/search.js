$(function() {

  const RESULTS_TAB_ID = '#tab_0';
  const RESULTS_TAB_BUTTON = 'button[data-bs-target="' + RESULTS_TAB_ID + '"]';

  function init() {
    $('#toggleNotRecommended').click(function() {
      $('li.notRecommended').toggleClass('notRecommendedShown notRecommendedHidden');
      return false;
    });

    $('#toggleElision').click(function() {
      $('li.elision').toggleClass('elisionShown elisionHidden');
      return false;
    });

    $('button[data-bs-toggle="tab"]').on('click', pushHistory);
    window.addEventListener('popstate', popHistory);

    var tabAdvertiser = $('#tabAdvertiser');
    if (tabAdvertiser.length) {
      new bootstrap.Popover(tabAdvertiser, {
        container: 'body',
        content: $('#tabAdvertiserContent').html(),
        // otherwise clicking on links won't work
        delay: { 'show': 0, 'hide': 100 },
        html: true,
        placement: 'bottom',
        trigger: 'focus',
      });
      tabAdvertiser.click(function() { return false; });
    }

    $('.cat-link').click(scrollToSourceType);
    $('.results-tab-link').click(showResultsTab);

    showSynthesisNotice();
    $('#btn-hide-synthesis-notice').click(hideSynthesisNotice);

    $(document).on('shown.bs.tab', RESULTS_TAB_BUTTON, collapseReadMore);

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

        // Move the banner down a few meanings, but
        // * not lower than 2/3 of the window height;
        // * only if followed by more meanings;
        var selector = '#tab_2 .depth-0:not(:first)';
        $(selector).slice(0,3).each(function() {
          var top = $(this).offset().top;
          if (top + 100 < 2 * h / 3) {
            pos = $(this);
          }
        });

        if (pos) {
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
    var firstTab = $('.nav-tabs button').attr('data-bs-target');
    var state = e.state || firstTab; // it's null for the original page
    var btn = $('button[data-bs-target="' + state + '"]');
    var tab = new bootstrap.Tab(btn);
    tab.show();
  }

  /**
   * For unknown reasons, implementing these links as regular #fragments makes
   * the browser switch to the tree tab when the user clicks a link. So we
   * implement the links ourselves.
   */
  function scrollToSourceType() {
    var sel = $(this).attr('href');
    var header = $(sel);
    header[0].scrollIntoView();
    return false;
  }

  function showResultsTab() {
    $('button[data-bs-target="#tab_0"]').click();
    return false;
  }

  {
    let called = false;

    function collapseReadMore() {
      if (!called) {
        called = true;
        $(RESULTS_TAB_ID + ' .read-more').readMore();
      }
    }
  }

  function showSynthesisNotice() {
    if (localStorage.getItem('synthesisNoticeHidden') != 1) {
      $('#notice-synthesis').show();
    }
  }

  function hideSynthesisNotice() {
    $('#notice-synthesis').slideToggle();
    localStorage.setItem('synthesisNoticeHidden', 1);
  }

  init();

});
