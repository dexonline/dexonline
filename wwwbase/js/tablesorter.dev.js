/* Functions on top of the jQuery tablesorter plugin */

var order = 'aăâbcdefghiîjklmnopqrsștțuvwxyz';
var equiv = [ 'åàáäçêèéìíïôòóöûùúü', 'aaaaceeeiiioooouuuu' ];

/* Sortable tables imported from MediaWiki have the "sortable" class. Make them sortable here too. */
function tablesorterMediaWikiInit() {
  // The CSS theme expects to see this class
  $('table.sortable').addClass('tablesorter-blue');

  // Add the <thead> element. Mediawiki does not give us that.
  $('table.sortable').prepend(
    $('<thead></thead>').append($('table.sortable tr:first').remove())
  );

  $('table.sortable').bind('tablesorter-initialized', tablesorterEnd);
  $('table.sortable').tablesorter({
    textExtraction: extractAscii,
  });
  $('table.sortable').bind('sortStart', tablesorterStart);
  $('table.sortable').bind('sortEnd', tablesorterEnd);
}

// Renumber the 31 Romanian letters with ASCII codes 60-90. Discard other characters.
function extractAscii(node) {
  var s = $(node).text().toLowerCase();
  var result = '';
  for (var i = 0; i < s.length; i++) {
    var c = s.charAt(i);

    // Canonicalize some characters: 'é' is the same as 'e' etc.
    var pos = equiv[0].indexOf(c);
    if (pos != -1) {
      c = equiv[1].charAt(pos);
    }

    // Now look it up: 'ă' is NOT the same as 'a' etc.
    pos = order.indexOf(c);
    if (pos != -1) {
      result += String.fromCharCode(pos + 60);
    }
  }
  return result;
}

// Remove the extra header rows
function tablesorterStart(e, table) {
  $('table.sortable th').parent().not(':first').remove();
}

// Replace the extra header rows
function tablesorterEnd(e, table) {
  var html = $('table.sortable tr:first').html();
  $('table.sortable tr:nth-child(20n)').after('<tr>' + html + '</tr>');
}
