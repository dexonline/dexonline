$(function() {
  const WORD_LIST_DIA_URL = 'https://dexonline.ro/static/download/game-word-list-dia.txt';
  const WORD_LIST_URL = 'https://dexonline.ro/static/download/game-word-list.txt';
  const ALPHABET = 'aăâbcdefghiîjklmnopqrsștțuvwxyz';

  const MODE_WORD_SEARCH = 0;
  const MODE_ANAGRAM = 1;

  const MINIMUM_WORD_LENGTH = 3;
  
  const CANVAS_WIDTH = 480;
  const CANVAS_HEIGHT = 320;
  const TILE_WIDTH = 55;
  const TILE_HEIGHT = 75;
  const TILE_PADDING = 10;
  const TOP_Y = 50;
  const BOTTOM_Y = 200;
  const CONTROLS_Y = 290;
  const STATS_HEIGHT = 40; // keep this many pixels vertically when scaling

  const ANIMATION_STEPS = 10; // for letter moves

  const VERIFY_BUTTON_URL = wwwRoot + 'img/scramble/verify-button.png';
  const LETTERS_URL = wwwRoot + 'img/scramble/letters.png';

  const MESSAGES = {
    MSG_CORRECT: {
      text: 'corect',
      style: {
        fill: '#3c763d',
        stroke: '#3c763d',
      },
    },
    MSG_ALREADY_FOUND: {
      text: 'deja găsit',
      style: {
        fill: '#8a6b3d',
        stroke: '#8a6b3d',
      },
    },
    MSG_WRONG: {
      text: 'incorect',
      style: {
        fill: '#a94442',
        stroke: '#a94442',
      },
    },
    MSG_TOO_SHORT: {
      text: 'prea scurt',
      style: {
        fill: '#a94442',
        stroke: '#a94442',
      },
    },
  };
  const MESSAGES_COMMON_STYLE = {
    font: '24px Verdana, sans-serif',
    strokeThickness: 1,
  };

  const NIL = -1;

  var letters;    // letter set
  var legalWords; // words that can be made from the letter set
  var wordsFound; // boolean array indicating which legal words the user has found
  var tiles;      // PIXI sprites
  var topTiles;   // indices into tiles[] or NIL for empty spaces
  var bottomTiles; // ditto
  var wordStem;   // div to be cloned for every legal word
  var wordList, wordListDia; // word lists downloaded from server, without and with diacritics
  var gameParams; // main menu options

  var stage;
  var renderer;
  var messages;
  var gameOverText;
  var gameScene;
  var gameOverScene;

  class Tile extends PIXI.Sprite {
    constructor(letter, pos) {
      // create the sprite
      var index = ALPHABET.indexOf(letter);
      var rectangle = new PIXI.Rectangle(0, TILE_HEIGHT * index, TILE_WIDTH, TILE_HEIGHT);
      var texture = new PIXI.Texture(PIXI.loader.resources[LETTERS_URL].texture, rectangle);
      super(texture);

      // set custom fields
      this.letter = letter;
      this.top = true;
      this.pos = pos;

      this.anchor.set(0.5, 0.5);
      this.position.set(this.getX(), TOP_Y);
      this.interactive = true;
      this.buttonMode = true;
      this.on('pointerup', this.clickTile);
    }

    // returns the X coordinate for this tile
    getX() {
      var wp = TILE_WIDTH + TILE_PADDING;
      return (CANVAS_WIDTH + wp * (2 * this.pos - letters.length + 1)) / 2;
    }

    getY() {
      return this.top ? TOP_Y : BOTTOM_Y;
    }

    // sets up the animation to the pos-th position on the top row (top = true)
    // or bottom row (top = false)
    startAnimation(pos) {
      this.top = !this.top;
      this.pos = pos;

      this.deltaX = this.getX() - this.position.x;
      this.deltaY = this.getY() - this.position.y;
      this.steps = ANIMATION_STEPS;

      this.interactive = false; // disable clicks during moves
    }

    animate() {
      if ('steps' in this) {
        // this tile is going somewhere -- take the next step
        this.steps--;
        var x = this.getX() - this.deltaX * this.steps / ANIMATION_STEPS;
        var y = this.getY() - this.deltaY * this.steps / ANIMATION_STEPS;

        this.position.set(x, y);
        if (!this.steps) {
          delete this.deltaX;
          delete this.deltaY;
          delete this.steps;
          this.interactive = true; // re-enable clicks
        }
      }
    }

    // moves the tile at position pos on row1 to the first open slot on row2
    move(row1, row2) {
      if (row1[this.pos] != NIL) {
        var i = 0;
        while (row2[i] != NIL) {
          i++;
        }
        row2[i] = row1[this.pos];
        row1[this.pos] = NIL;
        
        this.startAnimation(i);
      }
    }

    // moves the tile to the first open slot on the bottom row
    gather() {
      this.move(topTiles, bottomTiles);
    }

    // moves the tile to the first open slot on the top row
    scatter() {
      this.move(bottomTiles, topTiles);
    }

    // sends letters on the bottom row back to the top row
    static scatterLastBottom() {
      var j = bottomTiles.length - 1;
      while ((j >= 0) && (bottomTiles[j] == NIL)) {
        j--;
      }
      if (j >= 0) {
        tiles[bottomTiles[j]].scatter();
      }
    }

    // sends letters on the bottom row back to the top row
    static scatterBottomRow() {
      for (var j = 0; j < bottomTiles.length; j++) {
        if (bottomTiles[j] != NIL) {
          tiles[bottomTiles[j]].scatter();
        }
      }
    }

    clickTile() {
      if (this.top) {
        this.gather();
      } else {
        this.scatter();
      }
    }
  }

  function init() {
    // initialize Pixi
    renderer = PIXI.autoDetectRenderer({
      backgroundColor: 0xffffff,
      width: CANVAS_WIDTH,
      height: CANVAS_HEIGHT,
    });
    $('#canvasWrap').append(renderer.view);
    stage = new PIXI.Container();
    gameScene = new PIXI.Container();
    gameOverScene = new PIXI.Container();
    stage.addChild(gameScene);
    stage.addChild(gameOverScene);
    gameOverScene.visible = false;

    // automatic scaling
    $(window).resize(resize);

    $('#startGameButton').click(startGame);
    $('#restartGameButton').click(restartGame);
    wordStem = $('#wordStem').detach().removeAttr('id');

    // download word lists
    $.when($.get(WORD_LIST_URL),
           $.get(WORD_LIST_DIA_URL))
      .then(function(result, resultDia) {
        wordList = result[0].trim().split('\n');
        wordListDia = resultDia[0].trim().split('\n');
        $('#startGameButton').prop('disabled', false);
      })
      .fail(function() {
        console.log('Nu pot descărca listele de cuvinte.');
      });

    drawCanvasElements();
  }

  // runs whenever a new game starts
  function startGame() {
    gameParams = {
      mode: parseInt($('.active input[name="mode"]').val()),
      level: parseInt($('.active input[name="level"]').val()),
      useDiacritics: parseInt($('.active input[name="useDiacritics"]').val()),
      seconds: parseInt($('.active input[name="seconds"]').val()),
    };

    getNewLetters();

    $('#optionsDiv').collapse('hide');
    $('#wordCountDiv').toggle(gameParams.mode == MODE_WORD_SEARCH);
    $('#mainMenu').hide();
    $('#gamePanel').show();
    $('#score').text('0');
    $('#foundWords').text('0');
    $('#maxWords').text(legalWords.length);

    resize();
    scrollIntoView();
    $(document).keypress(letterHandler);
    $(document).keydown(specialKeyHandler);
    startTimer();
    gameLoop();
  }

  // generate a letter set
  function getLetters(wordList, level) {
    var s;

    // choose a random word
    do {
      s = wordList[Math.floor(Math.random() * wordList.length)];
    } while (s.length != level);

    // shuffles the letters
    var a = s.split('');

    for (var i = a.length - 1; i; i--) {
      var j = Math.floor(Math.random() * (i + 1));
      var tmp = a[i];
      a[i] = a[j];
      a[j] = tmp;
    }

    return a.join('');
  }

  // builds the frequency table of a string
  function frequencyTable(s) {
    var f = [];

    for (var i = 0; i < ALPHABET.length; i++) {
      f[ALPHABET[i]] = 0;
    }
    for (var i = 0; i < s.length; i++) {
      f[s[i]]++;
    }

    return f;
  }

  // builds the legal words list constrained by the frequency table
  function getLegalWords(wordList, limit) {
    legalWords = [];
    wordsFound = [];

    for (var i in wordList) {
      var len = wordList[i].length;

      if ((gameParams.mode != MODE_ANAGRAM) || (len == letters.length)) {
        var legal = true;
        var f = [];

        // increment frequencies for the word being examined
        var j = 0;
        while ((j < len) && legal) {
          var char = wordList[i][j];
          f[char] = 1 + ((char in f) ? f[char] : 0);
          if (f[char] > limit[char]) {
            legal = false;
          }
          j++;
        }
        if (legal) {
          legalWords.push(wordList[i]);
          wordsFound.push(false);
        }
      }
    }
  }

  // generate new letters at the game start or in anagram mode
  function getNewLetters() {
    var wl = gameParams.useDiacritics ? wordListDia : wordList;
    letters = getLetters(wl, gameParams.level);
    var limit = frequencyTable(letters);
    getLegalWords(wl, limit);
    writeLegalWords();
    drawLetters();
  }

  function letterHandler(event) {
    var key = String.fromCharCode(event.charCode).toLowerCase();

    if (key.match(/[a-zăîșțâ]/g)) {
      // move a tile down if the letter matches
      var i = 0;
      while ((i < topTiles.length) &&
             ((topTiles[i] == NIL) || (tiles[topTiles[i]].letter != key))) {
        i++;
      }

      if (i < topTiles.length) {
        tiles[topTiles[i]].gather();
      }
    }
  }

  function specialKeyHandler(event) {
    var keyCode = event.keyCode;

    if (keyCode == 13) { // enter
      scoreWord();
    } else if (keyCode == 8) { // backspace
      event.preventDefault(); // disable the various things Firefox does
      Tile.scatterLastBottom();
    } else if (keyCode == 27) { // esc
      Tile.scatterBottomRow();
    }
  }

  function scoreWord() {
    // assemble the word
    var word = '';
    for (var k = 0; k < bottomTiles.length; k++) {
      if (bottomTiles[k] != NIL) {
        word += tiles[bottomTiles[k]].letter;
      }
    }

    if (word == '') {
      return;
    } else if (word.length < MINIMUM_WORD_LENGTH) {
      animateMessage('MSG_TOO_SHORT');
      return;
    }

    // look for a legal word
    var i = 0;
    while ((i < legalWords.length) && (legalWords[i] != word)) {
      i++;
    }

    if (i == legalWords.length) {
      // no such word
      animateMessage('MSG_WRONG');
    } else if (wordsFound[i]) {
      // word already found
      animateMessage('MSG_ALREADY_FOUND');
    } else {
      // found a new word
      animateMessage('MSG_CORRECT');
      wordsFound[i] = true;

      var score = (gameParams.mode == MODE_ANAGRAM) ? 1 : (5 * word.length);
      $('#score').text(score + parseInt($('#score').text()));

      if (gameParams.mode == MODE_WORD_SEARCH) {
        $('#foundWords').text(1 + parseInt($('#foundWords').text()));
        $('#legalWord-' + i)
          .find('a')
          .removeClass('text-danger').addClass('text-success')
          .find('i')
          .removeClass('glyphicon-remove').addClass('glyphicon-ok');
        Tile.scatterBottomRow();
      } else {
        getNewLetters();
      }
    }
  }

  function animateMessage(key) {
    // stop all other animations
    for (k in messages) {
      messages[k].alpha = 0;
    }
    messages[key].alpha = 1;
  }

  function animateGameOverText() {
    gameOverText.scale.set(0, 0);
  }

  // moves the tile at position pos on row1 to the first open slot on row2
  function moveTile(pos, row1, row2, top) {
    if (row1[pos] != NIL) {
      var i = 0;
      while (row2[i] != NIL) {
        i++;
      }
      row2[i] = row1[pos];
      row1[pos] = NIL;

      tiles[row2[i]].startAnimation(i, top);
    }
  }

  // returns a string in the form m:ss
  function minutesAndSeconds(time) {
    var m = Math.floor(time / 60),
        s = time - m * 60;
    return (s >= 10)
      ? (m + ':' + s)
      : (m + ':0' + s);
  }

  function startTimer() {
    var secondsLeft = gameParams.seconds;
    var timer = setInterval(decrementTimer, 1000);

    $('#timer').text(minutesAndSeconds(secondsLeft));

    function decrementTimer() {
      secondsLeft--;
      $('#timer').text(minutesAndSeconds(secondsLeft));
      if (!secondsLeft) {
        clearInterval(timer);
        endGame();
      }
    }
  }

  // creates letter tiles
  function drawLetters() {
    // remove the old tiles, if any
    for (i in tiles) {
      gameScene.removeChild(tiles[i]);
    }

    tiles = [];
    topTiles = [];
    bottomTiles = [];

    for (var i = 0; i < letters.length; i++) {
      var t = new Tile(letters[i], i);
      gameScene.addChild(t);
      tiles.push(t);
      topTiles.push(i);
      bottomTiles.push(NIL);
    }
  }

  function drawCanvasElements() {
    PIXI.loader
      .add([ VERIFY_BUTTON_URL, LETTERS_URL, ])
      .load(function() {
        var vb = new PIXI.Sprite(PIXI.loader.resources[VERIFY_BUTTON_URL].texture);
        vb.anchor.set(0.5, 0.5);
        vb.position.set(CANVAS_WIDTH / 2, CONTROLS_Y);
        vb.interactive = true;
        vb.buttonMode = true;
        vb.on('pointerup', scoreWord);

        gameScene.addChild(vb);
      });

    messages = [];
    for (key in MESSAGES) {
      var data = MESSAGES[key];
      var style = Object.assign(MESSAGES_COMMON_STYLE, data.style);
      var m = new PIXI.Text(data.text, style);
      m.anchor.set(0.5, 0.5);
      m.position.set(CANVAS_WIDTH * 4 / 5, CONTROLS_Y);
      m.alpha = 0;
      gameScene.addChild(m);
      messages[key] = m;
    }

    gameOverText = new PIXI.Text('Stirfâș', {
      fill: '#aaa',
      stroke: '#000',
      font: '60px Verdana, sans-serif',
      strokeThickness: 1,
    });
    gameOverText.anchor.set(0.5, 0.5);
    gameOverText.position.set(CANVAS_WIDTH / 2, CANVAS_HEIGHT / 2);
    gameOverScene.addChild(gameOverText);
  }

  function writeLegalWords() {
    $('#legalWords').empty();

    for (var i in legalWords) {
      var w = legalWords[i];
      var div = wordStem.clone(true)
          .attr('id', 'legalWord-' + i)
          .appendTo('#legalWords');

      var href = div.find('a').attr('href');
      div.find('a').attr('href', href + w);

      div.find('span').text(w);
    }
  }

  function endGame() {
    // unbind key listeners
    $(document).unbind('keypress', letterHandler);
    $(document).unbind('keydown', specialKeyHandler);

    // show the legal words
    $('#wordListPanel').slideDown();
    $('#restartGameButton').show();

    // switch scenes
    gameScene.visible = false;
    gameOverScene.visible = true;
    animateGameOverText();
  }

  function restartGame() {
    $('#mainMenu').show();
    $('#gamePanel').hide();    
    $('#wordListPanel').hide();
    $('#restartGameButton').hide();

    gameScene.visible = true;
    gameOverScene.visible = false;
    gameOverText.scale.set(1, 1);
    gameOverText.rotation = 0;
  }

  // Scroll the canvas into view unless it is already entirely in the viewport
  function scrollIntoView() {
    // viewport info
    var vtop = $(window).scrollTop(),
        vbottom = vtop + $(window).height();

    // canvas info
    var ctop = $('#canvasWrap').offset().top,
        cbottom = ctop + $('#canvasWrap').outerHeight();

    if ((ctop < vtop) || (cbottom > vbottom)) {
      $('body').scrollTop(ctop);
    }
  }

  function resize() {
    // get container width
    var w = $('#canvasWrap').width();
    var h = $(window).height() - STATS_HEIGHT;

    // scale to fit
    var s = Math.min(w / CANVAS_WIDTH, h / CANVAS_HEIGHT);

    // get scaled dimensions
    var sw = Math.floor(s * CANVAS_WIDTH);
    var sh = Math.floor(s * CANVAS_HEIGHT);

    renderer.view.style.width = sw + "px";
    renderer.view.style.height = sh + "px";
  }

  function gameLoop() {
    requestAnimationFrame(gameLoop);

    if (gameScene.visible) {
      for (var i = 0; i < tiles.length; i++) {
        tiles[i].animate();
      }

      for (var k in messages) {
        if (messages[k].alpha) {
          messages[k].alpha -= 0.01;
        }
      }
    }

    if (gameOverScene.visible) {
      if (gameOverText.scale.x < 1) {
        var s = gameOverText.scale.x + 0.02;
        gameOverText.scale.set(s, s);
        gameOverText.rotation = -2 * Math.PI * s; // do a 360 while the scale goes from 0 to 1
      }
    }

    renderer.render(stage);
  }

  init();
});
