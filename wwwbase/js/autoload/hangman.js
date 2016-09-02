$(function() {

  var lives = 6;
  var lettersLeft = word.length;

  function init() {
    $('.letterButtons').click(function() { letterPressed($(this)); });
    $('#hintButton').click(function() { hint(); });
    $('.newGameControls button').click(function() {
        newGame($(this).attr('data-level'));
    });
    revealLetters(difficulty);
    $('.letters').focus();
    
    document.addEventListener('keypress', function(event) {
      var char = String.fromCharCode(event.charCode).toUpperCase();
      if(char == ' ') {
        hint();
      } else if (char >= '1' && char <= '4') {
        newGame(char);
      } else if (char.match(/[A-ZĂÎȘȚÂ]/g)) {
        //creating letterButton & matching key only with Hangman relevant chars
        var field = $('.letterButtons[value="' + char + '"]');
        letterPressed(field);
      }
    });
  }

  function updateLives() {
    $('.hangmanPic').css('background-position', '-' + (lives * 250 + 10) + 'px -10px');
    $("#livesLeft").fadeOut(function() {
      $(this).text(lives).fadeIn();
    });
  }

  function gameOver() {
    if (!lives || !lettersLeft) {
      jQuery.noticeAdd({
        text: lives ? 'Felicitări, ai câștigat!' : 'Ne pare rău, ai pierdut.',
        stayTime: 2000,
      });
      $('.letters').each(function(index) {
        $(this).val(word.charAt(index));
      });
      $('#hintButton').attr('disabled', 'disabled');
      $('.letterButtons').each(function() {
        $(this).attr('disabled', 'disabled');
      });
      $('#resultsWrapper').stop().slideToggle();
    }
  }

  /* Returns true if any letters were uncovered */
  function updateLetters(letter) {
    var ok = 0;
    for (i = 0; i < word.length; i++) {
      if (letter == word.charAt(i)) {
        $('.letters')[i].value = letter;
        ok = 1;
        lettersLeft--;
      }
    }
    return ok;
  }

  function letterPressed(field) {
    if(field.is(':disabled')) { //because of keyboard support one can press already pressed buttons
      return;
    }
    var ok = updateLetters(field.val());
    if (ok) {
      field.addClass('buttonGuessed');
    } else {
      lives--;
      updateLives();
      field.addClass('buttonMissed');
    }
    field.attr('disabled', 'disabled');
    gameOver();
  }
  
  function hint() {
    // Grab a random letter that wasn't revealed yet
    letters = $('.letters');

    var i;
    do {
      i = Math.floor(Math.random() * word.length);
    } while (letters[i].value != '');

    // Simulate a press of the corresponding button
    var button = $('.letterButtons[value="' + word.charAt(i) + '"]');
    button.addClass('buttonHinted');
    updateLetters(word.charAt(i));
    lives = (lives >= 2) ? lives - 2 : 0;
    updateLives();
    gameOver();
  }

  function newGame(difficulty) {
    window.location = "spanzuratoarea?d=" + difficulty;
  }

  function revealLetters(difficulty) {
    //showing the '-' character
    updateLetters('-');
    if (difficulty > 2) {
      return;
    }
    firstLetter = word.charAt(0);
    lastLetter = word.charAt(word.length - 1);
    var field = $('.letterButtons[value="' + firstLetter + '"]');
    letterPressed(field);

    if (lastLetter != firstLetter) {
      field = $('.letterButtons[value="' + lastLetter + '"]');
      letterPressed(field);
    }
  }

  init();

});
