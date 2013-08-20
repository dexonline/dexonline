<?php
mb_internal_encoding("UTF-8");
setlocale(LC_ALL, "ro_RO.utf8");

spl_autoload_register(); //clears the autoload stack

function autoloadLibClass($className) {
  $filename = util_getRootPath()."phplib/{$className}.php";
  if (file_exists($filename))
    require_once($filename);
}

function autoloadModelsClass($className) {
  $filename = util_getRootPath()."phplib/models/{$className}.php";
  if (file_exists($filename))
    require_once($filename);
}

spl_autoload_register("autoloadLibClass", false, true);
spl_autoload_register("autoloadModelsClass", false, true);

util_initEverything();

function util_initEverything() {
  // smarty < session_start/end : smarty caches the person's nickname.
  util_defineRootPath();
  util_defineWwwRoot();
  // At this point the server preferences are loaded (when
  // util_requireOtherFiles() includes serverPreferences.php)
  util_requireOtherFiles();
  util_defineConstants();
  db_init();
  session_init();
  mc_init();
  FlashMessage::restoreFromSession();
  SmartyWrap::init();
}

function util_defineRootPath() {
  $fileName = realpath($_SERVER['SCRIPT_FILENAME']);
  $pos = strrpos($fileName, '/wwwbase/');
  // Some offline scripts, such as dict-server.php, run from the tools or phplib directories.
  if ($pos === FALSE) {
    $pos = strrpos($fileName, '/tools/');
  }
  if ($pos === FALSE) {
    $pos = strrpos($fileName, '/phplib/');
  }
  $GLOBALS['util_rootPath'] = substr($fileName, 0, $pos + 1);
}

/**
 * Returns the absolute path of the Hasdeu folder in the file system.
 */
function util_getRootPath() {
  return $GLOBALS['util_rootPath'];
}

function util_defineWwwRoot() {
  $fileName = $_SERVER['SCRIPT_NAME'];
  $pos = strrpos($fileName, '/wwwbase/');
  
  if ($pos == FALSE) {
    $GLOBALS['util_wwwRoot'] = '/';
  } else {
    $GLOBALS['util_wwwRoot'] = substr($fileName, 0, $pos) . '/wwwbase/';
  }
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
  return util_getWwwRoot() . "styles"; 
}

function util_requireOtherFiles() {
  $root = util_getRootPath();
  require_once("$root/phplib/smarty/Smarty.class.php");
  require_once("$root/phplib/idiorm/idiorm.php");
  require_once("$root/phplib/idiorm/paris.php");
  require_once("$root/phplib/serverPreferences.php");
  require_once("$root/phplib/db.php");
  require_once("$root/phplib/logging.php");
  require_once("$root/phplib/session.php");
  require_once("$root/phplib/memcache.php");
}

function util_defineConstants() {
  define("DEFAULT_SOURCE", "none");

  // Constants for the user top.
  // Sorting criteria, default is number of chars
  define("CRIT_CHARS", 1);
  define("CRIT_WORDS", 2);
  define("CRIT_NICK",  3);
  define("CRIT_DATE",  4);

  define("ST_ACTIVE", 0);
  define("ST_PENDING", 1);
  define("ST_DELETED", 2);
  define("ST_HIDDEN", 3);

  define("ABBREV_NOT_REVIEWED", 0);
  define("ABBREV_AMBIGUOUS", 1);
  define("ABBREV_REVIEW_COMPLETE", 2);

  define("MAX_RECENT_LINKS", 20);
  
  $GLOBALS['wordStatuses'] = array(ST_ACTIVE  => "Activă",
                                   ST_PENDING => "Temporară",
                                   ST_DELETED => "Ștearsă",
                                   ST_HIDDEN  => "Ascunsă",
                             );

  define("SEARCH_REGEXP", 0);
  define("SEARCH_MULTIWORD", 1);
  define("SEARCH_INFLECTED", 2);
  define("SEARCH_APPROXIMATE", 3);
  define("SEARCH_DEF_ID", 4);
  define("SEARCH_LEXEM_ID", 5);
  define("SEARCH_FULL_TEXT", 6);

  define("INFINITY", 1000000000);

  define('UNKNOWN_ACCENT_SHIFT', 100);
  define('NO_ACCENT_SHIFT', 101);

  define('LOCK_FULL_TEXT_INDEX', 'full_text_index');

#TODO clean up here
  define('PRIV_ADMIN', 0x01);
  define('PRIV_LOC', 0x02);
  define('PRIV_EDIT', 0x04);
  define('PRIV_GUIDE', 0x08);
  define('PRIV_WOTD', 0x10);
  define('PRIV_SUPER', 0x20);
  define('PRIV_STRUCT', 0x40);
  define('PRIV_VIEW_HIDDEN', PRIV_ADMIN);
  define('NUM_PRIVILEGES', 7);
  $GLOBALS['PRIV_NAMES'] = array('Administrator', 'Moderator LOC', 'Moderator', 'Editor al ghidului de exprimare', 'Editor al cuvântului zilei',
                                 'Utilizator privilegiat', '«Structurist» al definițiilor');

  //Source 
  define('SOURCE_TYPE_HIDDEN', 3);
  define('SOURCE_TYPE_OFFICIAL', 2);
  define('SOURCE_TYPE_SPECIALIZED', 1);
  define('SOURCE_TYPE_UNOFFICIAL', 0);

  //Limits
  define('DEFAULT_LIMIT_FULLTEXT', 500);
  define('LIMIT_FULLTEXT_DISPLAY', pref_getSectionPreference('limits', 'limitFulltextSearch', DEFAULT_LIMIT_FULLTEXT));
}

