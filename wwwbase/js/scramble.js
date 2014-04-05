$(document).ready(function() {
  selectDifficulty();
  inputListen();
});

function selectDifficulty() {
  $(".difficultyButton").on("click", function() {
    // this e pentru a prelua valoarea butonului tocmai apasat, si nu a unuia oarecare
    var difficulty = $(this).attr("value");
    $.ajax({
      type: "POST",
      url: wwwRoot + "ajax/scramble.php",
      data: { difficulty : difficulty },
    })
    .done(function(response) {
     var word = $.parseJSON(response);
      $("#result").html(word.randomWord);
      $("#noWords").html(word.noWords);
      drawLetters(word.randomWord);
      startTimer(difficulty);
      console.log(word.randomWord);
      $(".searchWord").val(""); // clears input
    })
    .fail(function() {
      console.log("Nu merge");
    });
  });
}

  // test pentru cuvinte returnate prin json
  var score = 0;
  var cnt = 0;
function inputListen() {
  layers = $("canvas").getLayers();
  var lettersPressed = new Array(); // in acest array se retin pozitiile literelor tastate
  $(".searchWord").keyup(function(letter) {
    var searchWord = $(this).val();
    var letterCheck; // checks if the inputed letters correspond to the random word
    // var score = 0;
    $.ajax({
      type:"POST",
      url: wwwRoot + "ajax/scramble.php",
      data: { searchWord : searchWord },
    })
    .done(function(response){
      var enter;
      enter = letter.keyCode;
      if(enter == 13) {
       /* for(var i = 0; i < searchWord.length; i++) {
      for(var j = 0; j < word.length; j++) {
        if(word.randomWord[i] == searchWord[j]) {
          letterCheck++;
          console.log(letterCheck);
        }
      }
    }
  if(letterCheck == searchWord.length) { */
      	var result = $.parseJSON(response);
        scoreSystem(result.Found,searchWord,searchWord.length);        
        $("#score").html(score);
        $("#ifFound").html(result.Found);
        $(window).load(function() {
        $(".searchWord").val("");
        }); 
      }
 //   }
    })
    .fail(function() {
      console.log("Nu merge");
    });

    var key;
    var keyString;
    key = letter.keyCode;
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
          x: 50 + (cnt * 55),
          y: 200
        });
        layers[i].data.selected = true;
        lettersPressed[cnt] = i; // retine pozitiile literelor apasate
        cnt++; // modifica pozitia literei, literele se coboara relativ la ultima litera tastata
        return;
      }
    }   
    // urca o litera, daca aceasta este ultima litera introdusa
    for(var i = layers.length - 2; i > 0; i-= 2) {
      if(keyString == layers[i].data.letter && layers[i].data.selected) {
        $("canvas").animateLayerGroup("boggle" + i / 2, {
          x: 50 + (i / 2 * 55),
          y: 50,
        });
        layers[i].data.selected = false;
        cnt--;
        // return;
      }
    }
    // urca ultima litera atunci cand se apasa tasta "backspace"
    if(key == 8 && cnt > 0) {
        var position = lettersPressed[cnt-1];
        $("canvas").animateLayerGroup("boggle" + position / 2, {
          x: 50 + (position/2 * 55),
          y: 50,
        });
        layers[position].data.selected = false; 
        cnt--;
        return;
    }

  });
}


var wordsFound = new Array();   // store words we have already found
function scoreSystem(foundWord,newWord,wordLength)
{
  var wPresent = 0 // signals if the word has already been found and scored
  if(foundWord == 1) {
    for(var i = -1; i < wordsFound.length; i++) {
      if(wordsFound[i] == newWord) {
        wPresent = 1;
      } 
    }
    if(wPresent === 0) {
        wordsFound[wordsFound.length] = newWord;
  }
  for(var i = 0; i < layers.length; i+= 2) {
    if(layers[i].y == 200 ) {
      $("canvas").animateLayerGroup("boggle" + i / 2,{
        x: 50 + (i / 2 * 55),
        y: 50
      });
    }
  }
  cnt = 0;
}
  console.log(wordsFound.length,wPresent,wordsFound);
  if(foundWord == 1 && wPresent === 0) {
          if(wordLength < 4) {
            score += 5;
          } else if(wordLength < 5) {
            score +=10;
          } else if(wordLength < 6) {
            score+=15;
          } else if (wordLength < 10) {
            score+=20;
          }
        }
        return score;
}

function startTimer(timeMode) {
  console.log(timeMode);
  var count = 120 / timeMode; // time limit to find words, expresed in seconds
  var countReload = 120 / timeMode;
  var counter;
clearInterval(counter);
counter = setInterval(timeLeft, 1000); //1000 will run it every 1 second
function timeLeft() {
  count = count - 1;
  if (count <= 0) {
     clearInterval(counter);
     counter = setInterval(timeLeft, 1000); // auto reload values
     count = countReload;
      var randDifficulty = Math.floor((Math.random()*5)+1);
    $.ajax({
      type: "POST",
      url: wwwRoot + "ajax/scramble.php",
      data: { difficulty : randDifficulty },
    })
    .done(function(response) {
      var autoWord = $.parseJSON(response);
      $("#result").html(autoWord.randomWord);
      $("#noWords").html(autoWord.noWords);
      drawLetters(autoWord.randomWord);
      console.log(autoWord.randomWord);
      $(".searchWord").val("");
      cnt = 0;
    })
    .fail(function() {
      console.log("Nu merge");
    });
       return;
    }
    $("#timer").html(count + " secs");
  }
}
  // printeaza literele cuvantului random din baza de date
  function drawLetters(array) {
    $("canvas").removeLayers();
     //dynamic font and rectangle size
     var d_width;
     var d_height;
     var d_fontsize;
     if ( array.length > 8 ) {
            d_width = 35;
            d_height = 55;
            d_fontsize = 40; 
          } else {
            d_width = 45;
            d_height = 70;
            d_fontsize = 50;
          }
    for (var i = 0; i < array.length; i++) {

      var posX = 50 + ( i * 55 );

      $("canvas").drawRect({
        layer: true,
        // draggable: true,
        strokeStyle: "black",
        strokeWidth: 4,
        name: "rect" + i,
        fillStyle: function(layer) {
          var value = Math.round(layer.x / this.width * 360);
          value = Math.min(value, 360);
          return 'hsl(' + value + ', 50%, 50%)';
        },
        groups: ["boggle" + i],
        // dragGroups: ["boggle" + i],
        x: 320, y: -30,
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
        // draggable: true,
        name: "letter" + i,
        groups: ["boggle" + i],
        // dragGroups: ["boggle" + i],
        fillStyle: "white",
        strokeStyle: "gray",
        strokeWidth: 1,
        x: 320, y: -30,
        fontSize: d_fontsize,
        fontFamily: "Verdana, sans-serif",
        text: array[i].toUpperCase(),
      })
      .animateLayerGroup("boggle" + i, {     
        x: posX, y: 50
      });
    }
  }
  // goleste continutul input-ului dupa ce pagina este reincarcata
  $(window).load(function() {
    $(".searchWord").val("");
  }); 
