$(function() {
  initSelect2('#userId', 'ajax/getUsersById.php', {
    ajax: createUserAjaxStruct(),
    minimumInputLength: 3,
    placeholder: 'alegeți un utilizator',
  });

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

});
