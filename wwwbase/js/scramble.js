$(document).ready(function() {

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
      console.log(word.randomWord);
      $(".searchWord").val("");
    })
    .fail(function() {
      console.log("Nu merge");
    });
  });

  // test pentru cuvinte returnate prin json
  $(".searchWord").keyup(function(letter) {
    var searchWord = $(this).val();
    var score = 0;
    $.ajax({
      type:"POST",
      url: wwwRoot + "ajax/scramble.php",
      data: { searchWord : searchWord },
    })
    .done(function(response){
      var result = $.parseJSON(response);
      if(result.Found == 1) {
        score += 10;
      }
      $("#score").html(score);
      $("#ifFound").html(result.Found);
    })
    .fail(function() {
      console.log("Nu merge");
    });

    // asculta tot documentul pentru apasarea unei taste, daca tasta corespunde numelui layer-ului 
    // atunci se muta pozitia acelui layer pe Y = 150.
    layers = $("canvas").getLayers();
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
    
    for(var i = 0; i < layers.length; i += 2) {
     /* if(String.fromCharCode(key) == layers[i].data.letter && layers[i].data.selected) {
        $("canvas").animateLayerGroup("boggle" + i / 2, {
          y: 50
        });
        layers[i].data.selected = false;

        return;
      } */

      if(keyString == layers[i].data.letter && !layers[i].data.selected) {
        $("canvas").animateLayerGroup("boggle" + i / 2, {
          y: 200
        });
        layers[i].data.selected = true;

        return;
      }
    }
    for(var i = layers.length - 2; i > 0; i-= 2) {
      if(keyString == layers[i].data.letter && layers[i].data.selected) {
        $("canvas").animateLayerGroup("boggle" + i / 2, {
          y: 50
        });
        layers[i].data.selected = false;
       // return;
      }
    }
  });



var count = 30; // time limit to find words, expresed in seconds
var counter = setInterval(timeLeft, 1000); //1000 will  run it every 1 second

function timeLeft() {
  count = count - 1;
  if (count <= 0) {
     clearInterval(counter);
     counter = setInterval(timeLeft, 1000); // auto reload values
     count = 30;
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
    })
    .fail(function() {
      console.log("Nu merge");
    });
     return;
  }
  $("#timer").html(count + " secs");
}


  // printeaza literele cuvantului random din baza de date
  function drawLetters(array) {
    $("canvas").removeLayers();
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
        width: 45,
        height: 70,
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
        fontSize: 50,
        fontFamily: "Verdana, sans-serif",
        text: array[i].toUpperCase(),
      })
      .animateLayerGroup("boggle" + i, {     
        x: posX, y: 50
      });
    }

    /*var layers = $("canvas").getLayers();
    console.log(layers);
    keyListen(layers);*/
  }

  // goleste continutul input-ului dupa ce pagina este reincarcata
  $(window).load(function() {
    $(".searchWord").val("");
  }); 
});
