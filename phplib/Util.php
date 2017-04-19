<?php

// Generic functions that don't belong anywhere else.

class Util {

  const CURL_COOKIE_FILE = '/dexonline_cookie.txt';
  const INFINITY = 1000000000;

  // Returns a pair of ($data, $httpCode)
  static function fetchUrl($url) {
    $url = str_replace(' ', '%20', $url);

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true); // follow redirects
    $data = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    return [$data, $httpCode];
  }

  static function makeRequest($url, $data, $method = 'POST', $useCookies = false) {
    $ch = curl_init($url);
    if ($useCookies) {
      curl_setopt($ch, CURLOPT_COOKIEFILE, Config::get('global.tempDir') . CURL_COOKIE_FILE);
      curl_setopt($ch, CURLOPT_COOKIEJAR, Config::get('global.tempDir') . CURL_COOKIE_FILE);
    }
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    // For JSON data, set the content type
    if (is_string($data) && is_object(json_decode($data))) {
      curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        "Content-Type: application/json",
        'Content-Length: ' . strlen($data)
      ));
    }
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_USERAGENT, 'dexonline.ro');
    $result = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    return [$result, $httpCode];
  }

  // Given an array of sorted arrays, finds the smallest interval that includes
  // at least one element from each array. Named findSnippet in honor of Google.
  static function findSnippet($p) {
    $result = self::INFINITY;
    $n = count($p);
    $indexes = array_pad(array(), $n, 0);
    $done = false;

    while (!$done) {
      $min = self::INFINITY;
      $max = -1;
      for ($i = 0; $i < $n; $i++) {
        $k = $p[$i][$indexes[$i]];
        if ($k < $min) {
          $min = $k;
          $minPos = $i;
        }
        if ($k > $max) {
          $max = $k;
        }
      }
      if ($max - $min < $result) {
        $result = $max - $min;
      }
      if (++$indexes[$minPos] == count($p[$minPos])) {
        $done = true;
      }
    }

    return $result;
  }

}
