$(function() {

  const NUM_ROUNDS = 10;

  var round = -1;
  var questions;
  var answeredCorrect = 0;
  var definitions = [];

  function init() {
    $('#main button').click(setDifficulty);
    $('#mill button').click(optionPressed);
    $('#definitionsButton').click(toggleDefinitions);

    document.addEventListener('keypress', function(event) {
      var c = String.fromCharCode(event.charCode);
      if (c >= '1' && c <= '4') {
        c--; // data is 0-based
        // keys 1-4 can either choose the difficulty or choose an answer
        var formId = ($('#main').is(":visible")) ? 'main' : 'mill';
        $('#' + formId + ' button[value="' + c + '"]').click();
      }
    });
  }

  function nextRound() {
    round++;
    var q = questions[round];
    $('.word').html(q.choices[q.answer].word);
    for (var i = 0; i <= 3; i++) {
      var html = q.choices[i].html;
      $('#mill button[value="' + i + '"] .def').html(html);
      definitions.push(q.choices[i]);
    }
    $('#mill button')
      .removeClass('btn-success btn-danger')
      .attr('disabled', false);
  }

  function loadQuestions(difficulty) {
    $.ajax({
      url: 'ajax/mill.php?d=' + difficulty,
      cache: false,
      error: function() {
        alert('Nu pot încărca întrebările. Vă redirectez la pagina principală.');
        window.location = wwwRoot;
      }
    }).done(function(data) {
      questions = data;
      nextRound();
    });
  }

  function optionPressed() {
    var q = questions[round];
    var guessed = +($(this).val() == q.answer);

    if (guessed) {
      $(this).addClass('btn-success');
      $('#statusImage' + round).attr('src', wwwRoot + 'img/mill/success.png');
      answeredCorrect++;
    } else {
      $(this).addClass('btn-danger');
      $('#mill button[value="' + q.answer + '"]').addClass('btn-success');
      $('#statusImage' + round).attr('src', wwwRoot + 'img/mill/fail.png');
    }

    $.get('ajax/millLog.php?id=' + q.millDataId + '&guessed=' + guessed);

    $('#mill button').attr('disabled', true);

    if (round == NUM_ROUNDS - 1) {
      setTimeout(function() {
        $('#questionPage').hide();
        $('#resultsPage').show();
        $('#answeredCorrect').html(answeredCorrect);
        // track played game
        $.post(wwwRoot + 'ajax/trackGame', { game: 'mill' });
      }, 2000);
    } else {
      setTimeout(function() {
        nextRound();
      }, 2000);
    }
  }

  function setDifficulty() {
    var difficulty = $(this).val();
    loadQuestions(difficulty);
    $('#mainPage').hide();
    $('#questionPage').show();
  }

  function toggleDefinitions() {
    if ($('#definitionsSection').text().trim() == '') {

      definitions.sort(function(a, b) {
        return a.word.localeCompare(b.word);
      });

      for (i = 0; i < definitions.length; i++) {
        var html = '<p><b>' + definitions[i].word + ':</b> ' + definitions[i].html + '</p>';
        $('#definitionsSection').append(html);
      }
    }
    $('#defPanel').stop().slideToggle();
  }

  init();
});
