<?php

class DebugInfo {
  private static $startTimestamp;
  private static $lastClockReset;
  private static $enabled = true;
  public static $debugInfo = [];

  public static function init() {
    self::$startTimestamp = self::$lastClockReset = self::getTimeInMillis();
  }

  private static function getTimeInMillis() {
    $seconds = microtime(true);
    return (int)($seconds * 1000);
  }

  public static function resetClock() {
    self::$lastClockReset = self::getTimeInMillis();
  }

  // Certain scripts produce a lot of debug info and need a way to disable debugging.
  public static function disable() {
    self::$enabled = false;
  }

  public static function isEnabled() {
    return self::$enabled && (session_getUserNick() == Config::get('global.debugUser'));
  }

  // Measures the time since the last clock reset and appends a message
  public static function stopClock($message) {
    if (self::$enabled) {
      $delta = self::getTimeInMillis() - self::$lastClockReset;
      self::$debugInfo[] = "$delta ms: [$message]";
      self::resetClock();
    }
  }

  public static function getRunningTimeInMillis() {
    return self::getTimeInMillis() - self::$startTimestamp;
  }
}

?>
