<?php
mb_internal_encoding("UTF-8");
setlocale(LC_ALL, "ro_RO.utf8");

spl_autoload_register(); //clears the autoload stack

function autoloadLibClass($className) {
  $filename = util_getRootPath() . 'phplib' . DIRECTORY_SEPARATOR . $className . '.php';
  if (file_exists($filename)) {
    require_once($filename);
  }
}

function autoloadModelsClass($className) {
  $filename = util_getRootPath() . 'phplib' . DIRECTORY_SEPARATOR . 'models' . DIRECTORY_SEPARATOR . $className . '.php';
  if (file_exists($filename)) {
    require_once($filename);
  }
}

spl_autoload_register("autoloadLibClass", false, true);
spl_autoload_register("autoloadModelsClass", false, true);

util_initEverything();

function util_initEverything() {
  // smarty < session_start/end : smarty caches the person's nickname.
  util_defineRootPath();
  util_defineWwwRoot();
  util_requireOtherFiles();
  util_defineConstants();
  db_init();
  session_init();
  mc_init();
  FlashMessage::restoreFromSession();
  SmartyWrap::init();
  DebugInfo::init();
  if (util_isWebBasedScript() && Config::get('global.maintenanceMode')) {
    SmartyWrap::display('maintenance.tpl', true);
    exit;
  }
}

function util_defineRootPath() {
  $ds = DIRECTORY_SEPARATOR;
  $fileName = realpath($_SERVER['SCRIPT_FILENAME']);
  $pos = strrpos($fileName, "{$ds}wwwbase{$ds}");
  // Some offline scripts, such as dict-server.php, run from the tools or phplib directories.
  if ($pos === FALSE) {
    $pos = strrpos($fileName, "{$ds}tools{$ds}");
  }
  if ($pos === FALSE) {
    $pos = strrpos($fileName, "{$ds}phplib{$ds}");
  }
  if ($pos === FALSE) {
    $pos = strrpos($fileName, "{$ds}app{$ds}");
  }
  $GLOBALS['util_rootPath'] = substr($fileName, 0, $pos + 1);
}

/**
 * Returns the absolute path of the Hasdeu folder in the file system.
 */
function util_getRootPath() {
  return $GLOBALS['util_rootPath'];
}

/**
 * Returns the home page URL path.
 * Algorithm: compare the current URL with the absolute file name.
 * Travel up both paths until we encounter /wwwbase/ in the file name.
 **/
function util_defineWwwRoot() {
  $scriptName = $_SERVER['SCRIPT_NAME'];
  $fileName = realpath($_SERVER['SCRIPT_FILENAME']);
  $pos = strrpos($fileName, '/wwwbase/');
  
  if ($pos === false) {
    $result = '/';     // This shouldn't be the case
  } else {
    $tail = substr($fileName, $pos + strlen('/wwwbase/'));
    $lenTail = strlen($tail);
    if ($tail == substr($scriptName, -$lenTail)) {
      $result = substr($scriptName, 0, -$lenTail);
    } else {
      $result = '/';
    }
  }
  $GLOBALS['util_wwwRoot'] = $result;
}

/**
 * Returns the URL for Hasdeu's root on the webserver (since Hasdeu could be
 * running in a subdirectory on the server).
 */
function util_getWwwRoot() {
  return $GLOBALS['util_wwwRoot'];
}

function util_getImgRoot() {
  return util_getWwwRoot() . "img"; 
}

function util_getCssRoot() {
  return util_getWwwRoot() . "css"; 
}

function util_requireOtherFiles() {
  $root = util_getRootPath();
  require_once(StringUtil::portable("$root/phplib/third-party/smarty/Smarty.class.php"));
  require_once(StringUtil::portable("$root/phplib/third-party/idiorm/idiorm.php"));
  require_once(StringUtil::portable("$root/phplib/third-party/idiorm/paris.php"));
  require_once(StringUtil::portable("$root/phplib/db.php"));
  require_once(StringUtil::portable("$root/phplib/session.php"));
  require_once(StringUtil::portable("$root/phplib/memcache.php"));
}

