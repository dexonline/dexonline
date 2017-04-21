<?php

class Link extends BaseObject implements DatedObject {
	//implements DatedObject {

	public static $_table = 'Link';

	//adauga o intrare nou in tabelul Link
	static function saveLink2DB($canonicalUrl, $domain, $crawledPageId) {

		//nu inseram acelasi link de 2 ori
		if (Model::factory(self::$_table)->where('canonicalUrl', $canonicalUrl)->find_one()) {
			return;
		}

		try {

			$tableObj = Model::factory(self::$_table)->create();
			$tableObj->canonicalUrl = $canonicalUrl;
			$tableObj->domain = $domain;
			$tableObj->crawledPageId = $crawledPageId;
			$tableObj->save();

			return $tableObj->id;
		}
		catch(Exception $ex) {

			AppLog::exceptionLog($ex);
		}

		return null;
	}
}

?>
