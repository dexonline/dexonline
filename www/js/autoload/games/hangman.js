$(function() {

  const STORAGE_LIST = 'hangman-list-%d'; // word list
  const STORAGE_POS = 'hangman-pos-%d';   // position
  const STORAGE_TS = 'hangman-ts-%d';    // timestamp when list fetched
  const WORD_LIST_URL = 'https://dexonline.ro/static/download/word-list-hangman-%d.txt';

  const START_LIVES = 6;
  const DEFAULT_LEVEL = 2;
  const MAX_LEVEL_REVEAL_FIRST_LAST_LETTERS = 2;
  const CACHE_DURATION = 86400 * 30; // one month in seconds

  var gameState = {
    lettersLeft: null,
    level: DEFAULT_LEVEL,
    lives: null,
    word: null,
  };
  var endGameModal;

  // level-specific localStorage getter
  function lsGet(key) {
    return localStorage.getItem(sprintf(key, gameState.level));
  }

  // level-specific localStorage setter
  function lsSet(key, value) {
    localStorage.setItem(sprintf(key, gameState.level), value);
  }

  function ensureWordList() {

    return new Promise(function(resolve, reject) {
      var now = Math.floor(Date.now() / 1000);
      var ts = Number(lsGet(STORAGE_TS));

      if (ts && (ts >= now - CACHE_DURATION)) {
        resolve(null); // already in cache
        return;
      }

      $.ajax(sprintf(WORD_LIST_URL, gameState.level))
        .done(function(resp) {
          var list = resp.split('\n');
          shuffle(list);
          lsSet(STORAGE_LIST, list.join('\n'));
          lsSet(STORAGE_POS, 0);
          lsSet(STORAGE_TS, now);
          resolve(null);
        })
        .fail(function() {
          reject(new Error(_('hangman-word-list-download-error')));
        });
    });

  }

  function pickWord() {
    var list = lsGet(STORAGE_LIST).split('\n');
    var pos = Number(lsGet(STORAGE_POS));
    gameState.word = list[pos].toUpperCase();
    gameState.lettersLeft = gameState.word.length;

    // advance the list pointer
    pos = (pos + 1) % list.length;
    lsSet(STORAGE_POS, pos);
  }

  function createLetters() {
    var l =  gameState.word.length;

    var btns = $('.output input');
    btns.slice(l).remove(); // delete extras
    btns.val('');

    for (i = 0; i < l - btns.length; i++) {
      btns.first().clone().insertAfter(btns.last());
    }
  }

  // Shows all instances of the letter. Returns true iff any letters were shown.
  function updateLetters(letter) {
    var result = false;

    for (i = 0; i < gameState.word.length; i++) {
      if (letter == gameState.word.charAt(i)) {
        $('.letters')[i].value = letter;
        result = true;
        gameState.lettersLeft--;
      }
    }
    return result;
  }

  // Handler for letter buttons (accessed via mouse or keyboard).
  function letterPressed() {
    // because of keyboard support one can press already pressed buttons
    if ($(this).is(':disabled')) {
      return;
    }
    var ok = updateLetters($(this).val());
    if (ok) {
      $(this).addClass('buttonGuessed');
    } else {
      gameState.lives--;
      updateLives();
      $(this).addClass('buttonMissed');
    }
    $(this).attr('disabled', 'disabled');
    checkGameOver();
  }

  // Display the first and last letters by simulating button presses.
  function revealLetters() {
    if (gameState.level <= MAX_LEVEL_REVEAL_FIRST_LAST_LETTERS) {
      firstLetter = gameState.word.charAt(0);
      lastLetter = gameState.word.charAt(gameState.word.length - 1);
      var btn = $('.letterButtons[value="' + firstLetter + '"]');
      btn.click();

      if (lastLetter != firstLetter) {
        btn = $('.letterButtons[value="' + lastLetter + '"]');
        btn.click();
      }
    }
  }

  function refreshLetterButtons() {
    $('.letterButtons')
      .removeClass('buttonGuessed buttonMissed buttonHinted')
      .removeAttr('disabled');
  }

  function startGame() {
    ensureWordList().then(
      // handle the case when we do have a word list
      function() {
        gameState.lives = START_LIVES;
        updateLives();
        $('#resultsWrapper').hide();
        refreshLetterButtons();
        pickWord();
        createLetters();
        revealLetters();
      },
      function(error) {
        alert(error);
      }
    );
  }

  function init() {
    endGameModal = new bootstrap.Modal(document.getElementById('endModal'));
    $('.letterButtons').click(letterPressed);
    $('#hintButton').click(hint);
    $('.newGameControls button').click(newGame);

    document.addEventListener('keypress', function(event) {
      var chr = String.fromCharCode(event.charCode).toUpperCase();
      if (chr == ' ') {
        hint();
      } else if (chr >= '1' && chr <= '4') {
        newGame(null, chr);
      } else if (chr.match(/[A-ZĂÎȘȚÂ]/g)) {
        // creating letterButton & matching key only with Hangman relevant chars
        var btn = $('.letterButtons[value="' + chr + '"]');
        btn.click();
      }
    });

    startGame();
  }

  function updateLives() {
    var l = gameState.lives;
    $('.hangmanPic').css('background-position', '-' + (l * 250 + 10) + 'px -10px');
    $("#livesLeft").fadeOut(function() {
      $(this).text(l).fadeIn();
    });
  }

  function checkGameOver() {
    if (!gameState.lives || !gameState.lettersLeft) {
      $('.letters').each(function(index) {
        $(this).val(gameState.word.charAt(index));
      });
      $('#hintButton, .letterButtons').attr('disabled', 'disabled');

      if (gameState.lives) {
        $('#endModal .win').show();
        $('#endModal .lose').hide();
      } else {
        $('#endModal .lose').show();
        $('#endModal .win').hide();
      }

      $.ajax(wwwRoot + 'ajax/getOfficialDefinitions.php?word=' + gameState.word)
        .done(function(resp) {
          $('#resultsWrapper .card-body').html(resp.html);
          $('#resultsWrapper').show();
        });

      endGameModal.show();
      setTimeout(function() {
        endGameModal.hide();
      }, 2000);

      // track played game
      $.post(wwwRoot + 'ajax/trackGame', { game: 'hangman' });
    }
  }

  function hint() {
    // Grab a random letter that wasn't revealed yet
    letters = $('.letters');

    var i;
    do {
      i = Math.floor(Math.random() * gameState.word.length);
    } while (letters[i].value != '');

    // Simulate a press of the corresponding button
    var button = $('.letterButtons[value="' + gameState.word.charAt(i) + '"]');
    button.addClass('buttonHinted');
    updateLetters(gameState.word.charAt(i));
    gameState.lives = Math.max(gameState.lives - 2, 0);
    updateLives();
    checkGameOver();
  }

  // accessed via button clicks or the keys 1-4
  function newGame(evt, level) {
    if (!level) {
      level = $(this).data('level');
    }
    gameState.level = level;
    startGame();
  }

  init();

});
