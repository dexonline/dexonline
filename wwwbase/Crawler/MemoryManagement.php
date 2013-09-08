<?php
require_once '../../phplib/util.php';
require_once '../../phplib/serverPreferences.php';

require_once 'AppLog.php';

class MemoryManagement {

	/*
	 * cleans lost memory refferences
	 */
	public static function clean($print = false) {

			gc_enable(); // Enable Garbage Collector
			if ($print) {
			
				crawlerLog(gc_collect_cycles() . " garbage cycles cleaned"); // # of elements cleaned up
			}
			gc_disable(); // Disable Garbage Collector
	}

	public static function showUsage($message = '', $realUsage = false, $units = "B") {

		$truncate = 1;
		switch($units) {

			case 'KB':
				$truncate = pow(10,3);
				break;
			case 'MB':
				$truncate = pow(10,6);
				break;
			case 'GB':
				$truncate = pow(10,9);
				break;
			default: //Bytes

				break;
		}

		crawlerLog("Memory Usage $message: " . sprintf("%.0f", memory_get_usage($realUsage) / $truncate) . ' ' . $units);
	}
}

?>