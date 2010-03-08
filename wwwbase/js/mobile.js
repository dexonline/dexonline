function fixMobile() {
	var arrow = document.getElementById('inflArrow');
	if (arrow) {
		arrow.innerHTML = 'Arată ';
	}
}

function toggleInflVisibility() {
  var div = document.getElementById('paradigmDiv');
  var arrow = document.getElementById('inflArrow');
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
