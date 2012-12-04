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
      $('.optionButtons[value="1"]').html("1. " + $(xml).find("definition1").text());
      $('.optionButtons[value="2"]').html("2. " + $(xml).find("definition2").text());
      $('.optionButtons[value="3"]').html("3. " + $(xml).find("definition3").text());
      $('.optionButtons[value="4"]').html("4. " + $(xml).find("definition4").text());
      answer = $(xml).find("answer").text();
      answerId = $(xml).find("answerId").text();
    }
  );  
}

function mill_optionPressed(field) {
  if (answer == field.val()) {
    field.addClass('buttonGuessed');
    $('#statusImage' + round).attr("src", wwwRoot + "img/mill/success.jpg");
    answeredCorrect = answeredCorrect + 1;
    guessed = 1;
  } else {
    field.addClass('buttonMissed');
    $('.optionButtons[value="' + answer + '"]').addClass('buttonHinted');
    $('#statusImage'+round).attr("src", wwwRoot + "img/mill/fail.jpg");
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
    },2000);
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

$(function() {
  $('.optionButtons').click(function() { mill_optionPressed($(this)); });
  $('.difficultyButtons').click(function() { mill_setDifficulty($(this)); });
  $('#newGameButton').click(function() { document.location.reload(true); });
  $('.optionButtons').focus(); //Make sure to take focus from the search bar, this is the best choice as putting it on a button would make the use of space for clues impossible.
});
