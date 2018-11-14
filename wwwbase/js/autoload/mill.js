$(function() {

  var word = '';
  var difficulty = 1;
  var answer = '';
  var round = 1;
  var answeredCorrect = 0;
  var defId = 0;
  var guessed = 0;
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
      url: 'ajax/mill.php?d=' + difficulty + '&defId=' + defId +'&guessed=' + guessed,
      cache: false,
      error: function() {
        alert('Nu pot încărca următoarea întrebare. Vă redirectez la pagina principală.');
        window.location = wwwRoot;
      }
    }).done(function(data) {
      $('.word').html(data.word);
      for (var i = 1; i <= 4; i++) {
        $('#mill button[value="' + i + '"] .def').html(data.definition[i].text);
      }
      answer = data.answer;
      defId = data.defId;

      var terms = definitions.map(function (def) {return def.term;});
      for (i = 1; i <= 4; i++) {
        definitions.push(data.definition[i]);
      }
    });
  }

  function optionPressed() {
    if (answer == $(this).val()) {
      $(this).addClass('btn-success');
      $('#statusImage' + round).attr('src', wwwRoot + 'img/mill/success.png');
      answeredCorrect++;
      guessed = 1;
    } else {
      $(this).addClass('btn-danger');
      $('#mill button[value="' + answer + '"]').addClass('btn-success');
      $('#statusImage'+round).attr('src', wwwRoot + 'img/mill/fail.png');
      guessed = 0;
    }

    for(i = 1; i <= 4; i++) {
      $('#mill button[value="' + i + '"]').attr('disabled', true);
    }

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
        for(i = 1; i <= 4; i++) {
          var button = $('#mill button[value="' + i + '"]');
          button.removeClass('btn-success');
          button.removeClass('btn-danger');
          button.attr('disabled', false);
        }
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