function util_defineConstants() {
  define("DEFAULT_SOURCE", "none");

  // Constants for the user top.
  // Sorting criteria, default is number of chars
  define("CRIT_CHARS", 1);
  define("CRIT_WORDS", 2);
  define("CRIT_NICK",  3);
  define("CRIT_DATE",  4);

  define("ABBREV_NOT_REVIEWED", 0);
  define("ABBREV_AMBIGUOUS", 1);
  define("ABBREV_REVIEW_COMPLETE", 2);

  define("MAX_RECENT_LINKS", 20);
  
  define("INFINITY", 1000000000);

  define('UNKNOWN_ACCENT_SHIFT', 100);
  define('NO_ACCENT_SHIFT', 101);

  define('LOCK_FULL_TEXT_INDEX', 'full_text_index');
  define('CURL_COOKIE_FILE', '/dexonline_cookie.txt');

#TODO clean up here
  define('PRIV_ADMIN', 0x01);
  define('PRIV_LOC', 0x02);
  define('PRIV_EDIT', 0x04);
  define('PRIV_GUIDE', 0x08);
  define('PRIV_WOTD', 0x10);
  define('PRIV_SUPER', 0x20);
  define('PRIV_STRUCT', 0x40);
  define('PRIV_VISUAL', 0x80);

  $GLOBALS['PRIV_MAP_NAMES'] = [
    PRIV_ADMIN  => 'Administrator',
    PRIV_LOC    => 'Moderator LOC',
    PRIV_EDIT   => 'Moderator',
    PRIV_GUIDE  => 'Editor al ghidului de exprimare',
    PRIV_WOTD   =>' Editor al cuvântului zilei',
    PRIV_SUPER  => 'Utilizator privilegiat',
    PRIV_STRUCT => '«Structurist» al definițiilor',
    PRIV_VISUAL => 'Moderator imagini definiții'
  ];

  $GLOBALS['PRIV_NAMES'] = array_values($GLOBALS['PRIV_MAP_NAMES']);
  define('NUM_PRIVILEGES', count($GLOBALS['PRIV_NAMES']));

  define('PRIV_VIEW_HIDDEN', PRIV_ADMIN);
  define('PRIV_ANY', (1 << NUM_PRIVILEGES) - 1);

  //Limits
  define('DEFAULT_LIMIT_FULLTEXT', 500);
  define('LIMIT_FULLTEXT_DISPLAY', Config::get('limits.limitFulltextSearch', DEFAULT_LIMIT_FULLTEXT));
  define('PREVIEW_LIMIT', 20);
}

function util_randomCapitalLetterString($length) {
  $result = '';
  for ($i = 0; $i < $length; $i++) {
    $result .= chr(rand(0, 25) + ord("A"));
  }
  return $result;
}

/**
 * Returns true if this script is running in response to a web request, false
 * otherwise.
 */
function util_isWebBasedScript() {
  return isset($_SERVER['REMOTE_ADDR']);
}

function util_getFullServerUrl() {
  $host = $_SERVER['SERVER_NAME'];
  $port =  $_SERVER['SERVER_PORT'];
  $path = util_getWwwRoot();

  return ($port == '80') ? "http://$host$path" : "http://$host:$port$path";
}

function util_formatNumber($n, $decimals) {
  return number_format($n, $decimals, ',', '.');
}

function util_redirect($location) {
  // Fix an Android issue with redirects caused by diacritics
  $location = str_replace(array('ă', 'â', 'î', 'ș', 'ț', 'Ă', 'Â', 'Î', 'Ș', 'Ț'),
                          array('%C4%83', '%C3%A2', '%C3%AE', '%C8%99', '%C8%9B', '%C4%82', '%C3%82', '%C3%8E', '%C8%98', '%C8%9A'),
                          $location);
  FlashMessage::saveToSession();
  header("HTTP/1.1 301 Moved Permanently");
  header("Location: $location");
  exit;
}

/**
 * Redirect to the same URL while removing empty GET parameters.
 */
function util_hideEmptyRequestParameters() {
  $needToRedirect = false;
  $newQueryString = '';

  $params = array_keys($_GET);
  foreach ($params as $param) {
    $value = $_GET[$param];
    if ($value) {
      if ($newQueryString) {
        $newQueryString .= "&";
      } else {
        $newQueryString = "?";
      }
      $newQueryString .= "$param=$value";
    } else {
      $needToRedirect = true;
    }
  }

  if ($needToRedirect) {
    util_redirect($_SERVER['PHP_SELF'] . $newQueryString);
  }
}

function util_assertModerator($type) {
  if (!util_isModerator($type)) {
    FlashMessage::add('Nu aveți privilegii suficiente pentru a accesa această pagină.');
    util_redirect(util_getWwwRoot());
  }
}

