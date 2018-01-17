<?php

class MemoryManagement {

	/*
	 * cleans lost memory refferences
	 */
	static function clean($print = false) {

			gc_enable(); // Enable Garbage Collector
			if ($print) {
			
				AppLog::log(gc_collect_cycles() . " garbage cycles cleaned"); // # of elements cleaned up
			}
			gc_disable(); // Disable Garbage Collector
	}

	static function showUsage($message = '', $realUsage = false, $units = "B") {

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

		AppLog::log("Memory Usage $message: " . sprintf("%.0f", memory_get_usage($realUsage) / $truncate) . ' ' . $units, 1);
	}
}
