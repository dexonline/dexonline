<?php

Log::init();

class Log {
  static $file;
  static $level;

  static function init() {
    self::$file = fopen(Config::get('logging.file'), 'a');
    self::$level = Config::get('logging.level'); // no constant() call needed
  }

  /* Takes vprintf-style arguments (format + array of args). */
  static function write($level, $format, $args) {
    if ($level <= self::$level) {
      // Find the bottom-most call outside this class
      $trace = debug_backtrace();
      $i = 0;
      while ($trace[$i]['file'] == __FILE__) {
        $i++;
      }

      $file = basename($trace[$i]['file']);
      $line = $trace[$i]['line'];
      $date = date("Y-m-d H:i:s");

      vfprintf(self::$file, "[{$date}] [{$file}:{$line}] {$format}\n", $args);
    }

  }

  /**
   * The following functions take printf-style arguments (format + args).
   */
  static function emergency($format, ...$args) {
    self::write(LOG_EMERG, $format, $args);
  }

  static function alert($format, ...$args) {
    self::write(LOG_ALERT, $format, $args);
  }

  static function critical($format, ...$args) {
    self::write(LOG_CRIT, $format, $args);
  }

  static function error($format, ...$args) {
    self::write(LOG_ERR, $format, $args);
  }

  static function warning($format, ...$args) {
    self::write(LOG_WARNING, $format, $args);
  }

  static function notice($format, ...$args) {
    self::write(LOG_NOTICE, $format, $args);
  }

  static function info($format, ...$args) {
    self::write(LOG_INFO, $format, $args);
  }

  static function debug($format, ...$args) {
    self::write(LOG_DEBUG, $format, $args);
  }

}
