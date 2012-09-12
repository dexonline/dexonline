function loadXMLDoc()
{
  var xmlhttp;
  if (window.XMLHttpRequest)
  {// code for IE7+, Firefox, Chrome, Opera, Safari
    xmlhttp=new XMLHttpRequest();
  }
  else
  {// code for IE6, IE5
    xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
  }
  xmlhttp.onreadystatechange=function()
  {
    if (xmlhttp.readyState==4 && xmlhttp.status==200)
    {
      $(".word").html("<b>"+$(xmlhttp.responseText).find("word").text()+"</b>");
      $('.optionButtons[value="1"]').html("1. "+$(xmlhttp.responseText).find("definition1").text());
      $('.optionButtons[value="2"]').html("2. "+$(xmlhttp.responseText).find("definition2").text());
      $('.optionButtons[value="3"]').html("3. "+$(xmlhttp.responseText).find("definition3").text());
      $('.optionButtons[value="4"]').html("4. "+$(xmlhttp.responseText).find("definition4").text());
      answer = $(xmlhttp.responseText).find("answer").text();
    }
  }
  xmlhttp.open("GET","mill-ajax.php",true);
  xmlhttp.send();
}

function dynamo_optionPressed(field) {
  if(answer == field.val()) {
    field.addClass('buttonGuessed');
    $('#statusImage'+round).attr("src","../img/mill/success.jpg");
    answeredCorrect = answeredCorrect + 1;
  } else {
    field.addClass('buttonMissed');
    $('.optionButtons[value="' + answer + '"]').addClass('buttonHinted');
    $('#statusImage'+round).attr("src","../img/mill/fail.jpg");
  }
  
  for(i=1; i<=4; i++) {
    $('.optionButtons[value="'+i+'"]').attr("disabled", true);
  }
  
  round = round + 1;
  if(round == 11) {
    setTimeout(function() {
      $("#questionPage").hide();
      $("#resultsPage").show();
      $("#answeredCorrect").html(answeredCorrect);
    },2000);
  }
  else {
    setTimeout(function() {
      loadXMLDoc();
      for(i=1; i<=4; i++) {
        $('.optionButtons[value="'+i+'"]').removeClass('buttonHinted');
        $('.optionButtons[value="'+i+'"]').removeClass('buttonGuessed');
        $('.optionButtons[value="'+i+'"]').removeClass('buttonMissed');
        $('.optionButtons[value="'+i+'"]').attr("disabled", false);
      }
    },2000);
  }
}

function dynamo_setDiffculty(field) {
  difficulty = field.val();
  $("#mainPage").hide();
  $("#questionPage").show();
}

$(function() {
  $('.optionButtons').click(function() { dynamo_optionPressed($(this)); });
  $('.difficultyButtons').click(function() { dynamo_setDiffculty($(this)); });
  $('#newGameButton').click(function() { document.location.reload(true); });
  $('.optionButtons').focus();//Make sure to take focus from the search bar, this is the best choice as putting it on a button would make the use of space for clues impossible.

  $("#questionPage").hide();
  $("#resultsPage").hide();
  loadXMLDoc();
  document.addEventListener('keypress', function(event) {
    if(String.fromCharCode(event.charCode) == "1" || String.fromCharCode(event.charCode) == "2" || String.fromCharCode(event.charCode) == "3" || String.fromCharCode(event.charCode) == "4")
      dynamo_optionPressed($('.optionButtons[value="' + String.fromCharCode(event.charCode) + '"]'));
  });
});
