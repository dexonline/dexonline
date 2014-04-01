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
    $.ajax({
      type:"POST",
      url: wwwRoot + "ajax/scramble.php",
      data: { searchWord : searchWord },
    })
    .done(function(response){
      var result = $.parseJSON(response);
      $("#ifFound").html(result.Found);
    })
    .fail(function() {
      console.log("Nu merge");
    });

    // asculta tot documentul pentru apasarea unei taste, daca tasta corespunde numelui layer-ului 
    // atunci se muta pozitia acelui layer pe Y = 150.
    layers = $("canvas").getLayers();
    var key;
    key = letter.keyCode;
    for(i = 0; i< layers.length; i += 2) {
      console.log(layers[i].data.letter);
      if(layers[i].data.letter == "\u00e2" || layers[i].data.letter == "\u0103") {
        layers[i].data.letter = "a";
      }
      if(layers[i].data.letter == "\u00ee" || layers[i].data.letter == "\00ce") {
        layers[i].data.letter = "i";
      }
      if(layers[i].data.letter == "\u0219" || layers[i].data.letter == "\u0218") {
        layers[i].data.letter = "s";
      }
      if(layers[i].data.letter == "\u021b" || layers[i].data.letter == "\u021a") {
        layers[i].data.letter = "t";
      }
      console.log(layers[i].data.letter);
    }

    for(i = 0; i < layers.length; i += 2) {
     /* if(String.fromCharCode(key) == layers[i].data.letter && layers[i].data.selected) {
        $("canvas").animateLayerGroup("boggle" + i / 2, {
          y: 50
        });
        layers[i].data.selected = false;

        return;
      } */
      if(String.fromCharCode(key) == layers[i].data.letter && !layers[i].data.selected) {
        $("canvas").animateLayerGroup("boggle" + i / 2, {
          y: 200
        });
        layers[i].data.selected = true;

        return;
      }
    }
    for(i = layers.length - 1; i >= 0; i--) {
      if(String.fromCharCode(key) == layers[i].data.letter && layers[i].data.selected) {
        $("canvas").animateLayerGroup("boggle" + i / 2, {
          y: 50
        });
        layers[i].data.selected = false;

        return;
      }
    }
  });
  
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
