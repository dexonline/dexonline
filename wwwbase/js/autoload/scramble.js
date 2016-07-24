$(document).ready(function() {

  function GetWordAsync(level)
  {
    var result;
     $.ajax({
        type: "POST",
        url: wwwRoot + "ajax/scramble.php",
        data: 'difficulty=' + level + "&diacritic=" + check,
        datatype: "html",
      })
      .done(function(response) {
        result = $.parseJSON(response);
        console.log(result);
        lettersPressed = [];
        totalWords = result.everyWord;

        for(var i = 0; i < totalWords.length; i++)
        {
          totalWords[i] = totalWords[i].toLowerCase();
        }

        $("#maxWords").html(result.everyWord.length);
        drawLetters(result.randomWord);
        //Fire keyboard event checker

        //Start Timer
        startTimer(difficulty);
        //Fire Enter key checker
        checkWord();

        initLayerArrays();
        $(".wordArea").hide().find('tr').remove();
        hide = 0;
      })
      .fail(function() {
        console.log("Nu merge");
      });
  }

  function  reposArray(array, pos1, pos2)
  {
    var i, tmp;
    //cast inputs as integers
    pos1 = parseInt(pos1, 10);
    pos2 = parseInt(pos2, 10);
    // if positions are different inside array
    if (pos1 !== pos2 && 0 <= pos1 && pos1 <= array.length && 0 <= pos2 && pos2 <= array.length)
      {
      //save element from pos1
      tmp = array[pos1];
      // move element down and shift other elements up
      if(pos1 < pos2)
      {
        for(i = pos1; i < pos2; i++)
        {
          array[i] = array[i+1];
        }
      }
      else // move element up and shift other elements down
      {
        for(i = pos1; i > pos2; i--)
        {
          array[i] = array [i - 1];
        }
      }
      //put element from position 1 to destination
      array[pos2] = tmp;
    }
     return array;
  }

  var score = 0;
  var cnt = 0;
  var searchWord;
  var lettersPressed = new String(); // in acest array se retin literele tastate
  var check; // Check is diacritics are to be used.
  var totalWords = new Array(); // the possible words that can be made from the randomWord.
  var difficulty; // initial selected difficulty
  var layers = []; // Array of currently drawn layers.
  var upLayers = []; // Letters in the top
  var downLayers = []; // Letters in the bottom
  var threshold = 120; // y axis limit for moving letters
  var layerSpeed = 200; //Global animation speed

  function selectDifficulty() {

    $("#scramble").find('button').on("click", function() {
      // this e pentru a prelua valoarea butonului tocmai apasat, si nu a unuia oarecare
      difficulty = $(this).attr("value");
      $(this).blur();
      check = $("#toggleD").prop('checked');
      GetWordAsync(difficulty);

    });
  }

  function initialPosition()
  {
    var posX = 0;
    switch(layers.length / 2)
    {
      case 4: posX = 140; break;
      case 5: posX = 80;  break;
      case 6: posX = 60;  break;
      case 7: posX = 45;  break;
      default: posX = 140;
    }
    return posX;
  }

  function initLayerArrays() {

    layers = [];
    upLayers = [];
    downLayers = [];
    lettersPressed = "";

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
      console.log("pushed");
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
        for(var i = 0; i <= totalWords.length; i++)
        {
          if( i % 5 == 0 || (i == totalWords.length && totalWords.length % 5 != 0))
          {
            stop = i;
            var td = "td" + i;
            var ulist = "ulist" + i;
            $('<td></td>', { "class" : td }).appendTo("." + currentTR);
            $('<ul></ul>', { "class" : ulist + ' list-unstyled'}).appendTo("." + td);
            for(var k = start; k < stop; k++)
            {
              if(typeof totalWords[k] === "undefined")
              {
                break;
              }
              else
              {
                var list = "<li>" + totalWords[k] + "</li>";
                $("." + ulist).append(list);
              }
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

  function inputListen() {
    $(document).keyup(function(letter) {
      var key;
      key = letter.keyCode;
      var keyString;
      keyString = String.fromCharCode(key);
      //Transforma diacriticile in caractere fara diacritice.
      for(var i = 0; i< layers.length; i += 2) {
        if(layers[i].data.letter == "\u00c2" || layers[i].data.letter == "\u0102") {
          layers[i].data.selected = "A";
        }
        if(layers[i].data.letter == "\u00ee" || layers[i].data.letter == "\u00ce") {
          layers[i].data.selected = "I";
        }
        if(layers[i].data.letter == "\u0219" || layers[i].data.letter == "\u0218") {
          layers[i].data.selected = "S";
        }
        if(layers[i].data.letter == "\u021b" || layers[i].data.letter == "\u021a") {
          layers[i].data.selected = "T";
        }
      }

      var posX = initialPosition();

      var direction = true;
      // coboara o litera
      for(var i = 0; i < upLayers.length; i++)
      {
        if(upLayers[i] != 0 && (keyString == upLayers[i].data.letter || keyString == upLayers[i].data.selected))
        {
          for(var j = 0; j < downLayers.length; j++)
          {
            if(downLayers[j] == 0)
            {

              downLayers[j] = upLayers[i];
              upLayers[i] = 0;

              $("canvas").animateLayerGroup(downLayers[j].groups[0], {
                x: posX + (j * 65),
                y: 200,
              }, layerSpeed);
              currentWord();
              break;
            }
          }
          direction = false;
          break;
        }
      }
      if(direction)
      {
        //urca o litera
        for(var j = 0; j < downLayers.length; j++)
        {
          if(downLayers[j] != 0 && (keyString == downLayers[j].data.letter || keyString == downLayers[j].data.selected))
          {
            for(var k = 0; k < upLayers.length; k++)
            {
              if(upLayers[k] == 0)
              {

                upLayers[k] = downLayers[j];
                downLayers[j] = 0;

                $("canvas").animateLayerGroup(upLayers[k].groups[0], {
                  x: posX + (k * 65),
                  y: 50,
                }, layerSpeed);
                currentWord();
                break;
              }
            }
          }
        }
      }
    });
  }

  function checkWord() {
    var i = 0;
   // var checkWord = new String();
    var found = 0;
    document.addEventListener("keypress", function(enter) {
      var key;
      key = enter.keyCode;

      if (key == 13 && lettersPressed.length) {
        console.log("checkWord este " + lettersPressed);
        for (var i = 0; i < totalWords.length; i++) {
          if (totalWords[i] == lettersPressed) {
            found = 1;
            break;
          }
        }
        if (found) {
          scoreSystem(lettersPressed, lettersPressed.length);
        }
        $("#score").html(score);
        cnt = 0;
      }
    });
  }

  function currentWord() // Create current word;
  {
    lettersPressed = "";
    for(var k = 0; k < downLayers.length; k++)
    {
      if(downLayers[k] != 0)
      {
        lettersPressed += downLayers[k].data.letter.toLowerCase();
      }
    }
    console.log(lettersPressed);
  }


  var wordsFound = new Array();   // store words we have already found
  var hasFound = 0;
  function scoreSystem(newWord, wordLength) {
    var wPresent = 0; // signals if the word has already been found and scored
    for (var i = -1; i < wordsFound.length; i++) {
      if (wordsFound[i] == newWord) {
        wPresent = 1;
      }
    }
    posX = initialPosition();

    if (wPresent == 0) {
      wordsFound[wordsFound.length] = newWord;
      hasFound++;
      for(var i = 0; i < downLayers.length; i++)
      {
        for(var j = 0; j < upLayers.length; j++)
        {
          if(upLayers[j] == 0)
          {
            upLayers[j] = downLayers[i];
            downLayers[i] = 0;
            $("canvas").animateLayerGroup(upLayers[j].groups[0],{
              x: posX + (j * 65),
              y: 50,
            }, layerSpeed);
            lettersPressed = "";
            currentWord();
            break;
          }
        }
      }
    lettersPressed = [];
    }

    console.log(wordsFound.length, wPresent, wordsFound);
    if(wPresent == 0) {
      if(wordLength < 3) {
        score += 5;
      }
      else if(wordLength < 4) {
        score += 10;
      }
      else if(wordLength < 5) {
        score += 15;
      }
      else if (wordLength <= 6) {
        score += 20;
      }
    }
    return score;
  }


  var counter;
  function startTimer(timeMode) {

    console.log(timeMode);
    var count = 121 / timeMode; // time limit to find words, expresed in seconds
    var countReload = 121 / timeMode;

    count = Math.ceil(count);
    countReload = Math.ceil(countReload);

    clearInterval(counter);
    counter = setInterval(timeLeft, 1000); //1000 will run it every 1 second

      function timeLeft() {
        count = count - 1;
        if (count <= 0) {
          wordsFound = [];
          clearInterval(counter);
          if(hasFound >= 3)
          {
            counter = setInterval(timeLeft, 1000); // auto reload values
            count = countReload;
            var autoWord = GetWordAsync(difficulty);
            totalWords = autoWord.everyWord;
            $("#maxWords").html(autoWord.everyWord.length);
            $("#result").html(autoWord.randomWord);
            drawLetters(autoWord.randomWord);
            initLayerArrays();
            $(".wordArea").hide().find('tr').remove(); // Empty displayed words
            hide = 0;
            //console.log(autoWord.randomWord);
            cnt = 0;
          }
          else
          {
           drawEnd();
          }
          return;
        }
      $("#timer").html(count + " secunde");
      }
  }

    //Draw end screen message
    function drawEnd()
    {
      var x = 240;
      var y = 130;
      $("canvas").removeLayers();
      clearInterval(counter);


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
        text: "Game Over",

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
       //dynamic font and rectangle size
        var d_width    = 55;
        var d_height   = 75;
        var d_fontsize = 60;


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
          fillStyle: function(layer) {
            var value = Math.round(layer.x / this.width * 360);
            value = Math.min(value, 360);
            return 'hsl(' + value + ', 50%, 50%)';
          },
          groups: ["boggle" + i],
          dragGroups: ["boggle" + i],
          x: 500, y: 50,
          width: d_width,
          height: d_height,
          cornerRadius: 4,
          data: {
            letter: array[i].toUpperCase(),
            selected: "",
            shifted: false,
          },
          drag: function(layer) {
            //console.log("Position X Y:" + layer.x + " " + layer.y);
          },
          dragcancel: function(layer) {

            //console.log("X:" + layer.x + "Y:" + layer.y);
            console.log("Rect X:" + layer.x + " " + "Y:" + layer.y);
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

                  //$("canvas").stopLayerGroup(layer.groups[0]);
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

                  //$("canvas").stopLayerGroup(layer.groups[0]);
                  break;
                }
              }
            }
          },
          dragstop: function(layer) {
            var move = false;
            //console.log("rect layer dragged");
            //console.log("rect.y= " + layer.y);
            posX = initialPosition();
            //Switch position area
            for(var i = 0 ; i < downLayers.length; i++)
            {
              //if((layer.x > (l + (i * 65)) || layer.x < (r + (i * 65)) && (layer.y < 230 || layer.y > 165)))
              if(downLayers[i] != 0 && layer== downLayers[i])
              {
                for(var j = 0; j < downLayers.length; j++)
                {
                  if(layer.x < downLayers[j].x && (layer.y < 235 && layer.y > 175))
                  {

                    if (i < j && (j - 1) != 0)
                    {
                      downLayers = reposArray(downLayers, i, j - 1);
                    }
                    else
                    {
                      downLayers = reposArray(downLayers, i , j);
                    }

                    if(typeof downLayers === "undefined") // Try to catch array corruption
                    {
                      downLayers = [];
                      for(var i = 0; i < layers.length; i+=2)
                      {
                        if(layers[i].y > threshold)
                        {
                          downLayers[i] = layers[i];
                        }
                      }
                      for(var j = 0; j < downLayers.length; j++)
                      {
                        $("canvas").animateLayerGroup(downLayers[j].groups[0],{
                        x: posX + (j * 65),
                        y: 200,
                        }, layerSpeed);
                      }
                    }

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
                    currentWord();
                    break;
                  }
                  if(layer.x > downLayers[j].x && (layer.y < 235 && layer.y > 175) && j == downLayers.length - 1)
                  {
                    downLayers = reposArray(downLayers, i , j);
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
                   currentWord();
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
                        currentWord();
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
                        currentWord();
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
          fillStyle: "white",
          strokeStyle: "gray",
          strokeWidth: 1,
          x: 500, y: 50,
          fontSize: d_fontsize,
          fontFamily: "Verdana, sans-serif",
          text: array[i].toUpperCase(),
          drag: function(layer) {
            //console.log("Position X Y:" + layer.x + " " + layer.y);
          },
          dragcancel: function(layer) {
            //console.log("Text X:" + layer.x + " " + "Y:" + layer.y);
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
            //console.log("letter layer dropped at X: " + layer.x + " Y:" + layer.y);
            //console.log("rect.y= " + layer.y);
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
                    if (i < j && (j - 1) != 0)
                    {
                      downLayers = reposArray(downLayers, i, j - 1);
                    }
                    else
                    {
                      downLayers = reposArray(downLayers, i , j);
                    }
                    if(typeof downLayers === "undefined") // Try to catch array corruption
                    {
                      downLayers = [];
                      for(var i = 0; i < layers.length; i+=2)
                      {
                        if(layers[i].y > threshold)
                        {
                          downLayers[i] = layers[i];
                        }
                      }
                      for(var j = 0; j < downLayers.length; j++)
                      {
                        $("canvas").animateLayerGroup(downLayers[j].groups[0],{
                        x: posX + (j * 65),
                        y: 200,
                        }, layerSpeed);
                      }
                    }
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
                    currentWord();
                    break;
                  }
                  if(layer.x > downLayers[j].x && (layer.y < 235 && layer.y > 175) && j == downLayers.length - 1)
                  {
                    downLayers = reposArray(downLayers, i , j);
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
                   currentWord();
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
                        currentWord();
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
                        currentWord();
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
  selectDifficulty();
  inputListen();
  ShowWordsAndEnd();
});
