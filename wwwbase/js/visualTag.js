jQuery(document).ready(function() {
  
  var jcrop_api;
  var coords = new Object();
  
  initJcrop();
  resetCoords();

  function initJcrop() {
    $('#jcrop').Jcrop({
      boxHeight: 500,
      boxWidth: 500,
      onSelect: setCoords,
      onChange: setCoords
    }, function() {
      jcrop_api = this;
    });
  };

  function setCoords(c) {
    calculateCentre(c);

    $('#x').val(coords.cx);
    $('#y').val(coords.cy);
  };

  function calculateCentre(c) {
    coords.cx = Math.round((2 * c.x + c.w) / 2);
    coords.cy = Math.round((2 * c.y + c.h) / 2);
  };

  /** Clears the actual selection */
  $('#clrSel').click(function(e) {
    jcrop_api.release();

    resetCoords();
  });

  function resetCoords() {
    coords.cx = 0;
    coords.cy = 0;

    $('#x').val('');
    $('#y').val('');
  };

  $('#setCoordTag').click(function() {
    $('#xTag').val(coords.cx);
    $('#yTag').val(coords.cy);
  });

  $('#setCoordImg').click(function() {
    $('#xImg').val(coords.cx);
    $('#yImg').val(coords.cy);
  });

  $('#toggleHelp').click(function() {
    $('#helpText').toggle();
  });
});

/** Replaces the submit event that triggers on change, set in select2Dev.js */
function replaceSubmitEvent() {
  $('#lexemId').off();
  $('#lexemId').on('change', function(e){
    var id = $(this).select2('data').id;
    var text = $(this).select2('data').text;

    // Matches only the lexeme, without the description in brackets 
    text = text.match(/^[^ \(]+/);

    $('#lexemeId').val(id);
    $('#label').val(text);
  });
}

function validateTag() {
  var label = $('#label').val();
  var xImg = $('#xImg').val();
  var yImg = $('#yImg').val();
  var xTag = $('#xTag').val();
  var yTag = $('#yTag').val();

  if(!label) {
    alert('Ai uitat să completezi câmpul Cuvânt');
    return false;

  } else if(!xTag || !yTag) {
    alert('Ai uitat să completezi câmpurile Coordonatele centrului etichetei');
    return false;

  } else if(!xImg || !yImg) {
    alert('Ai uitat să completezi câmpurile Coordonatele zonei etichetate');
    return false;
  }
};

function validateLexeme() {
  var lexeme = $('#imgLexemeId').val();

  if(!lexeme) {
    alert('Ai uitat să completezi ce lexem descrie cel mai bine imaginea');
    return false;
  }
};
