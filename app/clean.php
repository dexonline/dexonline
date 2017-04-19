<?php
/*
 * Alin Ungureanu, 2013
 * alyn.cti@gmail.com
 */
require_once __DIR__ . '/../phplib/Core.php';

function printUsage() {
	echo "::Usage::" . PHP_EOL . "php clean.php [ -c | --crawler] [ -d | --diacritics]" . PHP_EOL;
	flush();
	exit();
}

if (count($argv) == 1) printUsage();

$db = ORM::get_db();
$db->beginTransaction();



if ($argv[1] == '--crawler' || $argv[1] == '-c') {


	function removeFiles($regexPath) {

		exec("rm -rf $regexPath");
	}

	try {

		// șterge toate fișierele salvate
		removeFiles('ParsedText/*');
		removeFiles('RawPage/*');


		echo "files deleted\n";

    $db->exec('TRUNCATE Table CrawledPage;');
    $db->exec('TRUNCATE Table Link;');
    $db->commit();

    echo "tables 'Link' and 'CrawledPage' truncated\n";

		echo "The cleaning process was successful\n";
	}

	catch(Exception $ex) {

		echo "The cleaning process encountered a problem: " . $ex->getMessage() . "\n";
	}
}
else if ($argv[1] == '--diacritics' || $argv[1] == '-d') {

	try{
		$db->exec('TRUNCATE Table Diacritics;');
		$db->exec('TRUNCATE Table FilesUsedInDiacritics;');
    $db->commit();
    echo "tables 'Diacritics' and 'FilesUsedInDiacritics' truncated\n";
		echo "The cleaning process was successful\n";
	}
	catch(Exception $ex) {

		echo "The cleaning process encountered a problem: " . $ex->getMessage() . "\n";
	}

}
else printUsage(); 
/**/
?>
