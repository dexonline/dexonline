$(function() {
  function init() {
    $('#calendar').datepicker({
      autoclose: true,
      format: 'yyyy-mm',
      keyboardNavigation: false,
      language: 'ro',
      minViewMode: 'months',
      todayBtn: 'linked',
      todayHighlight: true,
      weekStart: 1,
    });

    // for some reason the div steals focus despite event.stopPropagation()
    $('.card-header a').click(function() {
      window.location = $(this).attr('href');
    });
  }

  init();
});
