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


	private $currOffset;
	protected $file;
	private $fileEndOffset;

	private static $diacritics;
	private static $nonDiacritics;
	private static $paddingNumber;
	/*
	 * initialises instance variables
	 */
	function __construct() {
		crawlerLog("INSIDE " . __FILE__ . ' - ' . __CLASS__ . '::' . __FUNCTION__ . '() - ' . 'line '.__LINE__ );

		self::$diacritics = pref_getSectionPreference("crawler", "diacritics");
		self::$nonDiacritics = pref_getSectionPreference("crawler", "non_diacritics");
		self::$paddingNumber = pref_getSectionPreference('crawler', 'diacritics_padding_length');
 	}

	/* 
	 * gets the next unprocessed file for diacritics
	 */
	function getNextFile() {
		crawlerLog("INSIDE " . __FILE__ . ' - ' . __CLASS__ . '::' . __FUNCTION__ . '() - ' . 'line '.__LINE__ );

		$crawledPage = CrawledPage::getNextDiacriticsFile();
		
		if ($crawledPage == null) {

			return null;
		}
		FilesUsedInDiacritics::save2Db($crawledPage->id);

		return $this->toLower(file_get_contents($crawledPage->parsedTextPath));
	}


	function toLower($content) {

		$content = str_replace(array('Ă','Â','Î','Ș','Ț'), array('ă', 'â', 'î', 'ș', 'ț'), $content);

		return strtolower($content);
	}


	function getNextOffset() {
		crawlerLog("INSIDE " . __FILE__ . ' - ' . __CLASS__ . '::' . __FUNCTION__ . '() - ' . 'line '.__LINE__ );
		

		while($this->currOffset <= $this->fileEndOffset) {
			//daca urmatorul offset e a,i,s,t sau ă,â,î,ș,ț
			$ch = substr($this->file, $this->currOffset, 1);
			if (strstr(self::$nonDiacritics, $ch)) {
				
				return $this->currOffset ++;
			}
			else {

				$ch = substr($this->file, $this->currOffset, 2);

				if (strstr(self::$diacritics, $ch)) {
				
					$this->currOffset += 2;

					return $this->currOffset - 2;
				}
			}



			//trecem la urmatorul caracter
			$this->currOffset ++;
		}

		return null;
	}

	function isSeparator($ch) {
		crawlerLog("INSIDE " . __FILE__ . ' - ' . __CLASS__ . '::' . __FUNCTION__ . '() - ' . 'line '.__LINE__ );
		
		return !(ctype_lower($ch) || strstr(self::$diacritics, $ch) || $ch == '-');
	}

	function pointOfInterestPadding($offset) {
		crawlerLog("INSIDE " . __FILE__ . ' - ' . __CLASS__ . '::' . __FUNCTION__ . '() - ' . 'line '.__LINE__ );
		
		$before = '';
		if (strstr(self::$diacritics, substr($this->file, $offset, 2)))
			$middle = substr($this->file, $offset, 2);
		else
			$middle = substr($this->file, $offset, 1);

		$after = 'test';

		$infPadding = false;
		$supPadding = false;


		//echo "OFFSET ".$offset. '  char '.substr($this->file, $offset, 1).PHP_EOL; 


		$infOffset = $offset - 2;

		$supOffset = $offset + strlen($middle);

		$firstLetter = false;

		for ($i = 0; $i < self::$paddingNumber; $i++) {

			if ($infOffset < 0) {

				if ($infOffset + 1 == 0) {

					$firstLetter = true;
				}
				else {

					$infPadding = true;
				}
			}



			if ($infPadding) {

				$before = '*' . $before;
			}
			else {

				if ($firstLetter) {

					$infCh = substr($this->file, $infOffset, 1);

					if ($this->isSeparator($infCh)) {

						$infPadding = true;
						$before = '*' . $before;
					}
					else {

						$before = $infCh . $before;
					}

				}

				else {

					$infCh = substr($this->file, $infOffset, 2);

					if (!strstr(self::$diacritics, $infCh)) {
						$infOffset ++;
						$infCh = substr($this->file, $infOffset, 1);
					}

					if ($this->isSeparator($infCh)) {

						$infPadding = true;
						$before = '*' . $before;
					}
					else {
						$before = $infCh . $before;

						$infOffset -= 2;
					}
				}
			}
		}
/*
			$supCh = substr($this->file, $superiorOffset, 2);

			if (!strstr(self::$diacritics, $supCh)) {

				$supCh = substr($this->file, $superiorOffset, 1);
			}

			



			if ($inferiorOffset < 0) {
				
				$before = '*' . $before;
			}
			else {
				
				$ch = substr($this->file, $inferiorOffset, 1);
			
				if (!$inferiorSeparator) {
					
					$inferiorSeparator = $this->isSeparator($ch);
				}

				$before = ($inferiorSeparator ? '*' : $ch) . $before;
			}

			if ($superiorOffset > $this->fileEndOffset) {
				
				$after .= '*';
			}
			else {

			
				$ch = substr($this->file, $superiorOffset, 1);
			
				if (!$superiorSeparator) {
					
					$superiorSeparator = $this->isSeparator($ch);
				}

				$after .= ($superiorSeparator ? '*' : $ch);
			}

		}

		//echo "RESULT   $before|$middle|$after".PHP_EOL;
*/

		Diacritics::save2Db($before, $middle, $after);

	}


	function processFile($file) {
		crawlerLog("INSIDE " . __FILE__ . ' - ' . __CLASS__ . '::' . __FUNCTION__ . '() - ' . 'line '.__LINE__ );

		$this->file = $file;
		$this->currOffset = 0;
		$this->fileEndOffset = strlen($file) - 1;

		while(($offset = $this->getNextOffset()) != null) {

			$this->pointOfInterestPadding($offset);
		}
	}

	function start() {
		crawlerLog("INSIDE " . __FILE__ . ' - ' . __CLASS__ . '::' . __FUNCTION__ . '() - ' . 'line '.__LINE__ );

		while(($file = $this->getNextFile()) != null) {


			$this->processFile($file);
			MemoryManagement::clean();
		}
	}
}

if (strstr( $_SERVER['SCRIPT_NAME'], 'DiacriticsBuilder.php')) {

	$obj = new DiacriticsBuilder();
	$obj->start();
}

?>