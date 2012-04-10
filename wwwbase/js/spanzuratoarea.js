var lives = 6;
var lettersLeft = word.length;

function hangman_updateLives() {
  $('#hangmanPic').attr('src', wwwRoot + 'img/hangman/hangman' + lives + '.png');
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
  field.attr('disabled', 'disabled');
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
  var ok = hangman_updateLetters(field);
  if (ok) {
    field.addClass('buttonGuessed');
  } else {
    lives--;
    hangman_updateLives();
    field.addClass('buttonMissed');
  }

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

$(function() {
  $('.letterButtons').click(function() { hangman_letterPressed($(this)); });
  $('#hintButton').click(function() { hangman_hint($(this)); });
  $('.newGame').click(function() { hangman_newGame($(this).attr('name').split('_', 2)[1]); });
});
