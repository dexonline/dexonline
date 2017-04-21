<?php


class Diacritics  extends BaseObject implements DatedObject {
	
	public static $_table = 'Diacritics';

	//inlocuieste diactriticele
	private static function stripDiacritics($str) {

		return str_replace(array('ă','â','î','ș','ț'), array('a','a','i','s','t'), $str);
	}

	/*
	 * Am definit propria forma de diacritice
	 * â,î		- circumflex
	 * ă,ș,ț	- curbat (caciulita si virgulita) 
	 * a,i,s 	- default
	 */

	private static function getDiacriticForm($diacritic, $update = array()) {

		$defaultForm	= '0';
		$curvedForm 	= '0';
		$circumflexForm	= '0';

		if (!empty($update)) {
			list($defaultForm, $curvedForm, $circumflexForm) = $update;
		}

		if (strstr("âî", $diacritic)) {

			$circumflexForm = intval($circumflexForm) + 1;
		}
		else if (strstr("ășț", $diacritic)) {

			$curvedForm = intval($curvedForm) + 1;
		}
		else { // daca strstr("aist", $diacritic)

			$defaultForm = intval($defaultForm) + 1;
		}
		return array($defaultForm, $curvedForm, $circumflexForm);

	}


	static function insertRow($before, $middle, $after, $diacritic) {

		try {
			
			$tableObj = Model::factory(self::$_table);
			$tableObj->create();
			
			$tableObj->before	= $before;
			$tableObj->middle	= $middle;
			$tableObj->after	= $after;

			list($tableObj->defaultForm, $tableObj->curvedForm,
				$tableObj->circumflexForm) = self::getDiacriticForm($diacritic);
			
			$tableObj->save();
		}
		catch(Exception $ex) {

			AppLog::exceptionLog($ex);
		}
	}

	static function entryExists($before, $middle, $after) {
		
		$before = strtolower(self::stripDiacritics($before));
		$middle = strtolower(self::stripDiacritics($middle));
		$after = strtolower(self::stripDiacritics($after));

		return Model::factory(self::$_table)->raw_query("Select * from Diacritics where
				 `before` = '$before' and `middle` = '$middle' and `after` = '$after';")->find_one();
	}
	
	static function updateRow($before, $middle, $after, $diacritic) {
	
		try {	
			$tableObj = Model::factory(self::$_table)->raw_query("Select * from Diacritics where
				 `before` = '$before' and `middle` = '$middle' and `after` = '$after';")->find_one();
			if (!$tableObj) {
				return false;
			}
			else {
				list($tableObj->defaultForm, $tableObj->curvedForm,
					$tableObj->circumflexForm) = self::getDiacriticForm($diacritic,
					array($tableObj->defaultForm, $tableObj->curvedForm,
						$tableObj->circumflexForm));
				$tableObj->save();
			}
			return true;
		}
		catch(Exception $e) {

			echo $e;
		}
		
		return false;
	}


	static function save2Db($before, $middle, $after) {

		$diacritic = mb_substr($middle, 0, 1);

		$before = self::stripDiacritics($before);
		$middle = self::stripDiacritics($middle);
		$after = self::stripDiacritics($after);
			
		if (!self::updateRow($before, $middle, $after, $diacritic)) {

			self::insertRow($before, $middle, $after, $diacritic);
		}
	}
}

?>