function util_isModerator($type) {
  // Check the actual database, not the session user
  $userId = session_getUserId();
  $user = $userId ? User::get_by_id($userId) : null;
  return $user ? ($user->moderator & $type) : false;
}

function util_assertNotMirror() {
  if (Config::get('global.mirror')) {
    SmartyWrap::display('mirror_message.tpl');
    exit;
  }
}

function util_assertNotLoggedIn() {
  if (session_getUser()) {
    util_redirect(util_getWwwRoot());
  }
}

// Assumes the arrays are sorted and do not contain duplicates.
function util_intersectArrays($a, $b) {
  $i = 0;
  $j = 0;
  $countA = count($a);
  $countB = count($b);
  $result = array();

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
function util_findSnippet($p) {
  $result = INFINITY;
  $n = count($p);
  $indexes = array_pad(array(), $n, 0);
  $done = false;

  while (!$done) {
    $min = INFINITY;
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

function util_deleteFile($fileName) {
  if (file_exists($fileName)) {
    unlink($fileName);
  }
}

/**
 * Search engine friendly URLs used for the search page:
 * 1) https://dexonline.ro/definitie[-<sursa>]/<cuvânt>[/<defId>][/paradigma]
 * 2) https://dexonline.ro/lexem[-<sursa>]/<cuvânt>[/<lexemId>][/paradigma]
 * 3) https://dexonline.ro/text[-<sursa>]/<text>
 * Links of the old form (search.php?...) can only come via the search form and should not contain lexemId / definitionId.
 */
function util_redirectToFriendlyUrl($cuv, $lexemId, $sourceUrlName, $text, $showParadigm, $xml, $all) {
  if (strpos($_SERVER['REQUEST_URI'], '/search.php?') === false) {
    return;    // The url is already friendly.
  }

  if ($xml) {
    return;
  }

  $cuv = urlencode($cuv);
  $sourceUrlName = urlencode($sourceUrlName);

  $sourcePart = $sourceUrlName ? "-{$sourceUrlName}" : '';
  $paradigmPart = $showParadigm ? '/paradigma' : '';
  $allPart = ($all && !$showParadigm) ? '/expandat' : '';

  if ($text) {
    $url = "text{$sourcePart}/{$cuv}";
  } else if ($lexemId) {
    $l = Lexem::get_by_id($lexemId);
    if (!$l) {
      util_redirect(util_getWwwRoot());
    }
    $url = "lexem{$sourcePart}/{$l->formNoAccent}/{$l->id}/{$paradigmPart}";
  } else {
    $url = "definitie{$sourcePart}/{$cuv}{$paradigmPart}";
  }

  util_redirect(util_getWwwRoot() . $url . $allPart);
}

function util_suggestNoBanner() {
  if (isset($_SERVER['REQUEST_URI']) && preg_match('/(masturba|fute)/', $_SERVER['REQUEST_URI'])) {
    return true; // No banners on certain obscene pages
  }
  if (session_getUser() && session_getUser()->noAdsUntil > time()) {
    return true; // User is an active donor
  }
  return false;
}

// Returns a pair of ($data, $httpCode)
function util_fetchUrl($url) {
  $ch = curl_init($url);
  curl_setopt($ch, CURLOPT_HEADER, 0);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true); // follow redirects
  $data = curl_exec($ch);
  $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
  curl_close($ch);
  return array($data, $httpCode);
}

function util_makePostRequest($url, $data, $useCookies = false) {
  $ch = curl_init($url);
  if ($useCookies) {
    curl_setopt($ch, CURLOPT_COOKIEFILE, Config::get('global.tempDir') . CURL_COOKIE_FILE);
    curl_setopt($ch, CURLOPT_COOKIEJAR, Config::get('global.tempDir') . CURL_COOKIE_FILE);
  }
  curl_setopt($ch, CURLOPT_POST, true);
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
  curl_close($ch);
  return $result;
}

/* Returns $obj->$prop for every $obj in $a */
function util_objectProperty($a, $prop) {
  $results = [];
  foreach ($a as $obj) {
    $results[] = $obj->$prop;
  }
  return $results;
}

/* Returns an array of { $v -> true } for every value $v in $a */
function util_makeSet($a) {
  $result = array();
  if ($a) {
    foreach ($a as $v) {
      $result[$v] = true;
    }
  }
  return $result;
}

function util_recount() {
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

?>
