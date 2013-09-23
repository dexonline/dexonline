<?php
/*
 * Alin Ungureanu, 2013
 * alyn.cti@gmail.com
 */
require_once '../../phplib/util.php';
require_once '../../phplib/serverPreferences.php';
require_once '../../phplib/db.php';
require_once '../../phplib/idiorm/idiorm.php';
require_once '../../phplib/idiorm/paris.php';

require_once 'AppLog.php';
require_once 'MemoryManagement.php';

db_init();

$logFile = pref_getSectionPreference('crawler', 'diacritics_log');

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
		crawlerLog("INSIDE " . __FILE__ . ' - ' . __CLASS__ . '::' . __FUNCTION__ . '() - ' . 'line '.__LINE__ );

		self::$diacritics = pref_getSectionPreference("crawler", "diacritics");
		self::$nonDiacritics = pref_getSectionPreference("crawler", "non_lower_diacritics");
		self::$paddingNumber = pref_getSectionPreference('crawler', 'diacritics_padding_length');
		self::$paddingChar = pref_getSectionPreference('crawler', 'padding_char');

		$this->globalCount = 0;
 	}


 	function showProcessingFileStatus($crawledPage) {
 		$start  = strpos($crawledPage->parsedTextPath, '/') + 1;
 		$length = strrpos($crawledPage->parsedTextPath, '/') - $start; 
 		$folder = substr($crawledPage->parsedTextPath, $start, $length);
	
		if ($folder != $this->currentFolder) {

			$this->currentFolder = $folder;
			$this->localCount = 0;
			$this->folderCount = iterator_count(new DirectoryIterator(substr($crawledPage->parsedTextPath,0,strrpos($crawledPage->parsedTextPath, '/'))));
		}

		$this->localCount ++;		
		$this->globalCount ++;

 		crawlerLog("Total(this run)::$this->globalCount, now processing $folder $this->localCount/".$this->folderCount);
 	}

	/* 
	 * gets the next unprocessed file for diacritics
	 */
	function getNextFile() {
		crawlerLog("INSIDE " . __FILE__ . ' - ' . __CLASS__ . '::' . __FUNCTION__ . '() - ' . 'line '.__LINE__ );

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
		crawlerLog("INSIDE " . __FILE__ . ' - ' . __CLASS__ . '::' . __FUNCTION__ . '() - ' . 'line '.__LINE__ );
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
		crawlerLog("INSIDE " . __FILE__ . ' - ' . __CLASS__ . '::' . __FUNCTION__ . '() - ' . 'line '.__LINE__ );
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
		crawlerLog("INSIDE " . __FILE__ . ' - ' . __CLASS__ . '::' . __FUNCTION__ . '() - ' . 'line '.__LINE__ );
		return !(ctype_lower($ch) || strstr(self::$diacritics, $ch) || $ch == '-');
	}
	/*
	 * in the word arhivare, the 'i' padding is *arh  i  vare
	 */
	function leftAndRightPadding($offset) {
		crawlerLog("INSIDE " . __FILE__ . ' - ' . __CLASS__ . '::' . __FUNCTION__ . '() - ' . 'line '.__LINE__ );
		$before = '';
		$middle = StringUtil::getCharAt($this->file, $offset);
		$after = '';
		$infOffset = $offset - 1;
		$supOffset = $offset + 1;
		$infPadding = false;
		$supPadding = false;
		

		for ($i = 0; $i < self::$paddingNumber; $i++) {
			
			if ($infOffset < 0) {
				$before = self::$paddingChar . $before;
			}
			else {
				if (!$infPadding) {
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
			}
			
			if ($supOffset > $this->textEndOffset) {
				$after = $after . self::$paddingChar;
			}
			else {
				if (!$supPadding) {
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
		}

		Diacritics::save2Db($before, $middle, $after);
	}


	function processFile($file) {
		crawlerLog("INSIDE " . __FILE__ . ' - ' . __CLASS__ . '::' . __FUNCTION__ . '() - ' . 'line '.__LINE__ );

		$this->file = $file;
		$this->currOffset = 0;
		$this->fileEndOffset = mb_strlen($file) - 1;

		while(($offset = $this->getNextOffset()) !== null) {
			
			$this->leftAndRightPadding($offset);
		}
	}

	function start() {
		crawlerLog("INSIDE " . __FILE__ . ' - ' . __CLASS__ . '::' . __FUNCTION__ . '() - ' . 'line '.__LINE__ );
		while(($file = $this->getNextFile()) != null) {
			
			$this->processFile($file);
			MemoryManagement::clean();
		}

		crawlerLog("Finished");
	}
}

if (strstr( $_SERVER['SCRIPT_NAME'], 'DiacriticsBuilder.php')) {

	$obj = new DiacriticsBuilder();
	$obj->start();
}

?>