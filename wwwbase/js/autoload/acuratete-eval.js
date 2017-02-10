$(function() {
  $('#deleteButton').click(function() {
    return confirm('Confirmați ștergerea proiectului?');
  });

  $('#butDown').click(function() {
    changeValue(-1);
  });

  $('#butUp').click(function() {
    changeValue(+1);
  });

  function changeValue(delta) {
    var val = parseInt($('#errors').val());
    val = Math.max(val + delta, 0);
    $('#errors').val(val);
  }

});
