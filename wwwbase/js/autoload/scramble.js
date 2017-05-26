$(document).ready(function() {
  const WORD_LIST_DIA_URL = 'https://dexonline.ro/static/download/game-word-list-dia.txt';
  const WORD_LIST_URL = 'https://dexonline.ro/static/download/game-word-list.txt';
  const ALPHABET = 'aăâbcdefghiîjklmnopqrsștțuvwxyz';
  
  const CANVAS_WIDTH = 480;
  const CANVAS_HEIGHT = 280;
  const TILE_WIDTH = 55;
  const TILE_HEIGHT = 75;
  const TILE_PADDING = 10;
  const TILE_FONT_SIZE = 60;
  const TOP_Y = 50;
  const BOTTOM_Y = 200;
  const THRESHOLD_Y = 120;

  const ANIMATION_SPEED = 200;

  const SECONDS = 180;

  var score = 0;
  var letters;    // letter set
  var legalWords; // words that can be made from the letter set
  var wordsFound; // boolean array indicating which legal words the user has found
  var upLayers;   // top row rectangles
  var downLayers; // bottom row rectangles

  function init() {
    $('#startGameButton').click(startGame);
  }

  function startGame() {
    var level = parseInt($('.active input[name="level"]').val());
    var useDiacritics = parseInt($('.active input[name="useDiacritics"]').val());
    $('#mainMenu').slideToggle();
    $('#gameArea').slideToggle();

    $.get(useDiacritics ? WORD_LIST_DIA_URL : WORD_LIST_URL)
      .done(function(result) {
        var wordList = result.trim().split('\n');
        getLettersAndLegalWords(wordList, level);

        $('#maxWords').html(legalWords.length);
        drawLetters();
        
        $(document).keypress(letterHandler);
        $(document).keydown(specialKeyHandler);
        startTimer();
      })
      .fail(function() {
        console.log('Nu pot descărca lista de cuvinte.');
      });
  }

  // shuffles the letters of a string
  function shuffleLetters(s) {
    var a = s.split("");

    for (var i = a.length - 1; i; i--) {
      var j = Math.floor(Math.random() * (i + 1));
      var tmp = a[i];
      a[i] = a[j];
      a[j] = tmp;
    }

    return a.join("");
  }

  // chooses a letter set and finds all legal words for that set
  function getLettersAndLegalWords(wordList, level) {
    // choose a random word and shuffle its letters
    do {
      letters = wordList[Math.floor(Math.random() * wordList.length)];
    } while (letters.length != level);
    letters = shuffleLetters(letters);

    // build a frequency table
    var limit = [];
    for (var i = 0; i < ALPHABET.length; i++) {
      limit[ALPHABET[i]] = 0;
    }
    for (var i = 0; i < letters.length; i++) {
      limit[letters[i]]++;
    }

    // iterate through words and select legal ones
    legalWords = [];
    wordsFound = [];

    for (var i in wordList) {
      var len = wordList[i].length;
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

  // returns the X coordinate for a tile in the index-th position
  function getTileX(index) {
    var wp = TILE_WIDTH + TILE_PADDING;
    return (CANVAS_WIDTH + wp * (2 * index - letters.length + 1)) / 2;
  }

  function letterHandler(event) {
    var key = String.fromCharCode(event.charCode).toLowerCase();

    if (key.match(/[a-zăîșțâ]/g)) {
      // handle letters: move a letter down if one exists
      var i = 0;
      while ((i < upLayers.length) &&
             (!upLayers[i] || (upLayers[i].data.letter != key))) {
        i++;
      }

      if (i < upLayers.length) {
        gather(i);
      }
    }
  }

  function specialKeyHandler(event) {
    var keyCode = event.keyCode;

    if (keyCode == 13) { // enter
      scoreWord();
    } else if (keyCode == 8) { // backspace
      scatterLastBottom();
    } else if (keyCode == 27) { // esc
      scatterBottomRow();
    }
  }

  function scoreWord() {
    // assemble the word
    var word = '';
    for (var k = 0; k < downLayers.length; k++) {
      if (downLayers[k]) {
        word += downLayers[k].data.letter;
      }
    }

    // look for a legal word that the user has not already found
    var i = 0;
    while ((i < legalWords.length) &&
           ((legalWords[i] != word) || wordsFound[i])) {
      i++;
    }

    if (i < legalWords.length) {
      // found one
      wordsFound[i] = true;
      score += word.length * 5;
      $('#score').html(score);
      scatterBottomRow();
    }
  }

  // animates the given layer on the index-th position on the top row (top = true)
  // or bottom row (top = false)
  function animateTile(layer, index, top) {
    $('canvas').animateLayerGroup(layer.groups[0], {
      x: getTileX(index),
      y: (top ? TOP_Y : BOTTOM_Y),
    }, ANIMATION_SPEED);
  }

  // move the letter at position pos on row1 to the first open slot on row2
  function moveLetter(pos, row1, row2, top) {
    if (row1[pos]) {
      var i = 0;
      while (row2[i]) {
        i++;
      }
      row2[i] = row1[pos];
      row1[pos] = 0;

      animateTile(row2[i], i, top);
    }
  }

  // move the letter at position pos on the top row to the first open slot on the bottom row
  function gather(pos) {
    moveLetter(pos, upLayers, downLayers, false);
  }

  // send the letter at position pos on the bottom row back to the top row
  function scatter(pos) {
    moveLetter(pos, downLayers, upLayers, true);
  }

  // send letters on the bottom row back to the top row
  function scatterLastBottom() {
    var j = downLayers.length - 1;
    while ((j >= 0) && !downLayers[j]) {
      j--;
    }
    if (j >= 0) {
      scatter(j);
    }
  }

  // send letters on the bottom row back to the top row
  function scatterBottomRow() {
    for (var j = 0; j < downLayers.length; j++) {
      if (downLayers[j]) {
        scatter(j);
      }
    }
  }

  function startTimer() {
    var secondsLeft = SECONDS;
    var timer = setInterval(decrementTimer, 1000);
    $('#timer').html(secondsLeft);

    function decrementTimer() {
      secondsLeft--;
      $('#timer').html(secondsLeft);
      if (!secondsLeft) {
        clearInterval(timer);
        endGame();
      }
    }
  }

  function dragCancel(layer) {
    // if a letter was dragged, get the corresponding rectangle layer
    layer = $('canvas').getLayer(layer.data.tile);

    for (var i in upLayers) {
      if (upLayers[i] == layer) {
        animateTile(layer, i, true);
      }
    }
    for (var i in downLayers) {
      if (downLayers[i] == layer) {
        animateTile(layer, i, false);
      }
    }
  }
  
  function dragStop(layer) {
    // if a letter was dragged, get the corresponding rectangle layer
    layer = $('canvas').getLayer(layer.data.tile);

    var move = false;

    //Switch position area
    for(var i = 0 ; i < downLayers.length; i++)
    {
      if(downLayers[i] != 0 && layer== downLayers[i])
      {
        for(var j = 0; j < downLayers.length; j++)
        {
          if(layer.x < downLayers[j].x && (layer.y < 235 && layer.y > 175))
          {

            for(var l = 0; l < downLayers.length; l++)
            {
              if(downLayers[l] != 0)
              {
                $('canvas').animateLayerGroup(downLayers[l].groups[0],{
                  x: getTileX(l),
                  y: BOTTOM_Y,
                }, ANIMATION_SPEED);
              }
            }
            break;
          }
          if(layer.x > downLayers[j].x && (layer.y < 235 && layer.y > 175) && j == downLayers.length - 1)
          {
            for(var l = 0; l < downLayers.length; l++)
            {
              if(downLayers[l] != 0)
              {
                $('canvas').animateLayerGroup(downLayers[l].groups[0],{
                  x: getTileX(l),
                  y: BOTTOM_Y,
                }, ANIMATION_SPEED);
              }
            }
          }
        }
        break;
      }
    }
    //Drag down area
    if(layer.y > THRESHOLD_Y)  // Move and animate the letter down
    {
      for(var i = 0 ; i < downLayers.length; i++) // Check and reposition back into place if draged on the same area
      {
        if(downLayers[i] == layer)
        {
          $('canvas').animateLayerGroup(layer.groups[0],{
            x: getTileX(i),
            y: BOTTOM_Y,
          }, ANIMATION_SPEED);
          move = false;
          break;
        }
        else
        {
          move = true;
        }
      }
      if(move)
      {
        for(var i = 0 ; i < upLayers.length; i++)
        {
          if(upLayers[i] == layer)
          {
            for(var j = 0; j < downLayers.length; j++)
            {
              if(downLayers[j] == 0)
              {
                downLayers[j] = layer;
                upLayers[i] = 0;

                $('canvas').animateLayerGroup(layer.groups[0],{
                  x: getTileX(j),
                  y: BOTTOM_Y,
                }, ANIMATION_SPEED);
                break;
              }
            }
            break;
          }
        }
      }
    }
    else // Move and animate the letter up
    {
      for(var i = 0 ; i < upLayers.length; i++) // Check and reposition back into place if draged on the same area
      {
        if(upLayers[i] == layer)
        {
          $('canvas').animateLayerGroup(layer.groups[0],{
            x: getTileX(i),
            y: TOP_Y,
          }, ANIMATION_SPEED);
          move = false;
          break;
        }
        else
        {
          move = true;
        }
      }
      if(move)
      {
        for(var i = 0 ; i < downLayers.length; i++)
        {
          if(downLayers[i] == layer)
          {
            for(var j = 0; j < upLayers.length; j++)
            {
              if(upLayers[j] == 0)
              {
                upLayers[j] = layer;
                downLayers[i] = 0;
                $('canvas').animateLayerGroup(layer.groups[0],{
                  x: getTileX(j),
                  y: TOP_Y,
                }, ANIMATION_SPEED);
                break;
              }
            }
            break;
          }
        }
      }
    }
  }

  // printeaza literele cuvantului random din baza de date
  function drawLetters() {
    upLayers = [];
    downLayers = [];

    for (var i = 0; i < letters.length; i++) {

      var posX = getTileX(i);

      $('canvas').drawRect({
        layer: true,
        draggable: true,
        strokeStyle: 'black',
        strokeWidth: 4,
        name: 'rect' + i,
        fillStyle: '#cceeff',
        groups: ['boggle' + i],
        dragGroups: ['boggle' + i],
        x: 500, y: TOP_Y,
        width: TILE_WIDTH,
        height: TILE_HEIGHT,
        cornerRadius: 4,
        data: {
          letter: letters[i],
          tile: 'rect' + i, // self
        },
        dragcancel: dragCancel,
        dragstop: dragStop,
      })
        .drawText({
          layer: true,
          draggable: true,
          name: 'letter' + i,
          groups: ['boggle' + i],
          dragGroups: ['boggle' + i],
          fillStyle: 'black',
          strokeStyle: 'black',
          strokeWidth: 1,
          x: 500, y: TOP_Y,
          fontSize: TILE_FONT_SIZE,
          fontFamily: 'Verdana, sans-serif',
          text: letters[i].toUpperCase(),
          data: {
            tile: 'rect' + i,
          },
          dragcancel: dragCancel,
          dragstop: dragStop,
        })
        .animateLayerGroup('boggle' + i, {
          x: getTileX(i), y: TOP_Y
        }, ANIMATION_SPEED);

      upLayers.push($('canvas').getLayer('rect' + i));
      downLayers.push(0);
    }
  }

  //Draw end screen message
  function drawEnd()
  {
    $('canvas').removeLayers();

    $('canvas').drawText({
      layer: true,
      draggable: true,
      name: 'gameOverText',
      groups: 'gameOver',
      dragGroups: 'gameOver',
      fillStyle: function(layer) {
        var value = Math.round(layer.x / this.width * 360);
        value = Math.min(value, 360);
        return 'hsl(' + value + ', 50%, 50%)';
      },
      strokeStyle: 'black',
      strokeWidth: 2,
      x: 800, y: 120,
      fontSize: 60,
      fontFamily: 'Verdana, sans-serif',
      text: 'Sfârșit',

    })
      .animateLayer('gameOverText', {
        x: CANVAS_WIDTH / 2,
        y: CANVAS_HEIGHT / 2,
        rotate: '+=360',
      }, ANIMATION_SPEED);
  }

  function ShowWordsAndEnd() {
    $('.wordBtn').on('click', function() {
      $(this).blur();

      var ul = 0;
      var initialTR = 'wordList';
      var currentTR = initialTR;
      var start = 0;
      var stop;

      drawEnd(); //Draw Game Over

      var wordArea = $('.wordArea');
      wordArea.show();

      for(var i = 0; i <= legalWords.length; i++)
      {
        if( i % 5 == 0 || (i == legalWords.length && legalWords.length % 5 != 0))
        {
          stop = i;
          var td = 'td' + i;
          var ulist = 'ulist' + i;
          $('<td></td>', { 'class' : td }).appendTo('.' + currentTR);
          $('<ul></ul>', { 'class' : ulist + ' list-unstyled'}).appendTo('.' + td);
          for(var k = start; k < stop; k++) {
            var list = '<li>' + legalWords[k] + '</li>';
            $('.' + ulist).append(list);
          }
          ul++;
          start = stop;
        }
        if($('.' + currentTR).children().length % 9 == 0)
        {
          currentTR = initialTR + i;
          $('<tr></tr>', {'class' : currentTR}).appendTo(wordArea);
        }
      }
    });
  }

  function endGame() {
    $(document).unbind('keypress', letterHandler);
    $(document).unbind('keydown', specialKeyHandler);
    drawEnd();
  }

  init();
});
