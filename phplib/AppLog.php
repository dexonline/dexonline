<?php
/*
 * Alin Ungureanu, 2013
 * alyn.cti@gmail.com
 */


/*
 * variabile ce pot fi schimbate in functie de aplicatie
 * $exceptionExit = daca a aparut o exceptie, se inchide aplicatia
 * $logFile = fisierul de log al aplicatiei, poate fi schimbat dupa
 * includerea AppLog.php
 */
$exceptionExit = Config::get('app_log.exception_exit');
$logFile = Config::get('app_log.crawler_log');
/*
 * Logheaza activitatea crawlerului, afiseaza exceptiile
 * $level poate fi de forma :  __FILE__.' - '.__CLASS__.'::'.__FUNCTION__.' line '.__LINE__
 * sau mai simplu
 */
class AppLog {

	private static $exceptionExit;
	private static $logFile;

	function __construct() {
		global $exeptionExit, $logFile;
		self::$exceptionExit = $exceptionExit;
		self::$logFile = $logFile;
	}

	/*
	 *	application Log, $message = mesajul afisat
	 *  $detailLevel = nivelul de detaliu afisat
	 *  	0 - log disabled
	 *  	1 - minimal log: iterations, stages 
	 *  	2 - normal log: all messages except function debug (INSIDE __FILE__ - __CLASS__::__FUNCTION__ ...)
	 *  	3 - full log, all messages allowed, no restrictions on $detailLevel
	 *		4 - function debug only
	 */
	static function log($message, $detailLevel = 2) {

		global $logFile;
		//filtreaza mesajele
		switch(Config::get('app_log.log_detail_level')) {
			case 0: 
				return;
			case 1: case 4:
				//minimal sau doar function debug
				if ($detailLevel != Config::get('app_log.log_detail_level')) {
					return;
				}
				break;
			//normal, fara function debug
			case 2:
				if (substr($message, 0, 6) == 'INSIDE')
					return;
				break;
			//orice log acceptat
			case 3:
				break;
			//default este ca '3'
			default:
				break;
		}		
		//log in fisier
		if (Config::get('app_log.log2file'))
		try {
			$fd = fopen($logFile, "a+");
			fprintf($fd, "%s\n", date("Y-m-d H:i:s") . '::' . $message);
			fclose( $fd);
		}
		catch (Exception $ex) {
			echo "LOG FILE PROBLEM\n";
		}
		//log in stdout
		if (Config::get('app_log.log2screen')) {

			echo date("Y-m-d H:i:s") . '::' . $message. self::getCorrespondentNewLine();
			flush();
		}
	}
	/*
	 * Daca apare vreo exceptie, se afiseaza mesajul si se intrerupe programul
	 * functia primeste ca parametru o exceptie pentru a determina mesajul si
	 * locatia
	 */
	static function exceptionLog($exception) {
		global $exceptionExit;
		$location = $exception->getFile(). '  line '. $exception->getLine();
		self::log('Exception in: ' .$location. '  Msg: ' .$exception->getMessage(), 0);
		$level = null;
		$ex = null;
		//controlul din fisierul de configurare dex.conf
		if ($exceptionExit == 'true') {
			crawLerLog('Exiting');
				exit();
		}
	}
	/*
	 * returneaza caracterul de linie noua
	 * in functie de stdout handler
	 */
	static function getCorrespondentNewLine() {
		//daca este terminal
		if (PHP_SAPI == 'cli') {
			return PHP_EOL;
		}
		//altfel este browser (apache2handler, etc)
		else return '<br>';
	}
}

?>