function util_getAllStatuses() {
  return $GLOBALS['wordStatuses'];
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

function util_getRequestParameter($name) {
  return util_getRequestParameterWithDefault($name, NULL);
}

function util_getRequestParameterWithDefault($name, $default) {
  return array_key_exists($name, $_REQUEST) ? $_REQUEST[$name] : $default;
}

function util_getRequestIntParameter($name) {
  return util_getRequestIntParameterWithDefault($name, 0);
}

function util_getRequestIntParameterWithDefault($name, $default) {
  $string = util_getRequestParameter($name);
  return ($string == NULL) ? $default : (int)$string;
}

/* A boolean is a checkbox, a yes/no radio button pair, or any field that returns empty for false and non-empty for true. */
function util_getBoolean($name) {
  $v = util_getRequestParameter($name);
  return $v ? true : false;
}

function util_getRequestCheckboxArray($name, $separator) {
  $arr = util_getRequestParameter($name);
  return $arr ? implode($separator, $arr) : '';
}

/** Returns an array of values from a parameter in CSV format **/
function util_getRequestCsv($name) {
  $s = util_getRequestParameter($name);
  return $s ? explode(',', $s) : array();
}

function util_getUploadedFile($name) {
  return array_key_exists($name, $_FILES) ? $_FILES[$name] : null;
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

/*
 * Returns true if the user has an avatar image in wwwbase/img/user. If the $user argument is null, queries the currently logged in user.
 */
function util_userHasAvatar($user = null) {
  if (!$user) {
    $user = session_getUser();
  }
  return $user && file_exists(util_getRootPath() . "wwwbase/img/user/{$user->id}.jpg");
}

function util_assertNotMirror() {
  if (pref_isMirror()) {
    SmartyWrap::displayWithoutSkin('common/mirror_message.ihtml');
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
    $minArray = -1;
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

function util_enforceGzipEncoding() {
  $acceptEncoding = isset($_SERVER['HTTP_ACCEPT_ENCODING'])
    ? $_SERVER['HTTP_ACCEPT_ENCODING'] : "";
  if (strstr($acceptEncoding, "gzip") === FALSE) {
    header("HTTP/1.0 403 Forbidden");
    exit;
  }
}

function util_deleteFile($fileName) {
  if (file_exists($fileName)) {
    unlink($fileName);
  }
}

/**
 * Search engine friendly URLs used for the search page:
 * 1) http://dexonline.ro/definitie[-<sursa>]/<cuvânt>[/<defId>][/paradigma]
 * 2) http://dexonline.ro/lexem[-<sursa>]/<cuvânt>[/<lexemId>][/paradigma]
 * 3) http://dexonline.ro/text[-<sursa>]/<text>
 * Links of the old form (search.php?...) can only come via the search form and should not contain lexemId / definitionId.
 */
function util_redirectToFriendlyUrl($cuv, $lexemId, $sourceUrlName, $text, $showParadigm, $xml) {
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

  util_redirect(util_getWwwRoot() . $url);
}

/** Relaxed browser check. Currently checks for a few major browser. Can have false negatives, but (hopefully) no false positives. **/
function util_isDesktopBrowser() {
  if (!util_isWebBasedScript()) {
    return false;
  }
  $u = $_SERVER['HTTP_USER_AGENT'];
  return (strpos($u, 'Firefox') !== false) || (strpos($u, 'MSIE') !== false) || (strpos($u, 'Chrome') !== false) ||
    (strpos($u, 'Opera') !== false) || (strpos($u, 'Safari') !== false);
}

/** Keep this in sync with docs/.htaccess and wwwbase/.htaccess and with the Varnish configuration **/
function util_isMobile($userAgent = null) {
  if (!util_isWebBasedScript()) {
    return false;
  }
  if (!$userAgent) {
    $userAgent = $_SERVER['HTTP_USER_AGENT'];
  }
  return preg_match('/^(DoCoMo|J-PHONE|KDDI|UP.Browser|DDIPOCKET|.*iPhone.*|.*iPod.*|.*BlackBerry.*|.*Windows.CE.*|.*LG.*|.*HTC.*|.*MOT.*|.*Motorola.*|.*Nokia.*|.*Samsung.*|.*SonyEricsson.*|.*Palm.*|.*Symbian.*|.*Android.*)/i', $userAgent);
}

function util_suggestNoBanner() {
  return isset($_SERVER['REQUEST_URI']) ? preg_match('/(masturba|fute)/', $_SERVER['REQUEST_URI']) : 0;
}

function util_fetchUrl($url) {
  $ch = curl_init($url);
  curl_setopt($ch, CURLOPT_HEADER, 0);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  $result = curl_exec($ch);
  curl_close($ch);
  return $result;
}

function util_makePostRequest($url, $data, $useCookies = false) {
  $ch = curl_init($url);
  if ($useCookies) {
    curl_setopt($ch, CURLOPT_COOKIEFILE, self::$curlCookieFile);
    curl_setopt($ch, CURLOPT_COOKIEJAR, self::$curlCookieFile);
  }
  curl_setopt($ch, CURLOPT_POST, true);
  curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  curl_setopt($ch, CURLOPT_USERAGENT, 'dexonline.ro');
  $result = curl_exec($ch);
  curl_close($ch);
  return $result;
}

function util_print($var) {
  print "<pre>\n";
  print_r($var);
  print "</pre>\n";
}

/** Returns $obj->$prop for every $obj in $a **/
function util_objectProperty($a, $prop) {
  $results = array();
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

?>
