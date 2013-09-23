<?php
/*
 * Alin Ungureanu, 2013
 * alyn.cti@gmail.com
 */
require_once '../../phplib/util.php';
require_once '../../phplib/serverPreferences.php';
require_once '../../phplib/db.php';
require_once '../../phplib/idiorm/idiorm.php';


function printUsage() {
	echo "::Usage::".PHP_EOL."php clean_all.php [ -c | --crawler] [ -d | --diacritics]".PHP_EOL;
	flush();
	exit();
}

if (count($argv) == 1) printUsage();

db_init();

$db = ORM::get_db();
$db->beginTransaction();



if ($argv[1] == '--crawler' || $argv[1] == '-c') {


	function removeFiles($regexPath) {

		exec("rm -rf $regexPath");
	}

	try {

		//sterge toate fisierele salvate
		removeFiles('ParsedText/*');
		removeFiles('RawPage/*');


		echo 'files deleted'.pref_getSectionPreference('crawler', 'new_line');

	    $db->exec('TRUNCATE Table CrawledPage;');
	    $db->exec('TRUNCATE Table Link;');
	    $db->commit();

		echo "tables 'Link' and 'CrawledPage' truncated".pref_getSectionPreference('crawler', 'new_line');

		echo 'The cleaning process was successful'.pref_getSectionPreference('crawler', 'new_line');
	}

	catch(Exception $ex) {

		echo 'The cleaning process encountered a problem '.pref_getSectionPreference('crawler', 'new_line').$ex->getMessage();
	}
}
else if ($argv[1] == '--diacritics' || $argv[1] == '-d') {

	try{
		$db->exec('TRUNCATE Table Diacritics;');
		$db->exec('TRUNCATE Table FilesUsedInDiacritics;');
	    $db->commit();
		echo "tables 'Diacritics' and 'FilesUsedInDiacritics' truncated".pref_getSectionPreference('crawler', 'new_line');
		echo 'The cleaning process was successful'.pref_getSectionPreference('crawler', 'new_line');
	}
	catch(Exception $e) {

		echo 'The cleaning process encountered a problem '.pref_getSectionPreference('crawler', 'new_line').$ex->getMessage();
	}

}
else printUsage(); 
/**/
?>