$(function() {
  $("#entries").tablesorter({
    headerTemplate: '{content} {icon}',
    sortInitialOrder: 'asc',
    theme: 'bootstrap',
    widgets : [ "uitheme" ],
  });

  $('#entries').tablesorterPager({
    container: $("#entriesPager"),
    output: '{page}/{totalPages}',
    size: 50,
  });
});
