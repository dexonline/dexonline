$(document).ready(function() {
  const WORD_LIST_DIA_URL = 'https://dexonline.ro/static/download/game-word-list-dia.txt';
  const WORD_LIST_URL = 'https://dexonline.ro/static/download/game-word-list.txt';
  const ALPHABET = 'aăâbcdefghiîjklmnopqrsștțuvwxyz';
  const TILE_WIDTH = 55;
  const TILE_HEIGHT = 75;
  const TILE_FONT_SIZE = 60;
  const SECONDS = 180;

  var score = 0;
  var secondsLeft;
  var check; // Check is diacritics are to be used.
  var randomWord; // word chosen at random from wordList, constrained by difficulty
  var legalWords; // the possible words that can be made from the randomWord.
  var wordsFound; // boolean array indicating which legal words the user has found
  var difficulty;
  var layers = []; // Array of currently drawn layers.
  var upLayers = []; // Letters in the top
  var downLayers = []; // Letters in the bottom
  var threshold = 120; // y axis limit for moving letters
  var layerSpeed = 200; //Global animation speed
  var wordList; // word list downloaded from the server

  function init() {
    $('#mainMenu button').click(function() {
      difficulty = $(this).val();
      check = $("#toggleD").prop('checked');
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
    var f = [];
    for (var i = 0; i < ALPHABET.length; i++) {
      f[ALPHABET[i]] = 0;
    }
    for (var i = 0; i < randomWord.length; i++) {
      f[randomWord[i]]++;
    }

    // iterate through words and select legal ones
    legalWords = [];
    wordsFound = [];

    for (var i in wordList) {
      var legal = true;
      var fcopy = jQuery.extend({}, f);
      // decrement frequencies for the word being examined
      for (var j = 0; j < wordList[i].length; j++) {
        if (--fcopy[wordList[i][j]] < 0) {
          legal = false;
        }
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
        wordList = result.split('\n');
        getLettersAndLegalWords();

        $("#maxWords").html(legalWords.length);
        drawLetters(randomWord);
        //Fire keyboard event checker
        
        //Start Timer
        startTimer();
        
        initLayerArrays();
        $(".wordArea").hide().find('tr').remove();
        hide = 0;
      })
      .fail(function() {
        console.log("Nu merge");
      });
  }

  function initialPosition() {
    switch (layers.length / 2) {
    case 4: return 140;
    case 5: return 80;
    case 6: return 60;
    case 7: return 45;
    default: return 140;
    }
  }

  function initLayerArrays() {

    layers = [];
    upLayers = [];
    downLayers = [];

    layers = $("canvas").getLayers(); // Get drawn layers.
    for(var i = 0 ; i < layers.length; i+= 2) //Select the layers with letter data.
    {
      upLayers.push(layers[i]);
      downLayers.push(0);
    }
  }

  hide = 0;
  function ShowWordsAndEnd() {
    $(".wordBtn").on("click", function() {
      $(this).blur();

      var ul = 0;
      var initialTR = "wordList";
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
            var td = "td" + i;
            var ulist = "ulist" + i;
            $('<td></td>', { "class" : td }).appendTo("." + currentTR);
            $('<ul></ul>', { "class" : ulist + ' list-unstyled'}).appendTo("." + td);
            for(var k = start; k < stop; k++) {
              var list = "<li>" + legalWords[k] + "</li>";
              $("." + ulist).append(list);
            }
            ul++;
            start = stop;
          }
          if($("." + currentTR).children().length % 9 == 0)
          {
            currentTR = initialTR + i;
            $("<tr></tr>", {"class" : currentTR}).appendTo(wordArea);
          }
        }
        hide = 1;
      }
      else
      {
        $(".wordArea").hide().find('tr').remove();
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
      posX = initialPosition();

      score += word.length * 5;
      $("#score").html(score);

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

      var posX = initialPosition();

      $("canvas").animateLayerGroup(row2[i].groups[0], {
        x: posX + (i * 65),
        y: y,
      }, layerSpeed);
    }
  }

  // move the letter at position pos on the top row to the first open slot on the bottom row
  function gather(pos) {
    moveLetter(pos, upLayers, downLayers, 200);
  }

  // send the letter at position pos on the bottom row back to the top row
  function scatter(pos) {
    moveLetter(pos, downLayers, upLayers, 50);
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
      $("#timer").html(secondsLeft + " secunde");
      if (!secondsLeft) {
        clearInterval(timer);
        drawEnd();
      }
    }
  }

  //Draw end screen message
  function drawEnd()
  {
    var x = 240;
    var y = 130;
    $("canvas").removeLayers();

    $("canvas").drawText({
      layer: true,
      draggable: true,
      name: "gameOverText",
      groups: "gameOver",
      dragGroups: "gameOver",
      fillStyle: function(layer) {
        var value = Math.round(layer.x / this.width * 360);
        value = Math.min(value, 360);
        return 'hsl(' + value + ', 50%, 50%)';
      },
      strokeStyle: "black",
      strokeWidth: 2,
      x: 800, y: 120,
      fontSize: 60,
      fontFamily: "Verdana, sans-serif",
      text: "Sfârșit",

    })
      .animateLayer("gameOverText", {
        x: x,
        y: y,
        rotate: '+=360',
      }, layerSpeed + 100);
  }


  // printeaza literele cuvantului random din baza de date
  function drawLetters(array) {
    $("canvas").removeLayers();

    for (var i = 0; i < array.length; i++) {

      var posX =  0;// 110 + ( i * 65 );

      switch(array.length){

      case 4: posX = 140 + (i * 65); break;
      case 5: posX = 80 + (i * 65);  break;
      case 6: posX = 60 + (i * 65);  break;
      case 7: posX = 45 + (i * 65);  break;
      default: posX = 140 + (i * 65);
      }

      $("canvas").drawRect({
        layer: true,
        draggable: true,
        strokeStyle: "black",
        strokeWidth: 4,
        name: "rect" + i,
        fillStyle: '#cceeff',
        groups: ["boggle" + i],
        dragGroups: ["boggle" + i],
        x: 500, y: 50,
        width: TILE_WIDTH,
        height: TILE_HEIGHT,
        cornerRadius: 4,
        data: {
          letter: array[i],
        },
        dragcancel: function(layer) {
          if(layer.x < 35 || layer.x > 465 || layer.y < 35 || layer.y > 265)
          {
            posX = initialPosition();
            for(var i = 0; i < upLayers.length; i++)
            {
              if(upLayers[i] == layer)
              {
                $("canvas").animateLayerGroup(layer.groups[0],{
                  x: posX + (i * 65),
                  y: 50,
                }, layerSpeed);
                break;
              }
            }
            for(var j = 0; j < downLayers.length; j++)
            {
              if(downLayers[j] == layer)
              {
                $("canvas").animateLayerGroup(layer.groups[0],{
                  x: posX + (j * 65),
                  y: 200,
                }, layerSpeed);
                break;
              }
            }
          }
        },
        dragstop: function(layer) {
          var move = false;
          posX = initialPosition();
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
                      $("canvas").animateLayerGroup(downLayers[l].groups[0],{
                        x: posX + (l * 65),
                        y: 200,
                      }, layerSpeed);
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
                      $("canvas").animateLayerGroup(downLayers[l].groups[0],{
                        x: posX + (l * 65),
                        y: 200,
                      }, layerSpeed);
                    }
                  }
                }
              }
              break;
            }
          }
          //Drag down area
          if(layer.y > threshold)  // Move and animate the letter down
          {
            for(var i = 0 ; i < downLayers.length; i++) // Check and reposition back into place if draged on the same area
            {
              if(downLayers[i] == layer)
              {
                $("canvas").animateLayerGroup(layer.groups[0],{
                  x: posX + (i * 65),
                  y: 200,
                }, layerSpeed);
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

                      $("canvas").animateLayerGroup(layer.groups[0],{
                        x: posX + (j * 65),
                        y: 200,
                      }, layerSpeed);
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
                $("canvas").animateLayerGroup(layer.groups[0],{
                  x: posX + (i * 65),
                  y: 50,
                }, layerSpeed);
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
                      $("canvas").animateLayerGroup(layer.groups[0],{
                        x: posX + (j * 65),
                        y: 50,
                      }, layerSpeed);
                      break;
                    }
                  }
                  break;
                }
              }
            }
          }
        },
      })
        .drawText({
          layer: true,
          draggable: true,
          name: "letter" + i,
          groups: ["boggle" + i],
          dragGroups: ["boggle" + i],
          fillStyle: "black",
          strokeStyle: "black",
          strokeWidth: 1,
          x: 500, y: 50,
          fontSize: TILE_FONT_SIZE,
          fontFamily: "Verdana, sans-serif",
          text: array[i].toUpperCase(),
          dragcancel: function(layer) {
            if(layer.x < 35 || layer.x > 455 || layer.y < 35 || layer.y > 255)
            {
              posX = initialPosition();
              for(var i = 0; i < upLayers.length; i++)
              {
                if(upLayers[i] != 0 && upLayers[i].groups[0] == layer.groups[0])
                {
                  $("canvas").animateLayerGroup(layer.groups[0],{
                    x: posX + (i * 65),
                    y: 50,
                  }, layerSpeed);
                  //$("canvas").stopLayerGroup(layer.groups[0]);
                  break;
                }
              }
              for(var j = 0; j < downLayers.length; j++)
              {
                if(downLayers[j] != 0 && downLayers[j].groups[0] == layer.groups[0])
                {
                  $("canvas").animateLayerGroup(layer.groups[0],{
                    x: posX + (j * 65),
                    y: 200,
                  }, layerSpeed);
                  //$("canvas").stopLayerGroup(layer.groups[0]);
                  break;
                }
              }
            }
          },
          dragstop: function(layer) {
            var move = false;
            posX = initialPosition();
            //Switch position area
            for(var i = 0 ; i < downLayers.length; i++)
            {
              if(downLayers[i] != 0 && layer.groups[0] == downLayers[i].groups[0])
              {
                for(var j = 0; j < downLayers.length; j++)
                {
                  if(layer.x < downLayers[j].x && (layer.y < 235 && layer.y > 175))
                  {
                    for(var l = 0; l < downLayers.length; l++)
                    {
                      if(downLayers[l] != 0)
                      {
                        $("canvas").animateLayerGroup(downLayers[l].groups[0],{
                          x: posX + (l * 65),
                          y: 200,
                        }, layerSpeed);
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
                        $("canvas").animateLayerGroup(downLayers[l].groups[0],{
                          x: posX + (l * 65),
                          y: 200,
                        }, layerSpeed);
                      }
                    }
                  }
                }
                break;
              }
            }
            //Drag down area
            if(layer.y > threshold)  // Move and animate the letter down
            {
              for(var i = 0 ; i < downLayers.length; i++) // Check and reposition back into place if draged on the same area
              {
                if(downLayers[i] != 0 && downLayers[i].groups[0] == layer.groups[0])
                {
                  $("canvas").animateLayerGroup(layer.groups[0],{
                    x: posX + (i * 65),
                    y: 200,
                  }, layerSpeed);
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
                  if(upLayers[i] != 0 && upLayers[i].groups[0] == layer.groups[0])
                  {
                    for(var j = 0; j < downLayers.length; j++)
                    {
                      if(downLayers[j] == 0)
                      {
                        for(var k = 0; k < layers.length; k+=2)
                        {
                          if(layers[k].groups[0] == layer.groups[0])
                          {
                            downLayers[j] = layers[k];
                            upLayers[i] = 0;
                            break;
                          }
                        }
                        $("canvas").animateLayerGroup(layer.groups[0],{
                          x: posX + (j * 65),
                          y: 200,
                        }, layerSpeed);
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
                if(upLayers[i] != 0 && upLayers[i].groups[0] == layer.groups[0])
                {
                  $("canvas").animateLayerGroup(layer.groups[0],{
                    x: posX + (i * 65),
                    y: 50,
                  }, layerSpeed);
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
                  if(downLayers[i] != 0 && downLayers[i].groups[0] == layer.groups[0])
                  {
                    for(var j = 0; j < upLayers.length; j++)
                    {
                      if(upLayers[j] == 0)
                      {
                        for(var k = 0; k < layers.length; k+=2)
                        {
                          if(layers[k].groups[0] == layer.groups[0])
                          {
                            upLayers[j] = layers[k];
                            downLayers[i] = 0;
                            break;
                          }
                        }
                        $("canvas").animateLayerGroup(layer.groups[0],{
                          x: posX + (j * 65),
                          y: 50,
                        }, layerSpeed);
                        break;
                      }
                    }
                    break;
                  }
                }
              }
            }
          },
        })
        .animateLayerGroup("boggle" + i, {
          x: posX, y: 50
        }, layerSpeed);
    }
  }

  init();
  ShowWordsAndEnd();
});
