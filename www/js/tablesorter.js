$(function() {
  const ORDER = 'aăâbcdefghiîjklmnopqrsștțuvwxyz';
  const EQUIV = [ 'åàáäçêèéìíïôòóöûùúü', 'aaaaceeeiiioooouuuu' ];

  $('.tablesorter').each(function() {
    // Tablesorter depends on a <thead>. Some pages, like Mediawiki articles,
    // don't give us one. In those cases, move the first row from <tbody> into
    // a new <thead>.
    if (!$(this).find('thead').length) {
      $(this).prepend(
        $('<thead></thead>').append($(this).find('tbody tr:first').remove())
      );
    }

    // Now initialize it
    $(this).tablesorter({
      ignoreCase : true,
      textExtraction: extractAscii,
      theme: 'bootstrap',
    });
  });

  $('.ts-pager').tablesorterPager({
    container: $(this).find('tfoot'),
    output: '{page}/{totalPages}',
    size: 15,
  });

  // Renumber the 31 Romanian letters with ASCII codes 60-90. Discard other characters.
  function extractAscii(node) {
    // return data-text when available
    var data = $(node).data('text');
    if (data != undefined) {
      return data;
    }

    var s = $(node).text().toLowerCase();
    var result = '';
    for (var i = 0; i < s.length; i++) {
      var c = s.charAt(i);

      // Canonicalize some characters: 'é' is the same as 'e' etc.
      var pos = EQUIV[0].indexOf(c);
      if (pos != -1) {
        c = EQUIV[1].charAt(pos);
      }

      // Now look it up: 'ă' is NOT the same as 'a' etc.
      pos = ORDER.indexOf(c);
      if (pos != -1) {
        result += String.fromCharCode(pos + 60);
      }
    }
    return result;
  }

});
