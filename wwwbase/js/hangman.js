var lives = 6;
var lettersLeft = word.length;

function hangman_updateLives() {
  $('.hangmanPic').css('background-position', '-' + (lives * 250 + 10) + 'px -10px');
  $("#livesLeft").fadeOut(function() {
    $(this).text(lives).fadeIn();
  });
}

function hangman_gameOver() {
  if (!lives || !lettersLeft) {
    jQuery.noticeAdd({
      text: lives ? 'Felicitări, ai câștigat!' : 'Ne pare rău, ai pierdut.',
      stayTime: 2000,
    });
    $('.letters').each(function(index) {
      $(this).val(word.charAt(index).toUpperCase());
    });
    $('#hintButton').attr('disabled', 'disabled');
    $('.letterButtons').each(function() {
      $(this).attr('disabled', 'disabled');
    });
    $('#resultsWrapper').slideToggle();
  }
}

/* Returns true if any letters were uncovered */
function hangman_updateLetters(field) {
  var letter = field.val().toLowerCase();
  var ok = 0;
  for (i = 0; i < word.length; i++) {
    if (letter == word.charAt(i)) {
      $('.letters')[i].value = letter.toUpperCase();
      ok = 1;
      lettersLeft--;
    }
  }
  return ok;
}

function hangman_letterPressed(field) {
  if(field.is(':disabled')) { //because of keyboard support one can press already pressed buttons
    return;
  }
  var ok = hangman_updateLetters(field);
  if (ok) {
    field.addClass('buttonGuessed');
  } else {
    lives--;
    hangman_updateLives();
    field.addClass('buttonMissed');
  }
  field.attr('disabled', 'disabled');
  hangman_gameOver();
}
 
function hangman_hint() {
  // Grab a random letter that wasn't revealed yet
  letters = $('.letters');

  var i;
  do {
    i = Math.floor(Math.random() * word.length);
  } while (letters[i].value != '');
  var value = word.charAt(i).toUpperCase();

  // Simulate a press of the corresponding button
  var button = $('.letterButtons[value="' + value + '"]');
  button.addClass('buttonHinted');
  hangman_updateLetters(button);
  lives = (lives >= 2) ? lives - 2 : 0;
  hangman_updateLives();
  hangman_gameOver();
}

function hangman_newGame(difficulty) {
  window.location = "spanzuratoarea?d=" + difficulty;
}

function revealLetters(difficulty) {
  if (difficulty > 2) {
    return;
  }
  firstLetter = word.charAt(0).toUpperCase();
  lastLetter = word.charAt(word.length - 1).toUpperCase();
  var field = $('.letterButtons[value="' + firstLetter + '"]');
  hangman_letterPressed(field);

  if (lastLetter != firstLetter) {
    field = $('.letterButtons[value="' + lastLetter + '"]');
    hangman_letterPressed(field);
  }
}

$(function() {
  $('.letterButtons').click(function() { hangman_letterPressed($(this)); });
  $('#hintButton').click(function() { hangman_hint(); });
  $('.newGame').click(function() { hangman_newGame($(this).attr('name').split('_', 2)[1]); });
  revealLetters(difficulty);
  $('.letters').focus();//Make sure to take focus from the search bar, this is the best choice as putting it on a button would make the use of space for clues impossible.
  
  document.addEventListener('keypress', function(event) {
    if(String.fromCharCode(event.charCode) == " ")
      hangman_hint();
    else if(String.fromCharCode(event.charCode) == "1" || String.fromCharCode(event.charCode) == "2" || String.fromCharCode(event.charCode) == "3" || String.fromCharCode(event.charCode) == "4")
      hangman_newGame(String.fromCharCode(event.charCode));
    else
      hangman_letterPressed($('.letterButtons[value="' + String.fromCharCode(event.charCode).toUpperCase() + '"]'));
  });

});
