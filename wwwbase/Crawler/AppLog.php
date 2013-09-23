<?php
/*
 * Alin Ungureanu, 2013
 * alyn.cti@gmail.com
 */

$exceptionExit = pref_getSectionPreference('crawler', 'exception_exit');
$logFile = pref_getSectionPreference('crawler', 'crawler_log');
/*
 * Logheaza activitatea crawlerului, afiseaza exceptiile
 * $level poate fi de forma :  __FILE__.' - '.__CLASS__.'::'.__FUNCTION__.' line '.__LINE__
 * sau mai simplu
 */
function getCorrespondentNewLine() {

	//daca este terminal
	if (PHP_SAPI == 'cli') {
		return PHP_EOL;
	}
	//altfel este browser
	else return '<br>';
}

function crawlerLog($message, $level = '') {

	global $logFile;

	//afisaza sau nu in log "INSIDE " . __FILE__ . ' - ' . __CLASS__ . '::' . __FUNCTION__ . '() - '
	// . 'line '.__LINE__ acolo unde exista
	if (!pref_getSectionPreference('crawler', 'function_trace')) {

		if (substr($message, 0, 6) == 'INSIDE')
			return;
	}


	//log in fisier
	if (pref_getSectionPreference('crawler', 'log2file'))
	try{
		$fd = fopen($logFile, "a+");
		fprintf($fd, "%s\n", date("Y-m-d H:i:s") . '::' . $level . '::' . $message);
		fclose( $fd);
	}
	catch(Exception $ex) {

		echo "AVEM O PROBLEMA CU FISIERUL DE LOG AL CRAWLERULUI" .pref_getSectionPreference('crawler', 'new_line');
	}
	//log in stdout
	if(pref_getSectionPreference('crawler', 'log2screen')) {

		echo date("Y-m-d H:i:s") . '::' . $level . '::' . $message.getCorrespondentNewLine();
		flush();
	}

	$message = null;
	$location = null;
}

/*
 * Daca apare vreo exceptie, se afiseaza mesajul si se intrerupe programul
 * functia primeste ca parametru o exceptie pentru a determina mesajul si
 * locatia
 */
function logException($exception) {

	global $exceptionExit;

	$level = $exception->getFile(). '  line '. $exception->getLine();

	crawlerLog('Exception: '.$exception->getMessage(), $level);

	$level = null;

	$ex = null;

	//controlul din fisierul de configurare dex.conf
	if ($exceptionExit == 'true') {

		crawLerLog('Exiting');
		exit();
	}
}


?>