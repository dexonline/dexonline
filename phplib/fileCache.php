<?php
define('CACHE_EXPIRATION_SECONDS', 86400);
define('CACHE_PREFIX', '/tmp/dexcache_');
define('CKEY_TOP', 'top');
define('CKEY_WORDS_ALL', 'words_all');
define('CKEY_WORDS_LAST_MONTH', 'words_last_month');

/**
 * Returns the cached value for this key, or NULL if the key isn't cached or
 * it has expired.
 */

function _fileCache_openFileForRead($fileName) {
  if (!file_exists($fileName)) {
    return NULL;
  }

  $fileTime = filemtime($fileName);
  if (time() - $fileTime >= CACHE_EXPIRATION_SECONDS) {
    unlink($fileName);
    return NULL;
  }

  return fopen($fileName, 'rb');
}

function fileCache_get($key) {
  $fileName = CACHE_PREFIX . $key;
  $f = _fileCache_openFileForRead($fileName);
  if (!$f) {
    return NULL;
  }
  $fileSize = filesize($fileName);
  $s = fread($f, $fileSize);
  fclose($f);
  return unserialize($s);
}

function fileCache_put($key, $value) {
  $f = fopen(CACHE_PREFIX . $key, 'wb');
  fwrite($f, serialize($value));
  fclose($f);
}

function fileCache_getWordCount() {
  return fileCache_get(CKEY_WORDS_ALL);
}

function fileCache_putWordCount($value) {
  fileCache_put(CKEY_WORDS_ALL, $value);
}

function fileCache_getWordCountLastMonth() {
  return fileCache_get(CKEY_WORDS_LAST_MONTH);
}

function fileCache_putWordCountLastMonth($value) {
  fileCache_put(CKEY_WORDS_LAST_MONTH, $value);
}

function fileCache_getTop($manual) {
  return fileCache_get(CKEY_TOP . ($manual ? '1' : '0'));
}

function fileCache_putTop($value, $manual) {
  fileCache_put(CKEY_TOP . ($manual ? '1' : '0'), $value);
}

function _fileCache_getKeyForModeratorIp($ip) {
  return "moderator_$ip";
}

function fileCache_getModeratorQueryResults($ip) {
  $key = _fileCache_getKeyForModeratorIp($ip);
  return fileCache_get($key);
}

function fileCache_putModeratorQueryResults($ip, $defIds) {
  $key = _fileCache_getKeyForModeratorIp($ip);
  fileCache_put($key, $defIds);
}

?>
