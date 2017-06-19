$(function() {

  $('ins, del').each(function() {
    $(this).html($(this).text().split(' ').join('␣&#8203;'));
  });

});
