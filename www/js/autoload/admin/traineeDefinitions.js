$(function() {
  $("#defsTable").tablesorter({
    headerTemplate: '{content} {icon}',
    sortList: [[3,1]],
    theme: 'bootstrap',
    widgets: [ "uitheme" ],
  });

  $('#defsTable').tablesorterPager({
    container: $("#defsPager"),
    output: '{page}/{totalPages}',
    size: 50,
  });
});
