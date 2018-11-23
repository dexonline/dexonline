<?php

class DebugInfo {
  private static $startTimestamp;
  private static $lastClockReset;
  private static $enabled = true;
  public static $debugInfo = [];

  static function init() {
    self::$startTimestamp = self::$lastClockReset = self::getTimeInMillis();
  }

  static function getTimeInMillis() {
    $seconds = microtime(true);
    return (int)($seconds * 1000);
  }

  static function resetClock() {
    self::$lastClockReset = self::getTimeInMillis();
  }

  // Certain scripts produce a lot of debug info and need a way to disable debugging.
  static function disable() {
    self::$enabled = false;
  }

  static function isEnabled() {
    return
      self::$enabled &&
      User::getActive() &&
      (User::getActive()->nick == Config::get('global.debugUser'));
  }

  // Measures the time since the last clock reset and appends a message
  static function stopClock($message) {
    if (self::$enabled) {
      $delta = self::getTimeInMillis() - self::$lastClockReset;
      self::$debugInfo[] = "$delta ms: [$message]";
      self::resetClock();
      return $delta;
    }
    return null;
  }

  static function getRunningTimeInMillis() {
    return self::getTimeInMillis() - self::$startTimestamp;
  }
}
