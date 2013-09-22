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
	private $hiddenText;
	private $selectCount;


	protected $currOffset;
	protected $text;
	protected $fileEndOffset;

	protected static $diacritics;
	protected static $nonLowerDiacritics;
	protected static $nonUpperDiacritics;
	protected static $paddingNumber;
	protected static $paddingChar;
	/*
	 * initialises instance variables
	 */
	function __construct() {
		crawlerLog("INSIDE " . __FILE__ . ' - ' . __CLASS__ . '::' . __FUNCTION__ . '() - ' . 'line '.__LINE__ );

		self::$diacritics = pref_getSectionPreference("crawler", "diacritics");
		self::$nonLowerDiacritics = pref_getSectionPreference("crawler", "non_lower_diacritics");
		self::$nonUpperDiacritics = pref_getSectionPreference("crawler", "non_upper_diacritics");
		self::$paddingNumber = pref_getSectionPreference('crawler', 'diacritics_padding_length');
		self::$paddingChar = pref_getSectionPreference('crawler', 'padding_char');
		$this->selectCount = 0;
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
		return !(ctype_alpha($ch) || $ch == '-');
	}


	function processText($text) {
		crawlerLog("INSIDE " . __FILE__ . ' - ' . __CLASS__ . '::' . __FUNCTION__ . '() - ' . 'line '.__LINE__ );


		$this->currOffset = 0;
		$this->lastOffset = 0;

		$this->resultText = '';
		$this->hiddenText = '';
		$this->text = $text;

		$this->textEndOffset = mb_strlen($text) - 1;
		$offset = 0;
		while(($offset = $this->getNextOffset()) != null) {

			$this->leftAndRightPadding($offset);
		}
		//copiem de la ultimul posibil diacritic pana la final
		$this->resultText .= mb_substr($this->text, $this->lastOffset, $this->textEndOffset - $this->lastOffset + 1);
		$this->hiddenText .= mb_substr($this->text, $this->lastOffset, $this->textEndOffset - $this->lastOffset + 1);
	}


	public function fix($text) {
		crawlerLog("INSIDE " . __FILE__ . ' - ' . __CLASS__ . '::' . __FUNCTION__ . '() - ' . 'line '.__LINE__ );

		$this->processText($text);
		return $this->resultText;
	}

	static function toLower($content) {
		crawlerLog("INSIDE " . __FILE__ . ' - ' . __CLASS__ . '::' . __FUNCTION__ . '() - ' . 'line '.__LINE__ );
		return mb_strtolower($content);
	}


	static function isPossibleDiacritic($ch) {
		crawlerLog("INSIDE " . __FILE__ . ' - ' . __CLASS__ . '::' . __FUNCTION__ . '() - ' . 'line '.__LINE__ );
		return strstr(self::$nonLowerDiacritics, $ch) ||
			strstr(self::$nonUpperDiacritics, $ch);
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
			else {
				
				$infCh = StringUtil::getCharAt($this->text, $infOffset);
				$infPadding = self::isSeparator($infCh);
			}
			
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
			else {
				$supCh = StringUtil::getCharAt($this->text, $supOffset);
				$supPadding = self::isSeparator($supCh);
			}
			if ($supPadding) {
				$after = $after . self::$paddingChar;
			}
			else {
				$after = $after . $supCh;
				$supOffset ++;
			}
		}

		crawlerLog("IN TEXT " . $before .'|' . $middle . '|' . $after);

		$tableObj = Diacritics::entryExists($before, $middle, $after);
		if ($tableObj != null) {
			crawlerLog("Entry Exists");
			$ch = $this->getAllCharForms($tableObj, $middle);

			$this->resultText .= mb_substr($this->text, $this->lastOffset, $offset - $this->lastOffset);

			$this->hiddenText .= mb_substr($this->text, $this->lastOffset, $offset - $this->lastOffset);

			$this->resultText .= $ch;

			if (mb_strlen($ch) == 1) {
				$this->hiddenText .= $ch;
			}
			else {
				$this->hiddenText .= "@@".($this->selectCount - 1)."@@";
			}


		}
		else {

			$this->resultText .= mb_substr($this->text, $this->lastOffset, $offset - $this->lastOffset + 1);

			$this->hiddenText .= mb_substr($this->text, $this->lastOffset, $offset - $this->lastOffset + 1);			
		}

		$this->lastOffset = $this->currOffset;
	}

	public function getAllCharForms($tableObj, $middle) {
		crawlerLog("INSIDE " . __FILE__ . ' - ' . __CLASS__ . '::' . __FUNCTION__ . '() - ' . 'line '.__LINE__ );
		$ch = $tableObj->middle;
		//$ch = self::$a['circumflexForm'];

		$sortedSet = self::getCharOccurenceArray($tableObj);

		$charArray = $this->getCharArray($ch);

		crawlerLog("ARRAY ". print_r($sortedSet, true));

		//$key = key($sortedSet);//array_search($charArray[0], $charArray);
		//crawlerLog("WTF " . $key);
		//$ch = $charArray[$key];

		if (self::hasMoreVariants($sortedSet)) {
			$ch = $this->dropDownSelect($sortedSet, $charArray, $middle);
		}
		else {
			$ch  = self::getToUpperOrToLower($charArray[key($sortedSet)], $middle);
		}
		return $ch;
	}

	private function dropDownSelect($forms, $charArray, $middle) {

		$buffer = '<select name="'.$this->selectCount++.'">';

		foreach($forms as $form => $value) {

			if ($value > 0) {
				$ch = self::getToUpperOrToLower($charArray[$form], $middle);
				$buffer .= "<option value=\"".$ch."\">".$ch."</option>";
			}
		}

		$buffer .= '</select>';
		return $buffer;
	}

	static function hasMoreVariants($forms) {

		$i = 0;

		foreach($forms as $form => $value) {

			if ($value > 0) {

				$i++;
			}
		}

		return ($i > 1);
	}

	static function getToUpperOrToLower($val, $middle) {

		if ($middle == mb_strtolower($middle)) {
			return $val;
		}
		else {
			return mb_strtoupper($val);
		}
	}

	private function getCharArray($ch) {

		return self::$$ch;
	}

	private static function getCharOccurenceArray($tableObj) {

		$array = array(
			'defaultForm' => $tableObj->defaultForm,
			'curvedForm' => $tableObj->curvedForm,
			'circumflexForm' => $tableObj->circumflexForm
			);
		//sort array desc
		arsort($array);
		return $array;
	}

	function getHiddenText() {

		return $this->hiddenText;
	}

	function replaceDiacritics() {

		if (isset($_POST['hiddenText'])) {
			if ($_POST['hiddenText'] == '')
			return '';
			else {
				$search = array();
				$replace = array();

				$buffer = $_POST['hiddenText'];
				foreach($_POST as $key => $value) {

					if (is_numeric($key)) {
						$search[] = '/@@'.$key.'@@/i';
						$replace[] = $value;
					}
				}
				return preg_replace($search, $replace, $buffer);
			}
		}
		else {
			return '';
		}
	}

}


if (strstr( $_SERVER['SCRIPT_NAME'], 'diacritice.php')) {



	SmartyWrap::assign('page_title', 'Corector diacritice');

	$obj = new DiacriticsFixer();

	if (isset($_POST['text']) && $_POST['text'] != '') {

		SmartyWrap::assign('textarea', '<div id="text_input">'.$obj->fix($_POST['text']).'</div>');
		SmartyWrap::assign('hiddenText', '<input type="hidden" name="hiddenText" value="'.$obj->getHiddenText().'">');
	}
	else {

		SmartyWrap::assign('textarea', '<textarea name="text" id="text_input" placeholder="introduceți textul aici">'.$obj->replaceDiacritics().'</textarea>');
		SmartyWrap::assign('hiddenText', '<input type="hidden" name="hiddenText" value="">');
	}


	SmartyWrap::displayPageWithSkin('../diacritics_fix/diacritics_fix.ihtml');
}

?>