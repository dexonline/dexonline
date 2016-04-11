function mlUpdateDefVisibility(lexemId, divId) {
  var div = $('#' + divId);
  // If the definitions are already loaded, then just toggle the div's visibility.
  if (trim(div.html()) == '') {
    $.get(wwwRoot + 'ajax/getDefinitionsForLexem.php?lexemId=' + lexemId)
      .done(function(data) { div.html(data).slideToggle(); })
      .fail('Nu pot descărca lista de definiții.');
  } else {
    div.slideToggle();
  }
  return false;
}

// On the abbreviation review page, we must check that the user made selections for all the ambiguous abbreviations
// before we activate the submit button.
function initAbbrevCounter(size) {
  abbrev_clickTracker = Array(size);
  abbrev_numLeft = size;
}

function pushAbbrevButton(id, state) {
  var span = $('#abrevText_' + id);
  if (state) { // Long word
    span.css('border-bottom', '2px solid green');
    span.removeClass('abbrev');
  } else {
    span.css('border-bottom', '2px solid red');
    span.addClass('abbrev');
  }
  if (!abbrev_clickTracker[id]) {
    abbrev_clickTracker[id] = 1;
    abbrev_numLeft--;
    if (!abbrev_numLeft) {
      $('#submitButton').removeAttr('disabled');
    }
  }
}
