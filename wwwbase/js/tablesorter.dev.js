/* Functions on top of the jQuery tablesorter plugin */

var order = 'aăâbcdefghiîjklmnopqrsștțuvwxyz';

/* Sortable tables imported from MediaWiki have the "sortable" class. Make them sortable here too. */
function tablesorterMediaWikiInit() {
  // Add the <thead> element. Mediawiki does not give us that.
  $('table.sortable').prepend(
    $('<thead></thead>').append($('table.sortable tr:first').remove())
  );
  $("table.sortable").tablesorter({
    textExtraction: extractAscii,
  });
}

// Renumber the 31 Romanian letters with ASCII codes 60-90. Discard other characters.
function extractAscii(node) {
  var s = $(node).text().toLowerCase();
  var result = '';
  for (var i = 0; i < s.length; i++) {
    var c = s.charAt(i);
    var pos = order.indexOf(c);
    if (pos != -1) {
      result += String.fromCharCode(pos + 60);
    }
  }
  return result;
}
