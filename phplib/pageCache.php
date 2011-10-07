<?php

function pageCache_get() {
  // Check if we are allowed to use the cached page
  if (!pref_getServerPreference('pageCache')) { return false; }       // Page caching is disabled
  if (array_key_exists('prefs', $_COOKIE)) { return false; }          // This user has non-default preferences or is logged in
  if (array_key_exists('flashMessage', $_SESSION)) { return false; }  // There is a flash to display so we need a fresh page

  $fullPath = pageCache_getFileName();
  if (file_exists($fullPath)) {
    return file_get_contents($fullPath);
  }
  return false;
}

function pageCache_put($output) {
  // Check if we are allowed to cache this page
  if (!pref_getServerPreference('pageCache')) { return; } // Page caching is disabled
  if (array_key_exists('prefs', $_COOKIE)) { return; }    // This page is user-specific (logged in, uses prefs or uses custom skin)

  $fullPath = pageCache_getFileName();
  @mkdir(dirname($fullPath), 0777, true);
  file_put_contents($fullPath, $output);
}

function pageCache_getFileName() {
  $uri = urldecode($_SERVER['REQUEST_URI']);
  $hash = crc32($uri) % 1000; // Avoid one directory with 1.000.000 files
  return util_getRootPath() . pref_getServerPreference('pageCacheDir') . '/' . $hash . $uri . '.html';
}

?>
