var tocbox = function() {

	var framecount = 12,
		toclistel,
		ismoving = 0,
		curframe = 0,
		curwidth = 0,
		interval,
		targetwidth;

	function easein(c,t,s,d) {

		// c = current frame, t = total frames, s = start, d = delta
		return (d * (c /= t) * c) + s;
	}

	function setboxwidth(size) {

		// +5 since we don't want the <ul> width ever to be full zero
		// this allows IE7 & Safari to correctly hide all the links - if the width is zero is goes a bit weird
		toclistel.style.width = (size >= 0) ? (size + 5) + 'px' : 'auto';
	}

	function animateend() {

		if (!interval) return;
		window.clearInterval(interval);
		interval = false;
	}

	function animatestart(direction) {

		// hide open side box icon
		toclistel.className = 'open';

		// work out final natural width of box
		setboxwidth(-1);

		// minus 26 to compensate for the 24px of padding and 2px of border
		targetwidth = toclistel.offsetWidth - 26;
		setboxwidth(curwidth);

		// setup interval timer and ismoving direction
		animateend();
		interval = window.setInterval(animatebox,20);

		ismoving = direction;
	}

	function animatebox() {

		curframe += ismoving;
		curwidth = easein(curframe,framecount,0,targetwidth);
		setboxwidth(curwidth);

		if (curframe >= framecount) {
			// end animation - opened
			curframe = framecount;
			animateend();
			setboxwidth(-1);
			ismoving = 0;
		}

		if (curframe <= 0) {
			// end animation - closed
			curframe = 0;
			animateend();
			setboxwidth(0);
			ismoving = 0;

			// make open icon visible
			toclistel.className = '';
		}
	}

	function ismouseenterleave(el,e) {

		function ischildof(parent,child) {

			if (!child) return false;
			if (parent == child) return true;

			// call ischildof() recursively
			return ischildof(parent,child.parentNode);
		}

		var rel = e.relatedTarget || ((e.type == 'mouseover') ? e.fromElement : ((e.type == 'mouseout') ? e.toElement : false));
		return !(!rel || ischildof(el,rel));
	}

	return {
		init: function() {

			// get reference to <ul id="toc">
			toclistel = document.getElementById('toc');

			// attach event handlers to show/hide side box
			toclistel.onmouseover = function(e) {

				if (!ismouseenterleave(toclistel,e || window.event)) return;
				animatestart(1);
			};

			toclistel.onmouseout = function(e) {

				if (!ismouseenterleave(toclistel,e || window.event)) return;
				animatestart(-1);
			};
		}
	}
}();
