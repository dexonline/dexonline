$(function() {
  /* Page modal code (loading and displaying original (scanned) images of dictionary pages) */
  const SPINNER_WIDTH = 70;
  var sourceId;
  var word;
  var volume;
  var page;
  
  function init() {
    $('#pageModal').on('shown.bs.modal', modalShown);
    $('#prevPage').click(prevPageClick);
    $('#nextPage').click(nextPageClick);
  }

  function modalShown(event) {
    var link = $(event.relatedTarget); // link that triggered the modal
    sourceId = link.attr('data-sourceId');
    word = link.attr('data-word');
    volume = '';
    page =  '';
    loadPage();
  }

  function prevPageClick() {
    page--;
    word = '';
    loadPage();
  }

  function nextPageClick() {
    page++;
    word = '';
    loadPage();
  }

  function loadPage() {
    $('#pageImage')
      .attr('src', wwwRoot + 'img/spinning-circles.svg')
      .width(SPINNER_WIDTH)
      .show();
    $('#pageModal .alert').hide();

    $.get(wwwRoot + 'ajax/getPage.php',
          {
            sourceId: sourceId,
            word: word,
            volume: volume,
            page: page,
          })
      .done(function(data) {
        $('#pageImage')
          .attr('src', 'data:image/png;base64,' + data.img)
          .width('100%');
        volume = data.volume;
        page = data.page;
      })
      .fail(function(e) {
        $('#pageImage').hide();
        $('#pageModal .alert').text(e.responseText).show();
      });
  }

  init();
});
