// snippet fetched from https://bootsnipp.com/snippets/featured/input-spinner-with-min-and-max-values
// @ added stepping functionality
$(function(){

    $('.spinner .btn:first-of-type').on('click', function() {
      var btn = $(this);
      var input = btn.closest('.spinner').find('input');
      var step = parseInt(input.attr('step'));
      if (input.attr('max') == undefined || parseInt(input.val()) < parseInt(input.attr('max'))) {    
        input.val(parseInt(input.val(), 10) + step);
      } else {
        btn.next("disabled", true);
      }
    });
    $('.spinner .btn:last-of-type').on('click', function() {
      var btn = $(this);
      var input = btn.closest('.spinner').find('input');
      var step = parseInt(input.attr('step'));
      if (input.attr('min') == undefined || parseInt(input.val()) > parseInt(input.attr('min'))) {    
        input.val(parseInt(input.val(), 10) - step);
      } else {
        btn.prev("disabled", true);
      }
    });

})