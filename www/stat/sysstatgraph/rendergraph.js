var rendergraph = function() {

	var colorlist = ['#f00','#0f0','#00f'],
		valuemarkerspacing = 30,
		timemarkerspacing = 150,
		timemarkerwidth = 80,
		axislinespadding = 4,
		infoboxwidth = 250,
		pointerinfoboxoffset = 15,

		// builddateparts is a function that returns a friendly date/time from a given timestamp
		builddateparts,

		// graph instance id and TOC list element used to build table of contents at head of document
		graphinstanceid = -1,
		toclistel,

		// canvas mouseover info box variables
		iscanvashover,
		canvasposx,
		canvasposy,
		canvasinfoboxel;

	function $(id) { return document.getElementById(id); }

	function node(name,attrib,text) {

		var el = document.createElement(name);
		if (attrib) {
			for (var i in attrib) el[i] = attrib[i];
		}

		if (text) el.appendChild(document.createTextNode(text));

		return el;
	}

	function addgraphtotoc(graphtitle) {

		if (!toclistel) {
			// create new <ul> and insert into the DOM to hold table of contents
			toclistel = node('ul',{ id: 'toc' });
			$('content').appendChild(toclistel);
		}

		// create list item and child anchor node
		var toclistitemel = node('li')
			tocanchorel = node('a',{ href: '#g' + graphinstanceid },graphtitle);

		toclistitemel.appendChild(tocanchorel);
		toclistel.appendChild(toclistitemel);
	}

	function calcgraphminmaxvalues(graphlinelist) {

		var minval,
			maxval;

		// init minval/maxval with very first value in our graph lines
		minval = maxval = graphlinelist[0].data[0];

		for (var i = 0,j = graphlinelist.length;i < j;i++) {
			var lineminmax = calcgraphlineminmaxvalues(graphlinelist[i]);
			minval = Math.min(lineminmax.min,minval);
			maxval = Math.max(lineminmax.max,maxval);
		}

		return { min: minval,max: maxval };
	}

	function calcgraphlineminmaxvalues(graphline) {

		var minval,
			maxval;

		// init minval/maxval with very first value in our graph lines
		minval = maxval = graphline.data[0];

		for (var i = 0,j = graphline.data.length;i < j;i++) {
			minval = Math.min(graphline.data[i],minval);
			maxval = Math.max(graphline.data[i],maxval);
		}

		return { min: minval,max: maxval };
	}

	function buildvaluemarkers(axislinesel,canvasheight,ispercentgraph,graphminmax) {

		// calculate number of markers for the y-axis, what each value step will be and the value for the first marker
		// if calculated value step is less than one, render value markers to two decimal places
		var markercount = Math.floor(canvasheight / valuemarkerspacing),
			valuestep = Math.abs(((ispercentgraph) ? 100 : (graphminmax.max - graphminmax.min)) / markercount),
			decimalvalues = (valuestep < 1),
			curvalue = (ispercentgraph) ? valuestep : graphminmax.min + valuestep;

		while (markercount > 0) {
			// create marker node
			var notchvalue = (decimalvalues) ? roundto2decplaces(curvalue) : Math.floor(curvalue),
				el = node('p',{ className: 'markervalue' },notchvalue + ((ispercentgraph) ? '%' : ''));

			// place into graph DOM and position
			el.style.top = ((markercount - 1) * valuemarkerspacing) + 'px';
			axislinesel.appendChild(el);

			markercount--;
			curvalue += valuestep;
		}
	}

	function roundto2decplaces(value) {

		// round number to two decimal places
		value = (Math.round(value * 100) / 100) + '';
		if (value.indexOf('.') < 0) value += '.';
		while (value.indexOf('.') > value.length - 3) value += '0';

		return value;
	}

	function buildtimemarkers(axislinesel,canvaswidth,starttimestamp,timepointlist) {

		var timestep = Math.floor(timepointlist.length / (canvaswidth / timemarkerspacing)),
			curtimestamp = starttimestamp,
			curposition = -timemarkerwidth + axislinespadding;

		for (var i = 0,j = timepointlist.length;i < j;i++) {
			curtimestamp += timepointlist[i];

			// if first loop or current timepoint is not our next timestep - next loop
			if ((i == 0) || ((i % timestep) != 0)) continue;

			// render current time point
			curposition += timemarkerspacing;

			// if curposition is past end of canvas end for loop
			if ((curposition + timemarkerwidth) > canvaswidth) break;

			var dateparts = builddateparts(curtimestamp),
				el = node('p',{ className: 'markertime' });

			el.appendChild(node('span',false,dateparts[0]));
			el.appendChild(node('span',false,dateparts[1]));

			// place into graph DOM and position into place
			el.style.left = curposition + "px";
			axislinesel.appendChild(el);
		}
	}

	function buildlegend(axislinesel,graphlinelist,ispercentgraph) {

		var containerel = node('ul',{ className: 'legend' });

		function appendminmax(label,value) {

			var spanel = node('span'),
				labelel = node('strong',false,label + ':');

			spanel.appendChild(labelel);
			spanel.appendChild(document.createTextNode(
				' ' + value +
				((ispercentgraph) ? '%' : '')
			));

			return spanel;
		}

		for (var line = 0,j = graphlinelist.length;line < j;line++) {
			var listel = node('li'),
				boxel = node('span',{ className: 'box' }),
				detailel = node('div'),
				labelel = node('span',false,graphlinelist[line].title);

			// set the color of the legend box
			boxel.style.backgroundColor = colorlist[line];

			// add list item, color code box
			containerel.appendChild(listel);
			listel.appendChild(boxel);
			listel.appendChild(detailel);

			// add label and min/max details to legend container
			var lineminmax = calcgraphlineminmaxvalues(graphlinelist[line]);

			detailel.appendChild(labelel);
			detailel.appendChild(appendminmax('Min',roundto2decplaces(lineminmax.min)));
			detailel.appendChild(appendminmax('Max',roundto2decplaces(lineminmax.max)));
		}

		// append legend container to graph axis container
		axislinesel.appendChild(containerel);
	}

	function creategraph(graphtitle,graphlinelist,ispercentgraph,canvaswidth,canvasheight,graphminmax,starttimestamp,timepoints) {

		var graphel = node('div',{ className: 'graph' }),
			axislinesel = node('div',{ className: 'axislines' }),
			canvasel = node('canvas',{ width: canvaswidth, height: canvasheight });

		// create graph heading, append axis node and canvas
		graphel.appendChild(node('h2',{ id: 'g' + graphinstanceid },graphtitle));
		graphel.appendChild(axislinesel);
		axislinesel.appendChild(canvasel);

		// build y axis value markers and x axis time markers
		buildvaluemarkers(axislinesel,canvasheight,ispercentgraph,graphminmax);
		buildtimemarkers(axislinesel,canvaswidth,starttimestamp,timepoints);

		// add legend to graph
		buildlegend(axislinesel,graphlinelist,ispercentgraph);

		// return graph container, canvas element and canvas context
		return {
			graphel: graphel,
			canvasel: canvasel,
			canvasctx: canvasel.getContext('2d')
		};
	}

	function plotgraphline(canvasctx,graphlinedata,color,xvaluemulti,yvaluemulti,yaxisoffset) {

		function getvalue(pos) { return yaxisoffset - Math.floor(graphlinedata[pos] * yvaluemulti); }

		// set current context stroke color
		canvasctx.strokeStyle = color;

		// start path, position at first value
		canvasctx.beginPath();
		canvasctx.moveTo(0,getvalue(0));

		// draw path lines
		for (var i = 1,j = graphlinedata.length;i < j;i++) {
			canvasctx.lineTo(Math.floor(i * xvaluemulti),getvalue(i));
		}

		// stroke path to canvas
		canvasctx.stroke();
	}

	function canvasmouseover(e,canvasel,canvaswidth,graphlinelist,starttimestamp,timepoints,ispercentgraph) {

		// calc x/y on page coords of canvas and save to canvashoverx/canvashovery
		(function(el) {

			canvasposx = canvasposy = 0;

			while (el.offsetParent) {
				canvasposx += el.offsetLeft;
				canvasposy += el.offsetTop;
				el = el.offsetParent;
			}
		})(canvasel);

		iscanvashover = true;

		// put canvas info box next to <canvas> in DOM - if info box does not exist then create it
		if (!canvasinfoboxel) canvasinfoboxel = node('div',{ id: 'graphinfobox' });

		// place info box relative to mouse pointer & update info box values
		placeinfobox(e.pageX,e.pageY,canvaswidth);
		updateinfoboxdata(e.pageX,canvaswidth,graphlinelist,starttimestamp,timepoints,ispercentgraph);

		// place infobox into DOM
		canvasel.parentNode.appendChild(canvasinfoboxel);
	}

	function canvasmousemove(e,canvaswidth,graphlinelist,starttimestamp,timepoints,ispercentgraph) {

		if (!iscanvashover) return;

		// place info box relative to mouse pointer & update info box values
		placeinfobox(e.pageX,e.pageY,canvaswidth);
		updateinfoboxdata(e.pageX,canvaswidth,graphlinelist,starttimestamp,timepoints,ispercentgraph);
	}

	function canvasmouseout() {

		if (!iscanvashover) return;
		iscanvashover = false;

		// remove info box from DOM
		canvasinfoboxel.parentNode.removeChild(canvasinfoboxel);
	}

	function placeinfobox(mousex,mousey,canvaswidth) {

		// determine if info box should be placed to the left or right of the mouse pointer
		var positionx = ((axislinespadding + pointerinfoboxoffset + mousex) - canvasposx);
		if (mousex > Math.floor(canvaswidth / 2)) {
			positionx -= (infoboxwidth + (pointerinfoboxoffset * 2));
		}

		// update info box left/top placements
		canvasinfoboxel.style.left = positionx + "px";
		canvasinfoboxel.style.top = ((axislinespadding + pointerinfoboxoffset + mousey) - canvasposy) + "px";
	}

	function updateinfoboxdata(mousex,canvaswidth,graphlinelist,starttimestamp,timepoints,ispercentgraph) {

		function updatenodetext(el,text) { el.firstChild.nodeValue = text; }
		function calcindex(multiplier,length) { return Math.max(0,Math.ceil(multiplier * length) - 1); }

		// store graphlinelist length & fetch all datarow <div> and inner <span> elements already in info box
		var graphlinelistlength = graphlinelist.length,
			datarowellist = canvasinfoboxel.getElementsByTagName('div'),
			datarowvalueellist = canvasinfoboxel.getElementsByTagName('span');

		// if number of data row <div> elements is less than number of graphlinelist (plus to to account for 'current date'), add more <div> elements now
		if ((graphlinelistlength + 1) > datarowellist.length) {
			var createcount = (graphlinelistlength + 1) - datarowellist.length;
			for (var i = 0;i < createcount;i++) {
				// create label and value nodes
				var datarowel = node('div',false,'Label'),
					datarowvalueel = node('span',false,'Value');

				// append nodes to info box
				canvasinfoboxel.appendChild(datarowel);
				datarowel.appendChild(datarowvalueel);
			}
		}

		// calculate mouse position across canvas as a decimal (between 0 - 1)
		var mousecanvasdec = (mousex - canvasposx) / canvaswidth;

		// update info box report date at current mouse position
		updatenodetext(datarowellist[0],'Date:');
		datarowellist[0].className = 'date';

		// calculate current timestamp
		var targettimepoint = calcindex(mousecanvasdec,timepoints.length),
			timestamp = starttimestamp;

		while (targettimepoint >= 0) {
			timestamp += timepoints[targettimepoint];
			targettimepoint--;
		}

		// create friendly date/time from timestamp
		var dateparts = builddateparts(timestamp);
		updatenodetext(datarowvalueellist[0],dateparts[0] + ' ' + dateparts[1]);

		// now update labels/values for each data row element
		for (var i = 1;i < datarowellist.length;i++) {
			if (i > graphlinelistlength) {
				// don't need this <div> label, hide it
				datarowellist[i].style.display = 'none';
				continue;
			}

			// show this <div> label
			datarowellist[i].style.display = 'block';

			// set <div> label name and value
			updatenodetext(datarowellist[i],graphlinelist[i - 1].title + ':');
			var data = graphlinelist[i - 1].data;

			updatenodetext(
				datarowvalueellist[i],
				data[calcindex(mousecanvasdec,data.length)] + ((ispercentgraph) ? '%' : '')
			);
		}
	}

	return {
		setbuilddatepartsfunc: function(func) { builddateparts = func; },

		instance: function(graphtitle,canvaswidth,canvasheight,starttimestamp,timepoints) {

			var graphlinelist = [],
				ispercentgraph;

			return {
				addgraphline: function(title,datalist) {

					graphlinelist[graphlinelist.length] = {
						title: title,
						data: datalist
					};
				},

				setpercent: function() {

					ispercentgraph = true;
				},

				render: function() {

					if (!graphlinelist.length) return;

					// increment the graph instance id, used to build the jump to anchor TOC
					graphinstanceid++;

					// add graph to the <ul> TOC at top of page
					addgraphtotoc(graphtitle);

					// calc min/max values across all graph line sets
					// calc x-axis/y-axis value multiplier -> pixels on canvas, and y-axis start offset
					var graphminmax = calcgraphminmaxvalues(graphlinelist),
						xvaluemulti = canvaswidth / (graphlinelist[0].data.length - 1),
						yvaluemulti = 0,
						yaxisoffset = canvasheight,

						// create a new canvas element and place graph markers on it
						graphreference = creategraph(
							graphtitle,
							graphlinelist,
							ispercentgraph,
							canvaswidth,canvasheight,
							graphminmax,
							starttimestamp,timepoints
						);

					// calc yvaluemulti & yaxisoffset, if (graphminmax.max == graphminmax.min) (most likely due to zero values) then don't calculate
					if (ispercentgraph) {
						yvaluemulti = canvasheight / 100;

					} else if (graphminmax.max != graphminmax.min) {
						yvaluemulti = canvasheight / (graphminmax.max - graphminmax.min);
						yaxisoffset = Math.floor(canvasheight + (yvaluemulti * graphminmax.min));
					}

					// plot graph lines onto canvas
					for (var i = 0,j = graphlinelist.length;i < j;i++) {
						plotgraphline(
							graphreference.canvasctx,
							graphlinelist[i].data,
							colorlist[i],
							xvaluemulti,yvaluemulti,
							yaxisoffset
						);
					}

					// insert completed graph into DOM
					$('content').appendChild(graphreference.graphel);

					// setup event handlers for mouseover/out events to show graph details at mouse pointer
					var canvasel = graphreference.canvasel;

					canvasel.addEventListener(
						'mouseover',
						function(e) {

							canvasmouseover(
								e,this,canvaswidth,
								graphlinelist,starttimestamp,timepoints,
								ispercentgraph
							);
						},
						false
					);

					canvasel.addEventListener(
						'mousemove',
						function(e) {

							canvasmousemove(
								e,canvaswidth,
								graphlinelist,starttimestamp,timepoints,
								ispercentgraph
							);
						},
						false
					);

					canvasel.addEventListener('mouseout',canvasmouseout,false);
				}
			};
		}
	};
}();
