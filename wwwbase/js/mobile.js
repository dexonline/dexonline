function fixMobile() {
	var arrow = document.getElementById('inflArrow');
	if (arrow) {
		arrow.innerHTML = 'Arată ';
	}
}

function toggleInflVisibility(value, lexem) {
  var div = document.getElementById('paradigmDiv');
  var arrow = document.getElementById('inflArrow');
  if (div.innerHTML == '') {
	  param = (lexem ? 'lexemId' : 'cuv') + '=' + value;
	  makeGetRequest(wwwRoot + 'paradigm.php?ajax=1&' + param, getParadigmCallback, null);
  }
  if (div.className == 'paradigmHide') {
	div.className = 'paradigmShow';
    arrow.innerHTML = 'Ascunde ';
  }
  else {
	div.className = 'paradigmHide';
    arrow.innerHTML = 'Arată ';
  }
  return false;
}
