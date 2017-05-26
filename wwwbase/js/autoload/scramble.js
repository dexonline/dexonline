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
  var secondsLeft;
  var check; // Check is diacritics are to be used.
  var randomWord; // word chosen at random from wordList, constrained by difficulty
  var legalWords; // the possible words that can be made from the randomWord.
  var wordsFound; // boolean array indicating which legal words the user has found
  var difficulty;
  var upLayers = []; // Letters in the top
  var downLayers = []; // Letters in the bottom
  var wordList; // word list downloaded from the server

  function init() {
    $('#mainMenu button').click(function() {
      difficulty = parseInt($(this).val());
      check = $('#toggleD').prop('checked');
      $('#mainMenu').slideToggle();
      $('#gameArea').slideToggle();
      GetWordAsync(difficulty);
    });

    // TODO initialize these only after game starts; unbind at game end
    $(document).keypress(letterHandler);
    $(document).keydown(specialKeyHandler);
  }

  function getLettersAndLegalWords() {
    // choose a random word
    do {
      randomWord = wordList[Math.floor(Math.random() * wordList.length)];
    } while (randomWord.length != difficulty);

    // build a frequency table
    var limit = [];
    for (var i = 0; i < ALPHABET.length; i++) {
      limit[ALPHABET[i]] = 0;
    }
    for (var i = 0; i < randomWord.length; i++) {
      limit[randomWord[i]]++;
    }

    // iterate through words and select legal ones
    legalWords = [];
    wordsFound = [];

    for (var i in wordList) {
      var legal = true;
      var f = [];
      // increment frequencies for the word being examined
      var len = wordList[i].length;
      var j = 0;
      var legal = true;
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

  function GetWordAsync() {

    $.get(check ? WORD_LIST_DIA_URL : WORD_LIST_URL)
      .done(function(result) {
        wordList = result.trim().split('\n');
        getLettersAndLegalWords();

        $('#maxWords').html(legalWords.length);
        drawLetters(randomWord);
        
        startTimer();
        
        $('.wordArea').hide().find('tr').remove();
        hide = 0;
      })
      .fail(function() {
        console.log('Nu merge');
      });
  }

  function getTileX(index) {
    var wp = TILE_WIDTH + TILE_PADDING;
    return (CANVAS_WIDTH + wp * (2 * index - difficulty + 1)) / 2;
  }

  hide = 0;
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

      if(!hide)
      {
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
        hide = 1;
      }
      else
      {
        $('.wordArea').hide().find('tr').remove();
        hide = 0;
      }
    });
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
    console.log('looking up [' + word + ']');

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

  // move the letter at position pos on row1 to the first open slot on row2
  function moveLetter(pos, row1, row2, y) {
    if (row1[pos]) {
      var i = 0;
      while (row2[i]) {
        i++;
      }
      row2[i] = row1[pos];
      row1[pos] = 0;

      $('canvas').animateLayerGroup(row2[i].groups[0], {
        x: getTileX(i),
        y: y,
      }, ANIMATION_SPEED);
    }
  }

  // move the letter at position pos on the top row to the first open slot on the bottom row
  function gather(pos) {
    moveLetter(pos, upLayers, downLayers, BOTTOM_Y);
  }

  // send the letter at position pos on the bottom row back to the top row
  function scatter(pos) {
    moveLetter(pos, downLayers, upLayers, TOP_Y);
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

    secondsLeft = SECONDS;
    var timer = setInterval(decrementTimer, 1000);

    function decrementTimer() {
      secondsLeft--;
      $('#timer').html(secondsLeft + ' secunde');
      if (!secondsLeft) {
        clearInterval(timer);
        drawEnd();
      }
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

  function dragCancel(layer) {
    // if a letter was dragged, get the corresponding rectangle layer
    layer = $('canvas').getLayer(layer.data.tile);

    for(var i = 0; i < upLayers.length; i++)
    {
      if(upLayers[i] == layer)
      {
        $('canvas').animateLayerGroup(layer.groups[0],{
          x: getTileX(i),
          y: TOP_Y,
        }, ANIMATION_SPEED);
        break;
      }
    }
    for(var j = 0; j < downLayers.length; j++)
    {
      if(downLayers[j] == layer)
      {
        $('canvas').animateLayerGroup(layer.groups[0],{
          x: getTileX(j),
          y: BOTTOM_Y,
        }, ANIMATION_SPEED);
        break;
      }
    }
  }
  
  function dragStop(layer) {
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
    $('canvas').removeLayers();

    upLayers = [];
    downLayers = [];

    for (var i = 0; i < randomWord.length; i++) {

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
          letter: randomWord[i],
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
          text: randomWord[i].toUpperCase(),
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

  init();
  ShowWordsAndEnd();
});
