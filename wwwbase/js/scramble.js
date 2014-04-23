$(document).ready(function() {
  callAjax(); // init AJAX
  selectDifficulty();
  inputListen();
  checkWord();
  testing();
});

function callAjax()
{
  result;
   $.ajax({
      type: "POST",
      url: wwwRoot + "ajax/scramble.php",
    })
    .done(function(response) {
      result = $.parseJSON(response);
    })
    .fail(function() {
      console.log("Nu merge");
    });
  console.log(result);
  return result;
}

var score = 0;
var cnt = 0;
var searchWord;
var lettersPressed = new String(); // in acest array se retin literele tastate
var totalWords = new Array(); // the possible words that can be made from the randomWord.
function selectDifficulty() {
  $(".difficultyButton").on("click", function() {
    // this e pentru a prelua valoarea butonului tocmai apasat, si nu a unuia oarecare
    var difficulty = $(this).attr("value");
      
      var word = callAjax();
      cnt = 0;
      lettersPressed = [];
      totalWords = word.everyWord;
      $("#result").html(word.randomWord);
      $("#maxWords").html(word.everyWord.length);
      drawLetters(word.randomWord);
      startTimer(difficulty);
      console.log(totalWords);
      console.log(word.randomWord);
  });
}

function testing() {
$(".wordBtn").on("click", function() {
  console.log("pushed");
    $(".wordArea").html(totalWords + " ");
  });
}

function inputListen() {
  layers = $("canvas").getLayers();

  $(document).keyup(function(letter) {
    //var searchWord = $(this).val();
    var key;
    key = letter.keyCode;
    var keyString;
    keyString = String.fromCharCode(key);

    for(var i = 0; i< layers.length; i += 2) {
      if(layers[i].data.letter == "\u00c2" || layers[i].data.letter == "\u0102") {
        layers[i].data.letter = "A";
      }
      if(layers[i].data.letter == "\u00ee" || layers[i].data.letter == "\u00ce") {
        layers[i].data.letter = "I";
      }
      if(layers[i].data.letter == "\u0219" || layers[i].data.letter == "\u0218") {
        layers[i].data.letter = "S";
      }
      if(layers[i].data.letter == "\u021b" || layers[i].data.letter == "\u021a") {
        layers[i].data.letter = "T";
      }
    }

    // coboara o litera
    for(var i = 0; i < layers.length; i += 2) {   
      if(keyString == layers[i].data.letter && !layers[i].data.selected) {
        $("canvas").animateLayerGroup("boggle" + i / 2, {
          x: 110 + (cnt * 65),
          y: 200
        });
        layers[i].data.selected = true;
        lettersPressed += layers[i].data.letter.toLowerCase(); // retine pozitiile literelor apasate
        console.log(lettersPressed);
      //  valuesPressed[cnt] = i;
        cnt++; 
        console.log("cnt la coborare= " + cnt);
        return;
      }
    }   
    //urca o litera
    for(var i = layers.length - 2; i >= 0; i-= 2) {
      if(keyString == layers[i].data.letter && layers[i].data.selected) {
        $("canvas").animateLayerGroup("boggle" + i / 2, {
          x: 110 + (i / 2 * 65),
          y: 50,
        });
        layers[i].data.selected = false;
        lettersPressed = lettersPressed.replace(layers[i].data.letter.toLowerCase(), '');
        console.log(lettersPressed);
        if(cnt >= 0) {
          cnt--;
        }
        console.log(cnt);
        // return;
      }
    }
    var rePoz = 0;
    for(var i = 0; i <layers.length;i+= 2){
      if(layers[i].data.selected) {
        $("canvas").animateLayerGroup("boggle" + i / 2, {
          x: 110 + (rePoz / 2 * 65),
          y: 200,
        });
        rePoz+=2;
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

    if(key == 13 && lettersPressed.length) {
      console.log("checkWord este " + lettersPressed);
      for(var i = 0; i < totalWords.length; i++) {
        if(totalWords[i] == lettersPressed) {
          found = 1;
          break;
        }
      }
      if(found){
        scoreSystem(lettersPressed, lettersPressed.length);        
      }
      $("#score").html(score);
      cnt = 0;
    }
  });
}

var wordsFound = new Array();   // store words we have already found
function scoreSystem(newWord, wordLength) {
  var wPresent = 0 // signals if the word has already been found and scored
  for(var i = -1; i < wordsFound.length; i++) {
    if(wordsFound[i] == newWord) {
      wPresent = 1;
    } 
  }

  if(wPresent === 0) {
    wordsFound[wordsFound.length] = newWord;
    for(var i = 0; i < layers.length; i+= 2) {
      if(layers[i].y == 200 ) {
        $("canvas").animateLayerGroup("boggle" + i / 2,{
          x: 110 + (i / 2 * 65),
          y: 50
        });
        layers[i].data.selected = false;
      }
    }
  cnt = 0;
  lettersPressed = [];
  }
  
  console.log(wordsFound.length, wPresent, wordsFound);
  if(wPresent === 0) {
          if(wordLength < 3) {
            score += 5;
          } else if(wordLength < 4) {
            score +=10;
          } else if(wordLength < 5) {
            score+=15;
          } else if (wordLength < 6) {
            score+=20;
          }
        }
  return score;
}

 var counter;
function startTimer(timeMode) {
  console.log(timeMode);
  var count = 120 / timeMode; // time limit to find words, expresed in seconds
  var countReload = 120 / timeMode;
clearInterval(counter);
counter = setInterval(timeLeft, 1000); //1000 will run it every 1 second
function timeLeft() {
  count = count - 1;
  if (count <= 0) {
    wordsFound = [];
    clearInterval(counter);
    counter = setInterval(timeLeft, 1000); // auto reload values
    count = countReload;

    var autoWord = callAjax();
    totalWords = autoWord.everyWord;
    $("#maxWords").html(autoWord.everyWord.length);
    $("#result").html(autoWord.randomWord);
    drawLetters(autoWord.randomWord);
    console.log(autoWord.randomWord);
    cnt = 0;

    return;
    }
    $("#timer").html(count + " secs");
  }
}
  // printeaza literele cuvantului random din baza de date
  function drawLetters(array) {
    $("canvas").removeLayers();
     //dynamic font and rectangle size
     var d_width    = 55;
     var d_height   = 75;
     var d_fontsize = 60;
    for (var i = 0; i < array.length; i++) {

      var posX = 110 + ( i * 65 );

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
          selected: false
        }
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
      })
      .animateLayerGroup("boggle" + i, {     
        x: posX, y: 50
      });
    }
  }