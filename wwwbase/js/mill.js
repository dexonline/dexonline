function loadXMLDoc() {
  $.ajax({
    type: "GET",
    url: "ajax/mill.php?d=" + difficulty + "&answerId=" + answerId +"&guessed=" + guessed,
    dataType: 'xml',
    cache: false,
    error: function() {
      alert('Nu pot încărca următoarea întrebare. Vă redirectez la pagina principală.');
      window.location = wwwRoot;      
    }
  }).done( function(xml) {
      $(".word").html($(xml).find("word").text());
      $('.optionButtons[value="1"]').html("a&#41; " + $(xml).find("definition1").find("text").text());
      $('.optionButtons[value="2"]').html("b&#41; " + $(xml).find("definition2").find("text").text());
      $('.optionButtons[value="3"]').html("c&#41; " + $(xml).find("definition3").find("text").text());
      $('.optionButtons[value="4"]').html("d&#41; " + $(xml).find("definition4").find("text").text());
      answer = $(xml).find("answer").text();
      answerId = $(xml).find("answerId").text();
      
      var terms = definitions.map(function (def) {return def.term;});
      for (i=1; i<=4; i++)
      {
          var term= $(xml).find("definition"+i).find("term").text();
          //do not add duplicate definitions
          if (terms.indexOf(term) != -1) continue;
          var text = $(xml).find("definition"+i).find("text").text();
          var definition = {term: term, text: text};
              definitions.push(definition);
      }
    }
  );  
}

function mill_optionPressed(field) {
  if (answer == field.val()) {
    field.addClass('buttonGuessed');
    $('#statusImage' + round).attr("src", wwwRoot + "img/mill/success.png");
    answeredCorrect = answeredCorrect + 1;
    guessed = 1;
  } else {
    field.addClass('buttonMissed');
    $('.optionButtons[value="' + answer + '"]').addClass('buttonHinted');
    $('#statusImage'+round).attr("src", wwwRoot + "img/mill/fail.png");
    guessed = 0;
  }
  
  for(i = 1; i <= 4; i++) {
    $('.optionButtons[value="' + i + '"]').attr("disabled", true);
    }
  
  if (round == 10) {
    setTimeout(function() {
      $("#questionPage").hide();
      $("#resultsPage").show();
      $("#answeredCorrect").html(answeredCorrect);
    },3000);
  } else {
    round++;
    setTimeout(function() {
      loadXMLDoc();
      for(i = 1; i <= 4; i++) {
        var button = $('.optionButtons[value="' + i + '"]');
        button.removeClass('buttonHinted');
        button.removeClass('buttonGuessed');
        button.removeClass('buttonMissed');
        button.attr("disabled", false);
      }
    },2000);
  }
}

function mill_setDifficulty(field) {
  difficulty = field.val();
  loadXMLDoc();
  $("#mainPage").hide();
  $("#questionPage").show();
  
  document.addEventListener('keypress', function(event) {
    var c = String.fromCharCode(event.charCode);
    if (c >= "1" && c <= "4") {
      mill_optionPressed($('.optionButtons[value="' + c + '"]'));
    }
  });
}

function mill_showDefinitions()
{
   definitions.sort(function(o1, o2) 
                    {
                        if (o1.term < o2.term) return -1;
                        if (o1.term > o2.term) return 1;
                        return 0;
                    });
   for(i=0; i<definitions.length; i++)
   {
    $("#definitionsSection").append("<p>")
            .append("<b>" + definitions[i].term + "</b>")
            .append(", ")
            .append(definitions[i].text);
   }
   
   $("#definitionsSection").show();
   $('#definitionsButton').off('click');
   $('#definitionsButton').click(function() {mill_toggleDefinitions();});
}

function mill_toggleDefinitions()
{
    $("#definitionsSection").toggle();
}

$(function() {
  $('.optionButtons').click(function() { mill_optionPressed($(this)); });
  $('.difficultyButtons').click(function() { mill_setDifficulty($(this)); });
  $('#newGameButton').click(function() { document.location.reload(true); });
  $('#definitionsButton').click(function() { mill_showDefinitions(); });
  $('.optionButtons').focus(); //Make sure to take focus from the search bar, this is the best choice as putting it on a button would make the use of space for clues impossible.
});
