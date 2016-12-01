$(function() {

  $('ins, del').each(function() {
    $(this).text($(this).text().split(' ').join('␣'));
  });

});
