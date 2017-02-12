$(function() {

  function init() {
    $('#deleteButton').click(function() {
      return confirm('Confirmați ștergerea proiectului?');
    });

    $('#butDown').click(butDownClick);
    $('#butUp').click(butUpClick);

    $(document).bind('keydown', '-', butDownClick);
    $(document).bind('keydown', '+', butUpClick);
    $(document).bind('keydown', '=', butUpClick);
    $(document).bind('keydown', 'shift+=', butUpClick);
  }

  function butDownClick() {
    changeValue(-1);
  }

  function butUpClick() {
    changeValue(+1);
  }

  function changeValue(delta) {
    var val = parseInt($('#errors').val());
    val = Math.max(val + delta, 0);
    $('#errors').val(val);
  }

  init();
});
