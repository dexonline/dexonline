var sysstatgraph = function() {

	function $(id) { return document.getElementById(id); }

	function getvaluelist(key) { return sysstatgraph.statdata.valuelist[key]; }

	function getviewportwidth() {

		var docel = document.documentElement || null;
		return (docel && docel.clientWidth) ? docel.clientWidth : window.innerWidth;
	}

	function buildparagraphtext(text) {

		var el = document.createElement('p');
		el.appendChild(document.createTextNode(text));

		return el;
	}

	function builddateparts(timestamp) {

		function padzero(value) { return (value < 10) ? '0' + value : value; }
		var date = new Date(timestamp * 1000);

		return [
			date.getFullYear() + '-' + padzero(date.getMonth() + 1) + '-' + padzero(date.getDate()),
			padzero(date.getHours()) + ':' + padzero(date.getMinutes())
		];
	}

	return {
		init: function() {

			if (!sysstatgraph.statdata.starttime) {
				// no stat data to render - display message and exit
				$('content').appendChild(buildparagraphtext('Unable to render SYSSTAT graph data - possible configuration error.'));
				return;
			}

			// calculate start timestamp as seconds
			var starttimestamp = function() {

				var timepartlist = /(\d{4})-(\d{1,2})-(\d{1,2})/.exec(sysstatgraph.statdata.starttime),
					timestamp = new Date(timepartlist[1],timepartlist[2] - 1,timepartlist[3]);

				// return timestamp as seconds
				return Math.floor(timestamp.getTime() / 1000);
			}();

			// render report start/end period at top of page
			(function() {

				function fetchdate(timestamp) {

					var dateparts = builddateparts(timestamp);
					return dateparts[0] + ' ' + dateparts[1];
				}

				var finishtimestamp = starttimestamp,
					timepointlist = sysstatgraph.statdata.timepointlist;

				// calculate the finish timestamp, working through the timepointlist
				for (var i = 0,j = timepointlist.length;i < j;i++) finishtimestamp += timepointlist[i];

				// insert report period message into DOM
				$('content').appendChild(buildparagraphtext('Report period: ' + fetchdate(starttimestamp + timepointlist[0]) + ' - ' + fetchdate(finishtimestamp)));
			})();

			// pass function reference builddateparts() to rendergraph class
			rendergraph.setbuilddatepartsfunc(builddateparts);

			// graph widths will be based on the current browser viewport width
			var graphwidth = getviewportwidth() - 115,
				graphheight = 210,
				timepointlist = sysstatgraph.statdata.timepointlist;

			// graph #1
			// - tasks created per second
			var graph1 = new rendergraph.instance(
				'Tasks created (per second)',graphwidth,graphheight,
				starttimestamp,timepointlist
			);

			graph1.addgraphline('Tasks per second',getvaluelist('taskspersecond'));
			graph1.render();

			// graph #2
			// - context switches per second
			var graph2 = new rendergraph.instance(
				'Context switches (per second)',graphwidth,graphheight,
				starttimestamp,timepointlist
			);

			graph2.addgraphline('Context switches per second',getvaluelist('cswitchpersecond'));
			graph2.render();

			// graph #3
			// - percentage of CPU utilisation that occurred while executing at the user level
			// - percentage of CPU utilisation that occurred while executing at the system level
			// - Percentage of time that the CPU or CPUs were idle during which the system had an outstanding disk I/O request
			var graph3 = new rendergraph.instance(
				'CPU utilisation',graphwidth,graphheight,
				starttimestamp,timepointlist
			);

			graph3.setpercent();
			graph3.addgraphline('User',getvaluelist('cpuuser'));
			graph3.addgraphline('System',getvaluelist('cpusystem'));
			graph3.addgraphline('IOwait',getvaluelist('cpuiowait'));
			graph3.render();

			// graph #4
			// - amount of used memory in kilobytes
			// - amount of used swap space in kilobytes
			var graph4 = new rendergraph.instance(
				'Memory usage (megabytes)',graphwidth,graphheight,
				starttimestamp,timepointlist
			);

			graph4.addgraphline('Memory usage',getvaluelist('mbmemoryused'));
			graph4.addgraphline('Swap usage',getvaluelist('mbswapused'));
			graph4.render();

			// graph #5
			// - number of tasks in the process list
			var graph5 = new rendergraph.instance(
				'Running/sleeping task count',graphwidth,graphheight,
				starttimestamp,timepointlist
			);

			graph5.addgraphline('Running tasks',getvaluelist('taskcountrun'));
			graph5.addgraphline('Sleeping tasks',getvaluelist('taskcountsleep'));
			graph5.render();

			// graph #6
			// - system load average for the last minute
			// - system load average for the past 5 minutes
			// - system load average for the past 15 minutes
			var graph6 = new rendergraph.instance(
				'System load averages',graphwidth,graphheight,
				starttimestamp,timepointlist
			);

			graph6.addgraphline('Last minute',getvaluelist('loadavg1'));
			graph6.addgraphline('Last 5 minutes',getvaluelist('loadavg5'));
			graph6.addgraphline('Last 15 minutes',getvaluelist('loadavg15'));
			graph6.render();

			// store network interface list and render network graphs
			var networkinterfacelist = sysstatgraph.statdata.networkinterfacelist;

			for (var i = 0,j = networkinterfacelist.length;i < j;i++) {
				var networkinterfacename = networkinterfacelist[i];

				// graph #N
				// - total number of packets received per second
				// - total number of packets transmitted per second
				var networkpacketgraph = new rendergraph.instance(
					'Network packets (per second) - [' + networkinterfacename + ']',graphwidth,graphheight,
					starttimestamp,timepointlist
				);

				networkpacketgraph.addgraphline('Packets received',getvaluelist('pcktsrecvpersecond-' + networkinterfacename));
				networkpacketgraph.addgraphline('Packets transmitted',getvaluelist('pcktstrnspersecond-' + networkinterfacename));
				networkpacketgraph.render();

				// graph #N+1
				// - total number of kilobytes received per second
				// - total number of kilobytes transmitted per second
				var networkdatagraph = new rendergraph.instance(
					'Network kilobytes (per second) - [' + networkinterfacename + ']',graphwidth,graphheight,
					starttimestamp,timepointlist
				);

				networkdatagraph.addgraphline('Kilobytes received',getvaluelist('kbrecvpersecond-' + networkinterfacename));
				networkdatagraph.addgraphline('Kilobytes transmitted',getvaluelist('kbtrnspersecond-' + networkinterfacename));
				networkdatagraph.render();
			}

			// setup table of contents flyout menu
			tocbox.init();
		},

		statdata: {}
	};
}();
