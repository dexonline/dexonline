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
  }

  init();
});
