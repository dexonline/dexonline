<?php

class OS {

  const OS_UNKNOWN = 1;
  const OS_WIN = 2;
  const OS_LINUX = 3;
  const OS_OSX = 4;

  static function errorAndExit($msg, $exitCode = 1) {
    Log::error("ERROR: $msg");
    exit($exitCode);
  }

  static function execute($command, &$output = null) {
    Log::info('Executing %s', $command);
    exec($command, $output, $exitCode);
    $output = implode("\n", $output);
    return $exitCode;
  }

  static function executeAndAssert($command, &$output = null) {
    $exitCode = self::execute($command, $output);
    if ($exitCode) {
      Log::error('Output: %s', $output);
      self::errorAndExit("Failed command: $command (code $exitCode)");
    }
  }

  /** Checks if the directory specified in $path is empty */
  static function isDirEmpty($path) {
    $files = scandir($path);
    return count($files) == 2;
  }

  static function deleteFile($fileName) {
    if (file_exists($fileName)) {
      unlink($fileName);
    }
  }

  /**
   * Returns a constant for the OS underneath
   * @return int
   */
  static public function getOS() {
    switch (true) {
      case stristr(PHP_OS, 'DAR'): return self::OS_OSX;
      case stristr(PHP_OS, 'WIN'): return self::OS_WIN;
      case stristr(PHP_OS, 'LINUX'): return self::OS_LINUX;
      default : return self::OS_UNKNOWN;
    }
  }

  /**
   * Returns the content display command specific to the OS
   * @return string
   */
  static public function getCatCommand() {
    switch (self::getOS()) {
      case self::OS_WIN: return "type";
      default : return "cat";
    }
  }

}
