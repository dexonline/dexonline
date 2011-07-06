<?php
// importstatfiledata.php



class importstatfiledata {

	const filesection_taskspersecond = 'taskspersecond';
	const filesection_cpuutilisation = 'cpuutilisation';
	const filesection_memoryusage = 'memoryusage';
	const filesection_swapusage = 'swapusage';
	const filesection_load = 'load';
	const filesection_network = 'network';

	// $filesectionkeylist stores key header fields we are looking for to know what part of the sar file we are currently reading
	private $filesectionkeylist = array(
		'proc/s' => self::filesection_taskspersecond,
		'CPU' => self::filesection_cpuutilisation,
		'kbmemfree' => self::filesection_memoryusage,
		'kbswpfree' => self::filesection_swapusage,
		'runq-sz' => self::filesection_load,
		'IFACE' => self::filesection_network
	);

	// $datalinepartslookuplist stores the number of data fields we expect per each row type
	private $datalinepartslookuplist = array(
		self::filesection_taskspersecond => 3,
		self::filesection_cpuutilisation => 11,
		self::filesection_memoryusage => 8,
		self::filesection_swapusage => 6,
		self::filesection_load => 6,
		self::filesection_network => 9
	);

	private $networkinterfacelist = array();
	private $validnetworkinterfacelist = array();
	private $timepointlist = array();
	private $stattypelist = array();
	private $currentfilesection = '';
	private $twelvehourtimeformat = FALSE;



	public function __construct() {

		$this->networkinterfacelist = unserialize(NETWORKINTERFACELIST);
	}

	public function importfile($inputfilename) {

		$this->currentfilesection = '';
		$fp = fopen($inputfilename,'r');

		// first line will contain the date of the report - must exist
		if (!($filedatetimestamp = (!feof($fp)) ? $this->getfiledatetimestamp(fgets($fp)) : FALSE)) {
			// cant locate file date - exit
			fclose($fp);
			return;
		}

		$firstdataline = TRUE;
		while (!feof($fp)) {
			$linetext = trim(fgets($fp));
			if ($linetext == '') {
				// empty line - next line
				$this->currentfilesection = '';
				continue;
			}

			if ($firstdataline) {
				// determine if times are in 12/24 hour format
				$this->twelvehourtimeformat = preg_match('/^\d{2}:\d{2}:\d{2} (AM|PM) /',$linetext);
				$firstdataline = FALSE;
			}

			if ($this->twelvehourtimeformat) {
				// remove space between AM/PM in 12hour time format
				$linetext = str_replace(' AM ','AM ',$linetext);
				$linetext = str_replace(' PM ','PM ',$linetext);
			}

			// split up line into parts
			$lineparts = preg_split('/ +/',$linetext);

			if ((isset($lineparts[1])) && ($this->checkfilesection($lineparts[1]))) {
				// found next data section in file - next line
				continue;
			}

			// get report time for the current line as a unix timestamp
			// if FALSE, then not a valid line we want to process
			if (!($linetimestamp = $this->getlinetimestamp($lineparts[0]))) {
				// invalid time - next line
				continue;
			}

			// offset line timestamp by the file date
			$linetimestamp += $filedatetimestamp;

			// record the data line values, validating the data line has the right number of data parts
			$this->recorddataline($linetimestamp,$lineparts);
		}

		fclose($fp);
	}

	public function getvalidnetworkinterfacelist() {

		return array_keys($this->validnetworkinterfacelist);
	}

	public function gettimepointlist() {

		// sort time point list in ascending order
		$sortedtimepointlist = array_keys($this->timepointlist);
		sort($sortedtimepointlist,SORT_NUMERIC);

		return $sortedtimepointlist;
	}

	public function getstattypelist() {

		$sortedstattypelist = array();

		foreach (array_keys($this->stattypelist) as $typename) {
			// sort all time based data for the stat type
			$sortdatalist = $this->stattypelist[$typename];
			ksort($sortdatalist,SORT_NUMERIC);

			// throw result into $sortedstattypelist
			$sortedstattypelist[$typename] = $sortdatalist;
		}

		// return the time sorted $sortedstattypelist array
		return $sortedstattypelist;
	}

	private function getfiledatetimestamp($inputfirstline) {

		// convert YYYY-MM-DD to a unix timestamp
		return (preg_match('/(?P<year>\d{4})-(?P<month>\d{2})-(?P<day>\d{2})/',rtrim($inputfirstline),$match))
			? mktime(0,0,0,intval($match['month']),intval($match['day']),intval($match['year']))
			: FALSE;
	}

	private function checkfilesection($inputvalue) {

		if (!isset($this->filesectionkeylist[$inputvalue])) {
			// key not found - ignore
			return FALSE;
		}

		// store current file section
		$this->currentfilesection = $this->filesectionkeylist[$inputvalue];
		return TRUE;
	}

