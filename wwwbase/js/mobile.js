function fixMobile() {
	var arrow = document.getElementById('inflArrow');
	if (arrow) {
		arrow.innerHTML = 'Arată ';
	}
	window.scrollTo(0, 1);
}

function toggleInflVisibility(value, lexem) {
  var div = $('#paradigmDiv');
  if (trim(div.html()) == '') {
	  param = (lexem ? 'lexemId' : 'cuv') + '=' + value;
    $.get(wwwRoot + 'paradigm.php?ajax=1&' + param)
      .done(function(data) { div.html(data).slideToggle(); }); // Slide only after content is added
  } else {
    div.slideToggle();
  }
  var arrow = $('#inflArrow');
  arrow.html((arrow.html() == 'Ascunde ') ? 'Arată ' : 'Ascunde ');
  return false;
}
