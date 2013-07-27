<?php
/*
 * Alin Ungureanu, 2013
 * alyn.cti@gmail.com
 */
require_once '../../phplib/util.php';
require_once '../../phplib/serverPreferences.php';
require_once '../../phplib/db.php';
require_once '../../phplib/idiorm/idiorm.php';

try {

	//sterge toate fisierele salvate
	$files = glob('ParsedText/*'); // get all file names
	foreach($files as $file) { // iterate files
	  if(is_file($file))
	    unlink($file); // delete file
	}

	$files = glob('RawPage/*'); // get all file names
	foreach($files as $file) { // iterate files
	  if(is_file($file))
	    unlink($file); // delete file
	}

	echo 'files deleted'.pref_getSectionPreference('crawler', 'new_line');


	db_init();

	$db = ORM::get_db();
    $db->beginTransaction();
    $db->exec('TRUNCATE Table CrawledPage;');
    $db->exec('TRUNCATE Table Link;');
    $db->commit();

	echo "tables 'Link' and 'CrawledPage' emptied".pref_getSectionPreference('crawler', 'new_line');

	echo 'The cleaning process was successful'.pref_getSectionPreference('crawler', 'new_line');
}

catch(Exception $ex) {

	echo 'The cleaning process encountered a problem '.pref_getSectionPreference('crawler', 'new_line').$ex->getMessage();
}

?>