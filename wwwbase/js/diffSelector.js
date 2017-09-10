$(function () {
  $('#engine').change( function () {
        if ($(this).val() == "2") {
          $('#granularity').prop("hidden", true);
          $('#message').prop("hidden", false);
        } else {
          $('#granularity').prop("hidden", false);
          $('#message').prop("hidden", true);
        }
    });
});    
