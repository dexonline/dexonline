$(function() {

  var word = '';
  var difficulty = 1;
  var answer = '';
  var round = 1;
  var answeredCorrect = 0;
  var defId = 0;
  var definitions = [];

  function init() {
    $('#main button').click(setDifficulty);
    $('#mill button').click(optionPressed);
    $('#definitionsButton').click(toggleDefinitions);

    document.addEventListener('keypress', function(event) {
      var c = String.fromCharCode(event.charCode);
      if (c >= '1' && c <= '4') {
        // keys 1-4 can either choose the difficulty or choose an answer
        var formId = ($('#main').is(":visible")) ? 'main' : 'mill';
        $('#' + formId + ' button[value="' + c + '"]').click();
      }
    });
  }

  function loadXMLDoc() {
    $.ajax({
      url: 'ajax/mill.php?d=' + difficulty,
      cache: false,
      error: function() {
        alert('Nu pot încărca următoarea întrebare. Vă redirectez la pagina principală.');
        window.location = wwwRoot;
      }
    }).done(function(data) {
      $('.word').html(data.choices[data.answer].term);
      for (var i = 1; i <= 4; i++) {
        $('#mill button[value="' + i + '"] .def').html(data.choices[i].text);
      }
      answer = data.answer;
      defId = data.defId;

      for (i = 1; i <= 4; i++) {
        definitions.push(data.choices[i]);
      }
      $('#mill button').removeClass('btn-success btn-danger').attr('disabled', false);
    });
  }

  function optionPressed() {
    var guessed = (answer == $(this).val());

    if (guessed) {
      $(this).addClass('btn-success');
      $('#statusImage' + round).attr('src', wwwRoot + 'img/mill/success.png');
      answeredCorrect++;
    } else {
      $(this).addClass('btn-danger');
      $('#mill button[value="' + answer + '"]').addClass('btn-success');
      $('#statusImage' + round).attr('src', wwwRoot + 'img/mill/fail.png');
    }

    $.get('ajax/millLog.php?defId=' + defId + '&guessed=' + guessed);

    $('#mill button').attr('disabled', true);

    if (round == 10) {
      setTimeout(function() {
        $('#questionPage').hide();
        $('#resultsPage').show();
        $('#answeredCorrect').html(answeredCorrect);
        // track played game
        $.post(wwwRoot + 'ajax/trackGame', { game: 'mill' });
      }, 2000);
    } else {
      round++;
      setTimeout(function() {
        loadXMLDoc();
      }, 2000);
    }
  }

  function setDifficulty() {
    difficulty = $(this).val();
    loadXMLDoc();
    $('#mainPage').hide();
    $('#questionPage').show();
  }

  function toggleDefinitions() {
    if ($('#definitionsSection').text().trim() == '') {

      definitions.sort(function(a, b) {
        return a.term.localeCompare(b.term);
      });

      for(i = 0; i < definitions.length; i++) {
        var html = '<p><b>' + definitions[i].term + ':</b> ' + definitions[i].text + '</p>';
        $('#definitionsSection').append(html);
      }
    }
    $('#defPanel').stop().slideToggle();
  }

  init();
});
