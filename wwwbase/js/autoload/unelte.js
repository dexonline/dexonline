$(function() {

  var t = $('#toolTable tbody').eq(0);
  var r = t.find('tr');
  var cols = r.length;
  var rows = r.eq(0).find('td, th').length;

  var cell, next, tem, i = 0;
  var tb = $('<tbody></tbody>');

  while (i < rows) {
    cell = 0;
    tem = $('<tr></tr>');
    while (cell < cols) {
      next = r.eq(cell++).find('td, th').eq(0);
      tem.append(next);
    }
    tb.append(tem);
    ++i;
  }
  $('#toolTable').append(tb);

  $('#toolTable').show();
});
