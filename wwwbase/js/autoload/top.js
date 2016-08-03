$(function() {
  $("#manualTop, #bulkTop").tablesorter({
    headerTemplate: '{content} {icon}',
    sortInitialOrder: 'desc',
    theme: 'bootstrap',
    widgets : [ "uitheme" ],
  });
  
  $('#manualTop').tablesorterPager({
    container: $("#manualTopPager"),
    output: '{page}/{totalPages}',
    size: 15,
  });
});
