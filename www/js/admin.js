$(function() {
  /* Page modal code (loading and displaying original (scanned) images of dictionary pages) */
  var sourceId;
  var volume;
  var page;
  var word;
  var urlPattern;

  function init() {
    // move the modal outside of any tabs
    $('#pageModal').appendTo('body');

    $('#pageModal').on('shown.bs.modal', modalShown);
    $('#pageModal').on('hidden.bs.modal', modalHidden);
    $('.prevPage').click(prevPageClick);
    $('.nextPage').click(nextPageClick);
    $('.pageForWord').keypress(pageForWordKeypress);

    // We need tabindex="-1" on the modal so that it closes when we press Escape,
    // but that makes us unable to type in the select2 within the modal.
    // This seems to fix it. See:
    // https://github.com/select2/select2/issues/600#issuecomment-102857595
    // TODO: Is this still necessary with Bootstrap 5?
    // $.fn.modal.Constructor.prototype.enforceFocus = $.noop;
  }

  function modalShown(event) {
    $(document).bind('keydown', 'left', prevPageClick);
    $(document).bind('keydown', 'right', nextPageClick);

    var link = $(event.relatedTarget); // link that triggered the modal
    sourceId = link.attr('data-sourceId');
    word = link.attr('data-word');
    volume = link.attr('data-volume');
    page = link.attr('data-page');
    urlPattern = link.attr('url-pattern');

    $('#pageModal .sourceDropDown').val(sourceId).trigger('change');

    if (volume === 0 || page === 0) {
      getPageVolume();
    } else {
      loadPage();
    }
  }

  function modalHidden() {
    $(document).unbind('keydown', prevPageClick);
    $(document).unbind('keydown', nextPageClick);
  }

  function prevPageClick() {
    page--;
    loadPage();
  }

  function nextPageClick() {
    page++;
    loadPage();
  }

  function pageForWordKeypress(e) {
    if (e.which === 13) {
      word = $(this).val().trim();
      sourceId = $(this).siblings('.sourceDropDown').val();

      if (word) {
        getPageVolume();
      }

      return false;
    }
  }

  function getPageVolume() {
    showLoading();

    // resolve the sourceId + word to a volume + page
    $.get(wwwRoot + 'ajax/getPage.php', { sourceId: sourceId, word: word, })
    .done(function(data) {
      volume = data.volume;
      page = data.page;
      loadPage();
    })
    .fail(function(e) {
      showNotice(e.responseText);
    });
  }

  function loadPage() {
    showLoading();

    $('#pageModal .sourceDropDown').val(sourceId).trigger('change');

    var url = sprintf(URL_PATTERN, sourceId, volume, page);

    $('#pageImage').one('load', function() {
      $('#loading').hide();
      $('#pageImage').show();
    }).one('error', function() {
      showNotice('Imaginea cerută nu există.');
    }).attr('src', url);
  }

  function showLoading() {
    $('#pageModal .notice').hide();
    $('#loading').show();
  }

  function showNotice(message) {
    $('#loading').hide();
    $('#pageImage').hide();
    $('#pageModal .notice-body').text(message)
    // escalate to !important to trump d-flex, which is !important
    $('#pageModal .notice').attr('style', 'display: flex !important');
  }

  init();
});

/********************** select2 for .sourceDropDown **********************/

$(function() {
  $('.sourceDropDown').each(function() {
    /**
     * Don't pass in data-dropdown-parent directly because that causes a JS error.
     * See https://github.com/select2/select2/issues/4289
     */
    var ddParent = $( $(this).data('ddParent') || document.body );
    $(this).select2({
      dropdownParent: ddParent,
      templateResult: formatSource,
      templateSelection: formatSource,
    });
  });
});
