<?php
// buildjsonstructure.php



class buildjsonstructure {

	private $networkinterfacelist = array();
	private $timepointlist = array();
	private $stattypelist = array();



	public function __construct(array $inputnetworkinterfacelist,array $inputtimepointlist,array $inputstattypelist) {

		$this->networkinterfacelist = $inputnetworkinterfacelist;
		$this->timepointlist = $inputtimepointlist;
		$this->stattypelist = $inputstattypelist;
	}

	public function render() {

		$json = '{';

		// build time point list
		$lasttimestamp = $this->getstartofdaytimestamp($this->timepointlist[0]);

		// note we put the date in a YYYY-MM-DD and generate a timestamp on the client side to avoid timezone issues
		$json .=
			'starttime: \'' . date('Y-n-j',$lasttimestamp) . '\',' .
			'networkinterfacelist: [\'' . implode('\',\'',$this->networkinterfacelist) . '\'],' .
			'timepointlist: [';

		$first = TRUE;
		foreach ($this->timepointlist as $value) {
			$json .= (($first) ? '' : ',') . ($value - $lasttimestamp);
			$first = FALSE;
			$lasttimestamp = $value;
		}

		$json .= '],valuelist: {';

		// build stat data lists
		$firsttype = TRUE;
		foreach ($this->stattypelist as $type => $valuelist) {
			if (!$firsttype) $json .= ',';
			$json .= '\'' . $type . '\': [';

			$firstvalue = TRUE;
			foreach ($valuelist as $value) {
				$json .= (($firstvalue) ? '' : ',') . $value;
				$firstvalue = FALSE;
			}

			$json .= ']';
			$firsttype = FALSE;
		}

		$json .= '}}';

		return $json;
	}

	// getstartofdaytimestamp() takes a timestamp and returns a timestamp for the start of the day
	private function getstartofdaytimestamp($inputtimestamp) {

		return mktime(
			0,0,0,
			date('n',$inputtimestamp),
			date('j',$inputtimestamp),
			date('Y',$inputtimestamp)
		);
	}
}
