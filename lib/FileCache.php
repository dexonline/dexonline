<?php

class FileCache {
  const CACHE_EXPIRATION_SECONDS = 86400;
  const CACHE_PREFIX = '/dexcache_';
  const CKEY_WORDS_ALL = 'wordsTotal';
  const CKEY_WORDS_LAST_MONTH = 'wordsLastMonth';
  const CKEY_ARTISTS_TOP = 'topArtists';

  private static function openFileForRead($fileName) {
    if (!file_exists($fileName)) {
      return NULL;
    }

    $fileTime = filemtime($fileName);
    if (time() - $fileTime >= self::CACHE_EXPIRATION_SECONDS) {
      unlink($fileName);
      return NULL;
    }

    return fopen($fileName, 'rb');
  }

  /**
   * Returns the cached value for this key, or NULL if the key isn't cached or
   * it has expired.
   **/
  static function get($key, $unserialize = true) {
    $fileName = self::getFileName($key);
    $f = self::openFileForRead($fileName);
    if (!$f) {
      return NULL;
    }
    $fileSize = filesize($fileName);
    $s = fread($f, $fileSize);
    fclose($f);
    return $unserialize ? unserialize($s) : $s;
  }

  static function put($key, $value, $serialize = true) {
    $f = fopen(self::getFileName($key), 'wb');
    fwrite($f, $serialize ? serialize($value) : $value);
    fclose($f);
  }

  private static function getFileName($key) {
    return Config::TEMP_DIR . self::CACHE_PREFIX . $key;
  }

  static function getWordCount() {
    return self::get(self::CKEY_WORDS_ALL);
  }

  static function putWordCount($value) {
    self::put(self::CKEY_WORDS_ALL, $value);
  }

  static function getWordCountLastMonth() {
    return self::get(self::CKEY_WORDS_LAST_MONTH);
  }

  static function putWordCountLastMonth($value) {
    self::put(self::CKEY_WORDS_LAST_MONTH, $value);
  }

  static function getArtistTop() {
    return self::get(self::CKEY_ARTISTS_TOP);
  }

  static function putArtistTop($value) {
    self::put(self::CKEY_ARTISTS_TOP, $value);
  }
}
