$(function() {
  $("#projectTable").tablesorter({
    headerTemplate: '{content} {icon}',
    sortInitialOrder: 'asc',
    theme: 'bootstrap',
    widgets : [ "uitheme" ],
  });

  $('#projectTable').tablesorterPager({
    container: $("#projectPager"),
    output: '{page}/{totalPages}',
    size: 15,
  });

  $('#includePublic').change(function() {
    if ($(this).prop('checked')) {
      window.location = '?includePublic';
    } else {
      // remove query string
      window.location = window.location.href.split('?')[0];
    }
  });

});
