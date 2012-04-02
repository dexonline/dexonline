<?php

class FileCache {
  private static $CACHE_EXPIRATION_SECONDS = 86400;
  private static $CACHE_PREFIX = '/tmp/dexcache_';
  private static $CKEY_TOP = 'top';
  private static $CKEY_TOP_ALL = 'top_all';
  private static $CKEY_WORDS_ALL = 'words_all';
  private static $CKEY_WORDS_LAST_MONTH = 'words_last_month';

  /**
   * Returns the cached value for this key, or NULL if the key isn't cached or
   * it has expired.
   */

  private static function openFileForRead($fileName) {
    if (!file_exists($fileName)) {
      return NULL;
    }

    $fileTime = filemtime($fileName);
    if (time() - $fileTime >= self::$CACHE_EXPIRATION_SECONDS) {
      unlink($fileName);
      return NULL;
    }

    return fopen($fileName, 'rb');
  }

  static function get($key) {
    $fileName = self::$CACHE_PREFIX . $key;
    $f = self::openFileForRead($fileName);
    if (!$f) {
      return NULL;
    }
    $fileSize = filesize($fileName);
    $s = fread($f, $fileSize);
    fclose($f);
    return unserialize($s);
  }

  static function put($key, $value) {
    $f = fopen(self::$CACHE_PREFIX . $key, 'wb');
    fwrite($f, serialize($value));
    fclose($f);
  }

  static function getWordCount() {
    return self::get(self::$CKEY_WORDS_ALL);
  }

  static function putWordCount($value) {
    self::put(self::$CKEY_WORDS_ALL, $value);
  }

  static function getWordCountLastMonth() {
    return self::get(self::$CKEY_WORDS_LAST_MONTH);
  }

  static function putWordCountLastMonth($value) {
    self::put(self::$CKEY_WORDS_LAST_MONTH, $value);
  }

  static function getTop($manual, $hidden = false) {
    $var = $hidden ? self::$CKEY_TOP_ALL : self::$CKEY_TOP;
    return self::get($var . ($manual ? '1' : '0'));
  }

  static function putTop($value, $manual, $hidden = false) {
    $var = $hidden ?  self::$CKEY_TOP_ALL : self::$CKEY_TOP;
    self::put($var . ($manual ? '1' : '0'), $value);
  }

  private static function getKeyForModeratorIp($ip) {
    return "moderator_$ip";
  }

  static function getModeratorQueryResults($ip) {
    $key = self::getKeyForModeratorIp($ip);
    return self::get($key);
  }

  static function putModeratorQueryResults($ip, $defIds) {
    $key = self::getKeyForModeratorIp($ip);
    self::put($key, $defIds);
  }
}

?>
