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
    for(i = 0; i < layers.length; i += 2) {
      if(String.fromCharCode(key) == layers[i].data.letter && !layers[i].data.selected) {
        $("canvas").animateLayerGroup("boggle" + i / 2, {
          y: 200
        });
        layers[i].data.selected = true;

        return;
      }
    }
  });
  
  // printeaza literele cuvantului random din baza de date
  function drawLetters(array) {
    $("canvas").removeLayers();
    for (var i = 0; i < array.length; i++) {

      var posX = 50 + ( i * 40 );

      $("canvas").drawRect({
        layer: true,
        // draggable: true,
        name: "rect" + i,
        fillStyle: "black",
        groups: ["boggle" + i],
        // dragGroups: ["boggle" + i],
        x: 320, y: -30,
        width: 35,
        height: 45,
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
        fillStyle: "#9cf",
        strokeStyle: "#25a",
        strokeWidth: 2,
        x: 320, y: -30,
        fontSize: 32.5,
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
