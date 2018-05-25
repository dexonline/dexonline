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

    $('.panel-heading').hover(
      function() { $(this).find('a').show(); },
      function() { $(this).find('a').hide(); },
    );

    // prevent panel heading from stealing focus from the links within
    $('.panel-heading a').click(function() {
      event.stopPropagation();
    });
  }

  init();
});
