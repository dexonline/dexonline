<?php

// Generic functions that don't belong anywhere else.

class Util {

  const CURL_COOKIE_FILE = '/dexonline_cookie.txt';
  const INFINITY = 1000000000;

  /* Returns $obj->$prop for every $obj in $a */
  static function objectProperty($a, $prop) {
    $results = [];
    foreach ($a as $obj) {
      $results[] = $obj->$prop;
    }
    return $results;
  }

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

  // Assumes the arrays are sorted and do not contain duplicates.
  static function intersectArrays($a, $b) {
    $i = 0;
    $j = 0;
    $countA = count($a);
    $countB = count($b);
    $result = [];

    while ($i < $countA && $j < $countB) {
      if ($a[$i] < $b[$j]) {
        $i++;
      } else if ($a[$i] > $b[$j]) {
        $j++;
      } else {
        $result[] = $a[$i];
        $i++;
        $j++;
      }
    }

    return $result;
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

  static function recount() {
    Variable::poke(
      'Count.pendingDefinitions',
      Model::factory('Definition')->where('status', Definition::ST_PENDING)->count()
    );
    Variable::poke(
      'Count.definitionsWithTypos',
      Model::factory('Typo')->select('definitionId')->distinct()->count()
    );
    Variable::poke(
      'Count.ambiguousAbbrevs',
      Definition::countAmbiguousAbbrevs()
    );
    Variable::poke(
      'Count.rawOcrDefinitions',
      Model::factory('OCR')->where('status', 'raw')->count()
    );
    // this takes about 300 ms
    Variable::poke(
      'Count.unassociatedDefinitions',
      Definition::countUnassociated()
    );
    Variable::poke(
      'Count.unassociatedEntries',
      count(Entry::loadUnassociated())
    );
    Variable::poke(
      'Count.unassociatedLexems',
      Lexem::countUnassociated()
    );
    Variable::poke(
      'Count.unassociatedTrees',
      Tree::countUnassociated()
    );
    Variable::poke(
      'Count.ambiguousEntries',
      count(Entry::loadAmbiguous())
    );
    Variable::poke(
      'Count.entriesWithDefinitionsToStructure',
      count(Entry::loadWithDefinitionsToStructure())
    );
    Variable::poke(
      'Count.lexemesWithoutAccent',
      Model::factory('Lexem')->where('consistentAccent', 0)->count()
    );
    Variable::poke(
      'Count.ambiguousLexemes',
      count(Lexem::loadAmbiguous())
    );
    Variable::poke(
      'Count.temporaryLexemes',
      Model::factory('Lexem')->where('modelType', 'T')->count()
    );
    Variable::poke(
      'Count.treeMentions',
      Model::factory('Mention')->where('objectType', Mention::TYPE_TREE)->count()
    );
    Variable::poke(
      'Count.lexemesWithComments',
      Model::factory('Lexem')->where_not_null('comment')->count()
    );
  }

  static function redirect($location) {
    // Fix an Android issue with redirects caused by diacritics
    $location = str_replace(
      ['ă', 'â', 'î', 'ș', 'ț', 'Ă', 'Â', 'Î', 'Ș', 'Ț'],
      ['%C4%83', '%C3%A2', '%C3%AE', '%C8%99', '%C8%9B',
       '%C4%82', '%C3%82', '%C3%8E', '%C8%98', '%C8%9A'],
      $location);
    FlashMessage::saveToSession();
    header("HTTP/1.1 301 Moved Permanently");
    header("Location: $location");
    exit;
  }

  static function assertNotMirror() {
    if (Config::get('global.mirror')) {
      SmartyWrap::display('mirror_message.tpl');
      exit;
    }
  }

  static function assertNotLoggedIn() {
    if (User::getActive()) {
      Util::redirect(Core::getWwwRoot());
    }
  }

  static function suggestNoBanner() {
    if (isset($_SERVER['REQUEST_URI']) && preg_match('/(masturba|fute|condom)/', $_SERVER['REQUEST_URI'])) {
      return true; // No banners on certain obscene pages
    }
    if (User::getActive() && User::getActive()->noAdsUntil > time()) {
      return true; // User is an active donor
    }
    return false;
  }
}
