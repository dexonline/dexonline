$(function() {

  $('ins, del').each(function() {
    $(this).html($(this).html().split(' ').join('␣&#8203;')); // $(this).text().split is losing html tags on greater granularity that word
  });

});
