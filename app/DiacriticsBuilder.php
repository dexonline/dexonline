<?php
/*
 * Alin Ungureanu, 2013
 * alyn.cti@gmail.com
 */
require_once '../phplib/util.php';

require_once util_getRootPath() . 'phplib/AppLog.php';
require_once util_getRootPath() . 'phplib/MemoryManagement.php';

db_init();

$logFile = pref_getSectionPreference('app_log', 'diacritics_log');

/*
 * Builds examples and improves statistics for the diacritics mechanism
 */
class DiacriticsBuilder {


	protected $currOffset;
	protected $file;
	protected $fileEndOffset;

	protected static $diacritics;
	protected static $nonDiacritics;
	protected static $paddingNumber;
	protected static $paddingChar;
	private $globalCount;
	private $localCount;
	private $currentFolder;
	private $folderCount;
	/*
	 * initialises instance variables
	 */
	function __construct() {
		Applog::log("INSIDE " . __FILE__ . ' - ' . __CLASS__ . '::' . __FUNCTION__ . '() - ' . 'line '.__LINE__, 4);

		self::$diacritics = pref_getSectionPreference("diacritics", "diacritics");
		self::$nonDiacritics = pref_getSectionPreference("diacritics", "non_lower_diacritics");
		self::$paddingNumber = pref_getSectionPreference('diacritics', 'diacritics_padding_length');
		self::$paddingChar = pref_getSectionPreference('diacritics', 'padding_char');

		$this->globalCount = 0;
 	}


 	function showProcessingFileStatus($crawledPage) {
 		$start  = strpos($crawledPage->parsedTextPath, '/') + 1;
 		$length = strrpos($crawledPage->parsedTextPath, '/') - $start; 
 		$folder = substr($crawledPage->parsedTextPath, $start, $length);
	
		if ($folder != $this->currentFolder) {

			$this->currentFolder = $folder;
			$this->localCount = 0;
		}

		$this->localCount ++;
		$this->globalCount ++;

		Applog::log("Total(this run)::$this->globalCount, now processing $folder");
 	}

	/* 
	 * gets the next unprocessed file for diacritics
	 */
	function getNextFile() {
		Applog::log("INSIDE " . __FILE__ . ' - ' . __CLASS__ . '::' . __FUNCTION__ . '() - ' . 'line '.__LINE__, 4);

		while(1) {

			$crawledPage = CrawledPage::getNextDiacriticsFile();
			

			if ($crawledPage == null) {

				return null;
			}

			$this->showProcessingFileStatus($crawledPage);

			FilesUsedInDiacritics::save2Db($crawledPage->id);

			if (is_file($crawledPage->parsedTextPath) || $crawledPage->httpStatus < 400) {
				return $this->toLower(file_get_contents($crawledPage->parsedTextPath));
			}
		}

		return null;
	}


	function toLower($content) {
		Applog::log("INSIDE " . __FILE__ . ' - ' . __CLASS__ . '::' . __FUNCTION__ . '() - ' . 'line '.__LINE__, 4);
		return mb_strtolower($content);
	}
	/* verifica daca $ch este un caracter din lista
	 * [a,i,s,t,ă,â,î,ș,ț]
	 */
	static function isPossibleDiacritic($ch) {

		return strstr(self::$diacritics, $ch) || strstr(self::$nonDiacritics, $ch);
	}
	/* returneaza urmatorul index in fisier care contine
	 * un caracter din lista [a,i,s,t,ă,â,î,ș,ț]
	 */
	function getNextOffset() {
		Applog::log("INSIDE " . __FILE__ . ' - ' . __CLASS__ . '::' . __FUNCTION__ . '() - ' . 'line '.__LINE__, 4);
		while($this->currOffset <= $this->fileEndOffset) {
			//daca urmatorul offset e a,i,s,t sau ă,â,î,ș,ț
			if (self::isPossibleDiacritic(StringUtil::getCharAt($this->file, $this->currOffset))) {
				return $this->currOffset ++;
			}
			$this->currOffset ++;
		}
		return null;
	}

	static function isSeparator($ch) {
		Applog::log("INSIDE " . __FILE__ . ' - ' . __CLASS__ . '::' . __FUNCTION__ . '() - ' . 'line '.__LINE__, 4);
		return !(ctype_lower($ch) || strstr(self::$diacritics, $ch) || $ch == '-');
	}
	/*
	 * in the word arhivare, the 'i' padding is *arh  i  vare
	 */
	function leftAndRightPadding($offset) {
		Applog::log("INSIDE " . __FILE__ . ' - ' . __CLASS__ . '::' . __FUNCTION__ . '() - ' . 'line '.__LINE__, 4);
		$before = '';
		$middle = StringUtil::getCharAt($this->file, $offset);
		$after = '';
		$infOffset = $offset - 1;
		$supOffset = $offset + 1;
		$infPadding = false;
		$supPadding = false;
		

		for ($i = 0; $i < self::$paddingNumber; $i++) {
			
			if ($infOffset < 0) {
				//$before = self::$paddingChar . $before;
				$before = $before . self::$paddingChar;
			}
			else {
				if (!$infPadding) {
					$infCh = StringUtil::getCharAt($this->file, $infOffset);
					$infPadding = self::isSeparator($infCh);
				}
				if ($infPadding) {
					//$before = self::$paddingChar . $before;
					$before = $before . self::$paddingChar;
				}
				else {
					//$before = $infCh . $before;
					$before = $before . $infCh;
					$infOffset --;
				}
			}	

			if ($supOffset > $this->fileEndOffset) {
				$after = $after . self::$paddingChar;
			}
			else {
				if (!$supPadding) {
					$supCh = StringUtil::getCharAt($this->file, $supOffset);
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
		}

		Diacritics::save2Db($before, $middle, $after);
	}


	function processFile($file) {
		Applog::log("INSIDE " . __FILE__ . ' - ' . __CLASS__ . '::' . __FUNCTION__ . '() - ' . 'line '.__LINE__, 4);

		$this->file = $file;
		$this->currOffset = 0;
		$this->fileEndOffset = mb_strlen($file) - 1;

		while(($offset = $this->getNextOffset()) !== null) {

			$this->leftAndRightPadding($offset);
		}
	}

	function start() {
		Applog::log("INSIDE " . __FILE__ . ' - ' . __CLASS__ . '::' . __FUNCTION__ . '() - ' . 'line '.__LINE__, 4);
		while(($file = $this->getNextFile()) != null) {
			
			$this->processFile($file);
			MemoryManagement::clean();
		}

		Applog::log("Finished");
	}
}

if (strstr( $_SERVER['SCRIPT_NAME'], 'DiacriticsBuilder.php')) {

	$obj = new DiacriticsBuilder();
	$obj->start();
}

?>