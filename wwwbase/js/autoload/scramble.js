$(function() {
  "use strict";

  const WORD_LIST_DIA_URL = 'https://dexonline.ro/static/download/game-word-list-dia.txt';
  const WORD_LIST_URL = 'https://dexonline.ro/static/download/game-word-list.txt';
  const TILESET_URL = wwwRoot + 'img/scramble/tileset.png';
  const ALPHABET = 'aăâbcdefghiîjklmnopqrsștțuvwxyz';

  const MODE_WORD_SEARCH = 0;
  const MODE_ONE_ANAGRAM = 1;
  const MODE_ALL_ANAGRAMS = 2;

  const MINIMUM_WORD_LENGTH = 3;

  const GAME_ID_LENGTH = 8; // excluding the encoded params

  const CANVAS_WIDTH = 640;
  const CANVAS_HEIGHT = 480;
  const TILE_WIDTH = 75;
  const TILE_HEIGHT = 105;
  const TILE_PADDING = 14;
  const SEARCH_WIDTH = 200;
  const SHUFFLE_WIDTH = 70;
  const RESIGN_WIDTH = 70;
  const BUTTON_HEIGHT = 55;

  const MESSAGES_X = 100;
  const SHUFFLE_X = 512;
  const RESIGN_X = 590;
  const TOP_Y = 80;
  const BOTTOM_Y = 280;
  const CONTROLS_Y = 440;
  const STATS_HEIGHT = 40; // keep this many pixels vertically when scaling

  const ANIMATION_STEPS = 10; // for letter moves

  const COOKIE_NAME = 'scramble';

  // maps 0-based HTML field values to parameter values
  const PARAM_VALUES = {
    level: [ 4, 5, 6, 7 ],
    seconds: [ 60, 120, 180, 240, 300 ],
  }

  // game states
  const ST_PLAYING = 0;
  const ST_RESIGNED = 1;
  const ST_TIMEOUT = 2;
  const ST_WON = 3;

  const MESSAGES = {
    MSG_CORRECT: {
      text: 'corect',
      color: '#3c763d',
    },
    MSG_ALREADY_FOUND: {
      text: 'deja găsit',
      color: '#8a6b3d',
    },
    MSG_WRONG: {
      text: 'incorect',
      color: '#a94442',
    },
    MSG_TOO_SHORT: {
      text: 'prea scurt',
      color: '#a94442',
    },
  };

  var letters;       // letter set
  var legalWords;    // words that can be made from the letter set
  var wordsFound;    // boolean array indicating which legal words the user has found
  var numWordsLeft;  // number of words left to find
  var tiles;         // PIXI sprites
  var topTiles;      // tiles on the top row (null for empty spaces)
  var bottomTiles;   // ditto
  var wordStem;      // div to be cloned for every legal word
  var wordList, wordListDia; // word lists downloaded from server, without and with diacritics
  var gameParams;    // main menu options

  var stage;
  var renderer;
  var messages;
  var state;
  var gameScene;
  var gameOverScene;

  /**
   * A Tile is a Sprite that can move between the top and bottom rows.
   **/
  class Tile extends PIXI.Sprite {
    constructor(letter, pos) {
      // create the sprite
      var index = ALPHABET.indexOf(letter);
      var rectangle = new PIXI.Rectangle(0, TILE_HEIGHT * index, TILE_WIDTH, TILE_HEIGHT);
      var texture = new PIXI.Texture(PIXI.loader.resources[TILESET_URL].texture, rectangle);
      super(texture);

      // set custom fields
      this.letter = letter;
      this.top = true;
      this.pos = pos;

      this.anchor.set(0.5, 0.5);
      this.position.set(this.getX(), TOP_Y);
      this.interactive = true;
      this.buttonMode = true;
      this.on('pointerup', this.toggle);
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

    // moves the tile to the first open slot on the other row
    toggle() {
      var src, dest;
      if (this.top) {
        src = topTiles;
        dest = bottomTiles;
      } else {
        src = bottomTiles;
        dest = topTiles;
      }

      var i = 0;
      while (dest[i]) {
        i++;
      }
      dest[i] = this;
      src[this.pos] = null;

      this.startAnimation(i);
    }

    // sends the last tile on the bottom row back to the top row
    static scatterLastBottom() {
      var j = bottomTiles.length - 1;
      while ((j >= 0) && !bottomTiles[j]) {
        j--;
      }
      if (j >= 0) {
        bottomTiles[j].toggle();
      }
    }

    // sends all the tiles on the bottom row back to the top row
    static scatterBottomRow() {
      for (var j = 0; j < bottomTiles.length; j++) {
        if (bottomTiles[j]) {
          bottomTiles[j].toggle();
        }
      }
    }

    static shuffle() {
      // send all the tiles to the bottom row...
      for (var j = 0; j < topTiles.length; j++) {
        if (topTiles[j]) {
          topTiles[j].toggle();
        }
      }

      // ... then send them back up in random order
      for (var i = 0; i < bottomTiles.length; i++) {
        var j;
        do {
          j = Math.floor(Math.random() * bottomTiles.length);
        } while (!bottomTiles[j]);
        bottomTiles[j].toggle();
      }
      
    }
  }

  /**
   * A flash message featuring a text, color and opacity.
   **/
  class Message extends PIXI.Text {
    constructor(key) {
      var data = MESSAGES[key];
      super(data.text, {
        font: '24px Verdana, sans-serif',
        fill: data.color,
        stroke: data.color,
        strokeThickness: 1,
      });

      this.anchor.set(0.5, 0.5);
      this.position.set(MESSAGES_X, CONTROLS_Y);
      this.alpha = 0;

      gameScene.addChild(this);
    }

    startAnimation() {
      // stop all other animations
      for (var k in messages) {
        messages[k].alpha = 0;
      }
      this.alpha = 1;
    }

    animate() {
      if (this.alpha) {
        this.alpha -= 0.01;
      }
    }
  }

  /**
   * A text that appears once at the end of the game.
   **/
  class GameOverText extends PIXI.Text {
    constructor(text, color) {
      super(text, {
        fill: color,
        stroke: '#444',
        font: '60px Verdana, sans-serif',
        fontWeight: 'bold',
        strokeThickness: 1,
      });
      this.anchor.set(0.5, 0.5);
      this.position.set(CANVAS_WIDTH / 2, CANVAS_HEIGHT / 2);
    }

    startAnimation() {
      this.scale.set(0, 0);
    }

    animate() {
      if (this.scale.x < 1) {
        var s = this.scale.x + 0.04;
        this.scale.set(s, s);
        this.rotation = -2 * Math.PI * s; // do a 360 while the scale goes from 0 to 1
      }
    }
  }

  class Button extends PIXI.Sprite {

    // x: on-screen X coordinate
    // width: on-screen (and tileset) width
    // cropY: tileset cropping position
    // click: click handler
    constructor(x, width, cropY, click) {
      var rectangle = new PIXI.Rectangle(0, cropY, width, BUTTON_HEIGHT);
      var texture = new PIXI.Texture(PIXI.loader.resources[TILESET_URL].texture, rectangle);
      super(texture);

      this.anchor.set(0.5, 0.5);
      this.position.set(x, CONTROLS_Y);
      this.interactive = true;
      this.buttonMode = true;
      this.on('pointerup', click);

      gameScene.addChild(this);
    }
  }

  function toBase36(d) {
    return (d < 10)
      ? String.fromCharCode('0'.charCodeAt(0) + d)
      : String.fromCharCode('A'.charCodeAt(0) + d - 10);
  }

  function fromBase36(d) {
    return (d >= '0') && (d <= '9')
      ? d.charCodeAt(0) - '0'.charCodeAt(0)
      : d.charCodeAt(0) - 'A'.charCodeAt(0) + 10;
  }

  // encode each game parameter in base 6, then output a base-36 string
  function encodeGameParams() {
    return toBase36(gameParams.mode * 6 + gameParams.level) +
      toBase36(gameParams.useDiacritics * 6 + gameParams.seconds);
  }

  function decodeGameParams(gameId) {
    var code = gameId.substr(-2);
    var d1 = fromBase36(code[0]);
    var d2 = fromBase36(code[1]);

    return {
      mode: Math.floor(d1 / 6),
      level: d1 % 6,
      useDiacritics: Math.floor(d2 / 6),
      seconds: d2 % 6,
    }
  }

  // generates a random game ID, which includes the game parameters in a recoverable way
  function generateGameId(params) {
    // generate GAME_ID_LENGTH base-36 digits
    var s = '';
    for (var i = 0; i < GAME_ID_LENGTH; i++) {
      s += toBase36(Math.floor(Math.random() * 36));
    }

    // append the encoded parameters
    s += encodeGameParams();

    return s;
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

    // look for preset options in (a) game ID then (b) cookie
    var params = null;
    if (window.location.hash) {
      params = decodeGameParams(window.location.hash);
    } else if ($.cookie(COOKIE_NAME)) {
      params = JSON.parse($.cookie(COOKIE_NAME));
    }
    if (params) {
      $('#optionsDiv .active input').each(function() {
        var name = $(this).attr('name');
        var sel = '.btn-group input[name="' + name + '"][value="' + params[name] + '"]';
        $(sel).closest('.btn').button('toggle');
      });
    }
    // any further option changes will cause the fragment (hash) to disappear
    $('#optionsDiv input').change(removeHash);

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

    var gameId = window.location.hash.split('#')[1];
    if (gameId) {
      gameId = gameId.substr(1); // strip the # itself
    } else {
      gameId = generateGameId();
      window.location.hash = gameId;
    }
    $('#gameId').text(gameId);
    Math.seedrandom(gameId);

    $.cookie(COOKIE_NAME, JSON.stringify(gameParams), { expires: 3650, path: '/' });

    getNewLetters();

    $('#optionsDiv').collapse('hide');
    $('#wordCountDiv').toggle(gameParams.mode != MODE_ONE_ANAGRAM);
    $('#mainMenu').hide();
    $('#gamePanel').show();
    $('#gameIdPanel').show();
    $('#score').text('0');

    gameScene.visible = true;
    gameOverScene.visible = false;
    gameOverScene.removeChildren(); // remove the game over text from the previous play

    state = ST_PLAYING;
    resize();
    scrollIntoView();
    $(document).keypress(letterHandler);
    $(document).keydown(specialKeyHandler);
    startTimer();
    gameLoop();
  }

  // generate a letter set
  function getLetters(wordList) {
    var targetLength = PARAM_VALUES.level[gameParams.level];
    var s;

    // choose a random word
    do {
      s = wordList[Math.floor(Math.random() * wordList.length)];
    } while (s.length != targetLength);

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

      if ((gameParams.mode == MODE_WORD_SEARCH) || (len == letters.length)) {
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

    numWordsLeft = legalWords.length;
  }

  // generate new letters at the game start or in anagram mode
  function getNewLetters() {
    var wl = gameParams.useDiacritics ? wordListDia : wordList;
    letters = getLetters(wl);
    var limit = frequencyTable(letters);
    getLegalWords(wl, limit);
    writeLegalWords();
    drawLetters();
    $('#foundWords').text('0');
    $('#maxWords').text(legalWords.length);
  }

  function letterHandler(event) {
    var key = String.fromCharCode(event.charCode).toLowerCase();

    if (key.match(/[a-zăîșțâ]/g)) {
      // move a tile down if the letter matches
      var i = 0;
      while ((i < topTiles.length) &&
             (!topTiles[i] || (topTiles[i].letter != key))) {
        i++;
      }

      if (i < topTiles.length) {
        topTiles[i].toggle();
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
    } else if (keyCode == 191) { // slash
      Tile.shuffle();
    } else if (keyCode == 27) { // esc
      Tile.scatterBottomRow();
    }
  }

  function scoreWord() {
    // assemble the word
    var word = '';
    for (var k = 0; k < bottomTiles.length; k++) {
      if (bottomTiles[k]) {
        word += bottomTiles[k].letter;
      }
    }

    if (word == '') {
      return;
    } else if (word.length < MINIMUM_WORD_LENGTH) {
      messages['MSG_TOO_SHORT'].startAnimation();
      return;
    }

    // look for a legal word
    var i = 0;
    while ((i < legalWords.length) && (legalWords[i] != word)) {
      i++;
    }

    if (i == legalWords.length) {
      // no such word
      messages['MSG_WRONG'].startAnimation();
    } else if (wordsFound[i]) {
      // word already found
      messages['MSG_ALREADY_FOUND'].startAnimation();
    } else {
      // found a new word
      messages['MSG_CORRECT'].startAnimation();
      wordsFound[i] = true;

      var score = (gameParams.mode == MODE_WORD_SEARCH) ? (5 * word.length) : 1;
      $('#score').text(score + parseInt($('#score').text()));

      if (gameParams.mode == MODE_ONE_ANAGRAM) {
        getNewLetters();
      } else {
        $('#foundWords').text(1 + parseInt($('#foundWords').text()));
        $('#legalWord-' + i)
          .find('a')
          .removeClass('text-danger').addClass('text-success')
          .find('i')
          .removeClass('glyphicon-remove').addClass('glyphicon-ok');
        Tile.scatterBottomRow();

        if (!--numWordsLeft) {
          if (gameParams.mode == MODE_ALL_ANAGRAMS) {
            getNewLetters();
          } else {
            state = ST_WON;
          }
        }
      }
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
    var secondsLeft = PARAM_VALUES.seconds[gameParams.seconds];
    var timer = setInterval(decrementTimer, 1000);

    $('#timer').text(minutesAndSeconds(secondsLeft));

    function decrementTimer() {
      secondsLeft--;
      $('#timer').text(minutesAndSeconds(secondsLeft));
      if (!secondsLeft || (state != ST_PLAYING)) {
        clearInterval(timer);
        endGame();
      }
    }
  }

  function resign() {
    state = ST_RESIGNED;
  }

  // creates letter tiles
  function drawLetters() {
    // remove the old tiles, if any
    for (var i in tiles) {
      gameScene.removeChild(tiles[i]);
    }

    tiles = [];
    topTiles = [];
    bottomTiles = [];

    for (var i = 0; i < letters.length; i++) {
      var t = new Tile(letters[i], i);
      gameScene.addChild(t);
      tiles.push(t);
      topTiles.push(t);
      bottomTiles.push(null);
    }
  }

  function drawCanvasElements() {
    PIXI.loader
      .add(TILESET_URL)
      .load(function() {
        var btnOffset = TILE_HEIGHT * ALPHABET.length;
        new Button(CANVAS_WIDTH / 2, SEARCH_WIDTH, btnOffset, scoreWord);
        new Button(SHUFFLE_X, SHUFFLE_WIDTH, btnOffset + BUTTON_HEIGHT, Tile.shuffle);
        new Button(RESIGN_X, RESIGN_WIDTH, btnOffset + 2 * BUTTON_HEIGHT, resign);
      });

    messages = [];
    for (var key in MESSAGES) {
      messages[key] = new Message(key);
    }
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

    var gameOverText = (state == ST_WON)
        ? new GameOverText('Felicitări!', '#4cae4c')
        : new GameOverText('STIRFÂȘ :-)', '#761818')
    gameOverScene.addChild(gameOverText);

    // switch scenes
    gameScene.visible = false;
    gameOverScene.visible = true;
    gameOverText.startAnimation();

  }

  function removeHash () { 
    history.pushState('', document.title, window.location.pathname
                      + window.location.search);
  }

  // this handles div visibility only; game mechanics are in startGame()
  function restartGame() {
    $('#mainMenu').show();
    $('#gamePanel').hide();    
    $('#wordListPanel').hide();
    $('#restartGameButton').hide();
    $('#gameIdPanel').hide();
    removeHash();
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
        messages[k].animate();
      }
    }

    if (gameOverScene.visible) {
      gameOverScene.getChildAt(0).animate();
    }

    renderer.render(stage);
  }

  init();
});
