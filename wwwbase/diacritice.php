<?php

require_once '../phplib/util.php';
require_once '../phplib/serverPreferences.php';
require_once '../phplib/db.php';
require_once '../phplib/idiorm/idiorm.php';
require_once '../phplib/idiorm/paris.php';

require_once 'Crawler/AppLog.php';
require_once 'Crawler/MemoryManagement.php';


db_init();

$logFile = pref_getSectionPreference('crawler', 'diacritics_log');


class DiacriticsFixer {


	private static $a = array('defaultForm' => 'a', 'curvedForm' => 'ă', 'circumflexForm' => 'â');
	private static $i = array('defaultForm' => 'i', 'curvedForm' => null, 'circumflexForm' => 'î');
	private static $s = array('defaultForm' => 's', 'curvedForm' => 'ș', 'circumflexForm' => null);
	private static $t = array('defaultForm' => 't', 'curvedForm' => 'ț', 'circumflexForm' => null);

	private $resultText;
	private $lastOffset;


	protected $currOffset;
	protected $text;
	protected $fileEndOffset;

	protected static $diacritics;
	protected static $nonDiacritics;
	protected static $paddingNumber;
	protected static $paddingChar;
	/*
	 * initialises instance variables
	 */
	function __construct() {
		crawlerLog("INSIDE " . __FILE__ . ' - ' . __CLASS__ . '::' . __FUNCTION__ . '() - ' . 'line '.__LINE__ );

		self::$diacritics = pref_getSectionPreference("crawler", "diacritics");
		self::$nonDiacritics = pref_getSectionPreference("crawler", "non_diacritics");
		self::$paddingNumber = pref_getSectionPreference('crawler', 'diacritics_padding_length');
		self::$paddingChar = pref_getSectionPreference('crawler', 'padding_char');
 	}

	/* returneaza urmatorul index in fisier care contine
	 * un caracter din lista [a,i,s,t]
	 */
	function getNextOffset() {
		crawlerLog("INSIDE " . __FILE__ . ' - ' . __CLASS__ . '::' . __FUNCTION__ . '() - ' . 'line '.__LINE__ );
		while($this->currOffset <= $this->textEndOffset) {
			//daca urmatorul offset e a,i,s,t sau ă,â,î,ș,ț
			if (self::isPossibleDiacritic(StringUtil::getCharAt($this->text, $this->currOffset))) {
				return $this->currOffset ++;
			}
			$this->currOffset ++;
		}
		return null;
	}

	static function isSeparator($ch) {
		crawlerLog("INSIDE " . __FILE__ . ' - ' . __CLASS__ . '::' . __FUNCTION__ . '() - ' . 'line '.__LINE__ );
		return !(ctype_lower($ch) || $ch == '-');
	}


	function processText($text) {
		crawlerLog("INSIDE " . __FILE__ . ' - ' . __CLASS__ . '::' . __FUNCTION__ . '() - ' . 'line '.__LINE__ );


		$this->currOffset = 0;
		$this->lastOffset = 0;

		$this->resultText = '';
		$this->text = $text;

		$this->textEndOffset = mb_strlen($text) - 1;
		$offset = 0;
		while(($offset = $this->getNextOffset()) != null) {

			$this->leftAndRightPadding($offset);
		}
		//copiem de la ultimul posibil diacritic pana la final
		$this->resultText .= mb_substr($this->text, $this->lastOffset, $this->textEndOffset - $this->lastOffset + 1);
	}


	public function fix($text) {
		crawlerLog("INSIDE " . __FILE__ . ' - ' . __CLASS__ . '::' . __FUNCTION__ . '() - ' . 'line '.__LINE__ );

		$this->processText($text);
		return $this->resultText;
	}

	function toLower($content) {
		crawlerLog("INSIDE " . __FILE__ . ' - ' . __CLASS__ . '::' . __FUNCTION__ . '() - ' . 'line '.__LINE__ );
		return mb_strtolower($content);
	}


	static function isPossibleDiacritic($ch) {
		crawlerLog("INSIDE " . __FILE__ . ' - ' . __CLASS__ . '::' . __FUNCTION__ . '() - ' . 'line '.__LINE__ );
		return strstr(self::$nonDiacritics, $ch);
	}


	function leftAndRightPadding($offset) {
		crawlerLog("INSIDE " . __FILE__ . ' - ' . __CLASS__ . '::' . __FUNCTION__ . '() - ' . 'line '.__LINE__ );
		$before = '';
		$middle = StringUtil::getCharAt($this->text, $offset);
		$after = '';
		$infOffset = $offset - 1;
		$supOffset = $offset + 1;
		$infPadding = false;
		$supPadding = false;
		

		for ($i = 0; $i < self::$paddingNumber; $i++) {
			
			if ($infOffset < 0) {
				$infPadding = true;
			}

			$infCh = StringUtil::getCharAt($this->text, $infOffset);
			$infPadding = self::isSeparator($infCh);

			if ($infPadding) {
				$before = self::$paddingChar . $before;
			}
			else {
				$before = $infCh . $before;
				$infOffset --;
			}

			if ($supOffset > $this->textEndOffset) {
				$supPadding = true;
			}

			$supCh = StringUtil::getCharAt($this->text, $supOffset);
			$supPadding = self::isSeparator($supCh);

			if ($supPadding) {
				$after = $after . self::$paddingChar;
			}
			else {
				$after = $after . $supCh;
				$supOffset ++;
			}
		}

		crawlerLog("FOUND " . $before . '|' . $middle . '|' . $after);

		$tableObj = Diacritics::entryExists($before, $middle, $after);
		if ($tableObj != null) {
			crawlerLog("Entry Exists");
			$ch = $this->getMostProbableChar($tableObj);

			$this->resultText .= mb_substr($this->text, $this->lastOffset, $offset - $this->lastOffset);

			$this->resultText .= $ch;
		}
		else {

			$this->resultText .= mb_substr($this->text, $this->lastOffset, $offset - $this->lastOffset + 1);			
		}

		$this->lastOffset = $this->currOffset;
	}

	public function getMostProbableChar($tableObj) {
		crawlerLog("INSIDE " . __FILE__ . ' - ' . __CLASS__ . '::' . __FUNCTION__ . '() - ' . 'line '.__LINE__ );
		$ch = $tableObj->middle;
		//$ch = self::$a['circumflexForm'];

		$sortedSet = self::getCharProbabilityArray($tableObj);

		$charArray = $this->getCharArray($ch);

		crawlerLog("ARRAY ". print_r($sortedSet, true));

		$key = key($sortedSet);//array_search($charArray[0], $charArray);
		crawlerLog("WTF " . $key);
		$ch = $charArray[$key];

		return $ch;
	}

	private function getCharArray($ch) {

		return self::$$ch;
	}

	private static function getCharProbabilityArray($tableObj) {

		$array = array(
			'defaultForm' => $tableObj->defaultForm,
			'curvedForm' => $tableObj->curvedForm,
			'circumflexForm' => $tableObj->circumflexForm
			);
		//sort array desc
		arsort($array);
		return $array;
	}


}


if (strstr( $_SERVER['SCRIPT_NAME'], 'diacritice.php')) {



	SmartyWrap::assign('page_title', 'Corector diacritice');


	if (isset($_POST['text']) && $_POST['text'] != '') {

		$obj = new DiacriticsFixer();
		SmartyWrap::assign('result', $obj->fix($_POST['text']));
	}
	else {

		SmartyWrap::assign('result', '');
	}


	SmartyWrap::displayPageWithSkin('../diacritics_fix/diacritics_fix.ihtml');
}

?>