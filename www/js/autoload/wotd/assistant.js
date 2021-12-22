$(function() {
  function init() {
    new Datepicker(document.getElementById('calendar'), {
      autohide: true,
      buttonClass: 'btn',
      format: 'yyyy-mm',
      language: 'ro',
      pickLevel: 1,
      startView: 1,
      todayBtn: true,
      todayBtnMode: 1,
    });

    // for some reason the div steals focus despite event.stopPropagation()
    $('.card-header a').click(function() {
      window.location = $(this).attr('href');
    });
  }

  init();
});
