<?php


class Diacritics  extends BaseObject implements DatedObject {
	
	public static $_table = 'Diacritics';

	
	private static function replaceDiacritic($ch) {

		switch($ch) {

			case 'ă':
			case 'â':
				
				return 'a';
			
			case 'î':

				return 'i';

			case 'ș':

				return 's';

			case 'ț':

				return 't';

			default:

				return $ch;
		}
	}

	//inlocuieste diactriticele
	private static function stripDiacritics($str) {

		$strippedStr = '';
		
		$currOffset = 0;
		$finalOffset = strlen($str) - 1;
		
		while($currOffset <= $finalOffset) {

			$ch = '';
			if ($currOffset == $finalOffset) {

				$ch = substr($str, $currOffset, 1);
				$currOffset ++;
			}
			else {

				$ch = substr($str, $currOffset, 2);
				if (strstr('ăâîșț', $ch)) {

					$currOffset += 2;
				}
				else {

					$ch = substr($str, $currOffset, 1);
					$currOffset ++;
				}
			}

			$strippedStr .= self::replaceDiacritic($ch);
		}

		return $strippedStr;
	}



	public function insertRow($before, $middle, $after, $diacritic) {

		try {
			
			$tableObj = Model::factory(self::$_table);
			$tableObj->create();


			$tableObj->before = $before;
			$tableObj->middle = $middle;
			$tableObj->after = $after;
			
			
			$tableObj->defaultForm = '0';
			$tableObj->curvedForm = '0';
			$tableObj->circumflexForm = '0';

			if (strstr("âî", $middle)) {

				$tableObj->circumflexForm = '1';
			}
			else if (strstr("ășț", $middle)) {

				$tableObj->curvedForm = '1';
			}
			else { // if (strstr("aist", $middle))

				$tableObj->defaultForm = '1';
			}


			$tableObj->save();
		}
		catch(Exception $ex) {

			logException($ex);
		}
	}

	


	public static function updateRow($before, $middle, $after) {

		return false;
	}


	public static function entryExists($before, $middle, $after) {
		
		return false;
		$foundEntry = Model::factory(self::$_table)->raw_query("Select id from self::$_table where
				 before = '$before' and middle = '$middle' and after = '$after';")->find_one();
		if ($foundEntry) {

			return true;
		}

		return false;
	}


	public static function save2Db($before, $middle, $after) {

		$diacritic = substr($middle, 0);

		$before = self::stripDiacritics($before);
		$middle = self::stripDiacritics($middle);
		$after = self::stripDiacritics($after);
			

			
		if (self::entryExists($before, $middle, $after)) {

			self::updateRow($before, $middle, $after, $diacritic);
		}
		else {

			self::insertRow($before, $middle, $after, $diacritic);
		}
	}
}

?>
