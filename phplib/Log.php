<?php

Log::init();

class Log {
  static $file;
  static $level;

  static function init() {
    self::$file = fopen(Config::get('logging.file'), 'a');
    self::$level = Config::get('logging.level'); // no constant() call needed
  }

  private static function write($level, $args) {
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
      $format = array_shift($args);
      $user = User::getActive();

      fprintf(self::$file, "[{$date}] [{$file}:{$line}] ");
      if ($user) {
        fprintf(self::$file, "[{$user->nick}] ");
      }
      vfprintf(self::$file, "{$format}\n", $args);
    }
  }

  /**
   * The following functions take printf-style arguments (format + args).
   */
  static function emergency(/* Variable-length argument list */) {
    self::write(LOG_EMERG, func_get_args());
  }

  static function alert(/* Variable-length argument list */) {
    self::write(LOG_ALERT, func_get_args());
  }

  static function critical(/* Variable-length argument list */) {
    self::write(LOG_CRIT, func_get_args());
  }

  static function error(/* Variable-length argument list */) {
    self::write(LOG_ERR, func_get_args());
  }

  static function warning(/* Variable-length argument list */) {
    self::write(LOG_WARNING, func_get_args());
  }

  static function notice(/* Variable-length argument list */) {
    self::write(LOG_NOTICE, func_get_args());
  }

  static function info(/* Variable-length argument list */) {
    self::write(LOG_INFO, func_get_args());
  }

  static function debug(/* Variable-length argument list */) {
    self::write(LOG_DEBUG, func_get_args());
  }

}
