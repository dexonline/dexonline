<?php

DebugInfo::init();

class DebugInfo {
  private static $startTimestamp;
  private static $lastClockReset;
  private static $enabled = true;
  private static $debugInfo = array();

  public static function init() {
    self::$startTimestamp = self::getTimeInMillis();
    self::$lastClockReset = self::getTimeInMillis();
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

  public static function getDebugInfo() {
    $enabled = self::$enabled && (session_getUserNick() == pref_getDebugUser());
    SmartyWrap::assign('debug_enabled', $enabled);
    if ($enabled) {
      SmartyWrap::assign('debug_messages', self::$debugInfo);
      SmartyWrap::assign('debug_runningTimeMillis', self::getRunningTimeInMillis());
      SmartyWrap::assign('debug_ormQueryLog', ORM::get_query_log());
    }
  }
}

?>
