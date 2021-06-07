$(function() {

  $('#includePublic').change(function() {
    if ($(this).prop('checked')) {
      window.location = '?includePublic';
    } else {
      // remove query string
      window.location = window.location.href.split('?')[0];
    }
  });

});