	private function getlinetimestamp($inputtime) {

		if (!$this->twelvehourtimeformat) {
			// try hh:mm:ss formatted time
			if (preg_match('/^(?P<hour>\d{2}):(?P<minute>\d{2}):(?P<second>\d{2})$/',$inputtime,$matches)) {
				// found match - convert to timestamp
				return (intval($matches['hour']) * 3600) + (intval($matches['minute']) * 60) + intval($matches['second']);
			}
		}

		// try hh:mm:ss AM/PM formatted time
		if (preg_match('/^(?P<hour>\d{2}):(?P<minute>\d{2}):(?P<second>\d{2})(?P<period>AM|PM)$/',$inputtime,$matches)) {
			// handle AM/PM
			$hour = intval($matches['hour']);
			if ($hour == 12) $hour = 0;
			if ($matches['period'] == 'PM') $hour += 12;;

			// found match - convert to timestamp
			return ($hour * 3600) + (intval($matches['minute']) * 60) + intval($matches['second']);
		}

		// not a valid match
		return FALSE;
	}

	private function recorddataline($inputtimestamp,array $inputlinepartlist) {

		// validate the data line has the right number of data parts for the file section
		if (
			(!isset($this->datalinepartslookuplist[$this->currentfilesection])) ||
			($this->datalinepartslookuplist[$this->currentfilesection] != sizeof($inputlinepartlist))
		) {
			// invalid number of line data parts for current file section - reject the data line
			return;
		}

		// tasks created/context switches (per second)
		if ($this->currentfilesection == self::filesection_taskspersecond) {
			$this->recordstat($inputtimestamp,'taskspersecond',floatval($inputlinepartlist[1]));
			$this->recordstat($inputtimestamp,'cswitchpersecond',floatval($inputlinepartlist[2]));
			return;
		}

		// CPU utilisation (%)
		if ($this->currentfilesection == self::filesection_cpuutilisation) {
			// we only want the 'all' CPU report lines
			if ($inputlinepartlist[1] != 'all') return;

			$this->recordstat($inputtimestamp,'cpuuser',floatval($inputlinepartlist[2]));
			$this->recordstat($inputtimestamp,'cpusystem',floatval($inputlinepartlist[4]));
			$this->recordstat($inputtimestamp,'cpuiowait',floatval($inputlinepartlist[5]));

			return;
		}

		// memory usage kilobytes (convert from kilobytes to megabytes)
		if ($this->currentfilesection == self::filesection_memoryusage) {
			$this->recordstat($inputtimestamp,'mbmemoryused',$this->twodecimalplaces(floatval($inputlinepartlist[2] / 1024)));
			return;
		}

		// swap usage kilobytes (convert from kilobytes to megabytes)
		if ($this->currentfilesection == self::filesection_swapusage) {
			$this->recordstat($inputtimestamp,'mbswapused',$this->twodecimalplaces(floatval($inputlinepartlist[2] / 1024)));
			return;
		}

		// system load averages
		if ($this->currentfilesection == self::filesection_load) {
			$runtaskcount = intval($inputlinepartlist[1]);
			$this->recordstat($inputtimestamp,'taskcountrun',$runtaskcount);
			$this->recordstat($inputtimestamp,'taskcountsleep',intval($inputlinepartlist[2]) - $runtaskcount);

			$this->recordstat($inputtimestamp,'loadavg1',floatval($inputlinepartlist[3]));
			$this->recordstat($inputtimestamp,'loadavg5',floatval($inputlinepartlist[4]));
			$this->recordstat($inputtimestamp,'loadavg15',floatval($inputlinepartlist[5]));

			return;
		}

		// network traffic (per second)
		if ($this->currentfilesection == self::filesection_network) {
			// we only want network adapters that are in our $this->networkinterfacelist
			$adaptername = $inputlinepartlist[1];
			if (!in_array($adaptername,$this->networkinterfacelist)) return;

			// save as valid network adapter
			$this->validnetworkinterfacelist[$adaptername] = TRUE;

			// packets sent/received
			$this->recordstat($inputtimestamp,'pcktsrecvpersecond-' . $adaptername,floatval($inputlinepartlist[2]));
			$this->recordstat($inputtimestamp,'pcktstrnspersecond-' . $adaptername,floatval($inputlinepartlist[3]));

			// KB's sent/received
			$this->recordstat($inputtimestamp,'kbrecvpersecond-' . $adaptername,floor($inputlinepartlist[4]));
			$this->recordstat($inputtimestamp,'kbtrnspersecond-' . $adaptername,floor($inputlinepartlist[5]));

			return;
		}
	}

	private function recordstat($inputtimestamp,$inputstattype,$inputvalue) {

		$this->timepointlist[$inputtimestamp] = TRUE;
		$this->stattypelist[$inputstattype][$inputtimestamp] = $inputvalue;
	}

	private function twodecimalplaces($inputvalue) {

		if (preg_match('/^\d+\.\d{1,2}/',$inputvalue,$matches)) {
			// return value rounded to two decimal places
			return $matches[0];
		}

		// no match, just return original value
		return $inputvalue;
	}
}
