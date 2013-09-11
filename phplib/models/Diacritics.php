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

		return $str;

		
		$strippedStr = '';
		$strArray = str_split($str, 1);
		
		foreach($strArray as $ch) {

			$strippedStr .= self::replaceDiacritic($ch);
		}


		return $strippedStr;
	}


	public static function save2Db($before, $middle, $after) {

		try {
			
			$tableObj = Model::factory(self::$_table);
			$tableObj->create();


			$tableObj->before = self::stripDiacritics($before);
			$tableObj->middle = self::stripDiacritics($middle);
			$tableObj->after = self::stripDiacritics($after);
			
			
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
}

?>
