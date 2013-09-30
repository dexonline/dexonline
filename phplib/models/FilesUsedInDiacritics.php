<?php


class FilesUsedInDiacritics  extends BaseObject implements DatedObject {
	
	public static $_table = 'FilesUsedInDiacritics';

	public static function save2Db($fileId) {

		try {
			$tableObj = Model::factory(self::$_table);
			$tableObj->create();
			$tableObj->fileId = $fileId;
			$tableObj->save();
		}
		catch(Exception $ex) {

			AppLog::exceptionLog($ex);
		}
	}
}

?>