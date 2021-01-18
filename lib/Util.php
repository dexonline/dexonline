<?php

// Generic functions that don't belong anywhere else.

class Util {

  const CURL_COOKIE_FILE = '/dexonline_cookie.txt';
  const INFINITY = 1000000000;

  private static $hiddenThirdPartyBanners = false;

  /* Returns $obj->$prop for every $obj in $a */
  static function objectProperty($a, $prop) {
    $results = [];
    foreach ($a as $obj) {
      $results[] = $obj->$prop;
    }
    return $results;
  }

  static function mapById($objects) {
    $result = [];
    foreach ($objects as $o) {
      $result[$o->id] = $o;
    }
    return $result;
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
      curl_setopt($ch, CURLOPT_COOKIEFILE, Config::TEMP_DIR . CURL_COOKIE_FILE);
      curl_setopt($ch, CURLOPT_COOKIEJAR, Config::TEMP_DIR . CURL_COOKIE_FILE);
    }
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    // For JSON data, set the content type
    if (is_string($data) && is_object(json_decode($data))) {
      curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Content-Type: application/json",
        'Content-Length: ' . strlen($data)
      ]);
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

  /**
   * Checks for sequences missing incremental value
   *
   * @param array $arr
   * @return boolean
   **/
  static function isIncrementalSequence($arr) {
    sort($arr);
    return sizeof(array_diff(range($arr[0], $arr[count($arr) - 1]), $arr)) == 0;
  }

  // Given an array of sorted arrays, finds the smallest interval that includes
  // at least one element from each array. Named findSnippet in honor of Google.
  static function findSnippet($p) {
    $result = self::INFINITY;
    $n = count($p);
    $indexes = array_pad([], $n, 0);
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
      'Count.pendingDefinitions', Model::factory('Definition')->where('status', Definition::ST_PENDING)->count()
    );
    Variable::poke(
      'Count.definitionsWithTypos', Model::factory('Typo')->select('definitionId')->distinct()->find_result_set()->count()
    );
    Variable::poke(
      'Count.ambiguousAbbrevs', Definition::countAmbiguousAbbrevs()
    );
    Variable::poke(
      'Count.rawOcrDefinitions', Model::factory('OCR')->where('status', 'raw')->count()
    );
    // this takes about 300 ms
    Variable::poke(
      'Count.unassociatedDefinitions', Definition::countUnassociated()
    );
    Variable::poke(
      'Count.missingRareGlyphsTags', count(Definition::loadMissingRareGlyphsTags())
    );
    Variable::poke(
      'Count.unneededRareGlyphsTags', count(Definition::loadUnneededRareGlyphsTags())
    );
    Variable::poke(
      'Count.unassociatedEntries', count(Entry::loadUnassociated())
    );
    Variable::poke(
      'Count.unassociatedLexemes', Lexeme::countUnassociated()
    );
    Variable::poke(
      'Count.unassociatedTrees', Tree::countUnassociated()
    );
    Variable::poke(
      'Count.ambiguousEntries', count(Entry::loadAmbiguous())
    );
    Variable::poke(
      'Count.entriesWithDefinitionsToStructure', count(Entry::loadWithDefinitionsToStructure())
    );
    Variable::poke(
      'Count.entriesWithoutMainLexemes', count(Entry::loadWithoutMainLexemes())
    );
    Variable::poke(
      'Count.lexemesWithoutAccent', Model::factory('Lexeme')->where('consistentAccent', 0)->count()
    );
    Variable::poke(
      'Count.ambiguousLexemes', count(Lexeme::loadAmbiguous())
    );
    Variable::poke(
      'Count.temporaryLexemes', Model::factory('Lexeme')->where('modelType', 'T')->count()
    );
    Variable::poke(
      'Count.staleParadigms', Lexeme::countStaleParadigms()
    );
    Variable::poke(
      'Count.treeMentions', Model::factory('Mention')->where('objectType', Mention::TYPE_TREE)->count()
    );
    Variable::poke(
      'Count.entriesWithMultipleMainLexemes', Entry::loadWithMultipleMainLexemes()
    );

    foreach (Config::TAG_REPORTS as $value) {
      $t = Tag::get_by_value($value);
      if ($t) {
        $count = ObjectTag::count_by_objectType_tagId(ObjectTag::TYPE_DEFINITION, $t->id);
        Variable::poke("Count.tag.{$t->id}", $count);
      } else {
        print "No tag found by value [{$value}], please check Config.php::TAG_REPORTS.\n";
      }
    }
  }

  static function redirect($location) {
    // Fix an Android issue with redirects caused by diacritics
    $location = str_replace(
      ['ă', 'â', 'î', 'ș', 'ț', 'Ă', 'Â', 'Î', 'Ș', 'Ț'],
      ['%C4%83', '%C3%A2', '%C3%AE', '%C8%99', '%C8%9B',
       '%C4%82', '%C3%82', '%C3%8E', '%C8%98', '%C8%9A'], $location);
    FlashMessage::saveToSession();
    header("HTTP/1.1 301 Moved Permanently");
    header("Location: $location");
    exit;
  }

  static function redirectToRoute($route) {
    self::redirect(Router::link($route));
  }

  static function redirectToHome() {
    self::redirect(Config::URL_PREFIX);
  }

  // Redirects to the same page, stripping any GET parameters but preserving
  // any slash-delimited arguments.
  static function redirectToSelf() {
    $uri = $_SERVER['REQUEST_URI'];
    $path = parse_url($uri, PHP_URL_PATH);
    self::redirect($path);
  }

  static function assertNotLoggedIn() {
    if (User::getActive()) {
      Util::redirectToHome();
    }
  }

  static function isPrivateMode() {
    return Config::GLOBAL_PRIVATE_MODE ||
      Session::userPrefers(Preferences::PRIVATE_MODE);
  }

  // Any part of the program can call us to indicate that third party banners
  // should not be displayed (e.g. search.php if it detects adult definitions).
  // Then templates will call Util::isBannerVisible() at the end.
  static function hideThirdPartyBanners() {
    self::$hiddenThirdPartyBanners = true;
  }

  static function isBannerVisible() {
    $user = User::getActive();

    // cases that require us to block all banners
    if (!Config::SKIN_BANNER ||                   // disabled by config file
        User::can(User::PRIV_ANY) ||              // user is privileged
        ($user && $user->noAdsUntil > time())) {  // user is a donor
      return false;
    }

    // cases that require us to block third-party banners
    $isThirdPartyBanner = !in_array(Config::BANNER_TYPE, ['revive', 'fake', 'none']);

    if ($isThirdPartyBanner &&
        (self::$hiddenThirdPartyBanners ||  // some other code requested no 3rd party banners
         self::isPrivateMode())) {          // in private mode
      return false;
    }

    return true;
  }

  /**
   * Calculates percentage
   * @param integer $number Amount of something processed
   * @param integer $total The total of something
   * @param integer $decimals Decimal rounding unit
   *
   * @return float
   */
  static function percentageOf($number, $total, $decimals = 2) {
    return round($number / $total * 100, $decimals);
  }

  /**
   * Interleaves A = (a_1, a_2, ..., a_n) and B = (b_1, b_2, ..., b_n) to obtain
   * (a_1, b_1, a_2, b_2, ..., a_n, b_n).
   * Checks that A and B have equal numbers of elements or A has one mre.
   **/
  static function interleaveArrays($a, $b) {
    if ((count($a) != count($b)) &&
        (count($a) != count($b) + 1)) {
      throw new Exception(
        sprintf('Cannnot interleave arrays of sizes %s and %s',
                count($a), count($b))
      );
    }

    $result = [];
    foreach ($a as $i => $elem) {
      $result[] = $elem;
      if ($i < count($b)) {
        $result[] = $b[$i];
      }
    }

    return $result;
  }
}
