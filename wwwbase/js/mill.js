function dynamo_optionPressed(field) {
  if(answer == field.val()) {
    field.addClass('buttonGuessed');
  } else {
    field.addClass('buttonMissed');
    $('.optionButtons[value="' + answer + '"]').addClass('buttonHinted');
  }
  setTimeout(function() {document.location.reload(true);},2000);
}

$(function() {
  $('.optionButtons').click(function() { dynamo_optionPressed($(this)); });
  $('.optionButtons').focus();//Make sure to take focus from the search bar, this is the best choice as putting it on a button would make the use of space for clues impossible.
  
  document.addEventListener('keypress', function(event) {
    if(String.fromCharCode(event.charCode) == "1" || String.fromCharCode(event.charCode) == "2" || String.fromCharCode(event.charCode) == "3" || String.fromCharCode(event.charCode) == "4")
      dynamo_optionPressed($('.optionButtons[value="' + String.fromCharCode(event.charCode) + '"]'));
  });
});
