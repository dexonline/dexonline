<?php
define('ADODB_OUTP', 'debug_adodbHandler');
define('ADODB_ASSOC_CASE', 2);
$ADODB_ASSOC_CASE = 2;
util_initEverything();

function util_initEverything() {
  // smarty < session_start/end : smarty caches the person's nickname.
  util_defineRootPath();
  util_defineWwwRoot();
  // At this point the server preferences are loaded (when
  // util_requireOtherFiles() includes serverPreferences.php)
  util_requireOtherFiles();
  debug_init();
  util_defineConstants();
  $GLOBALS['util_db'] = db_init();
  session_init();
  text_init();
  mc_init();

  if (util_isWebBasedScript()) {
    smarty_init();
  }
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
  require_once("$root/phplib/textProcessing.php");
  require_once("$root/phplib/serverPreferences.php");
  require_once(pref_getServerPreference('adoDbClass'));
  require_once(pref_getServerPreference('adoDbActiveRecordClass'));
  require_once("$root/phplib/db.php");
  require_once("$root/phplib/debugInfo.php");
  require_once("$root/phplib/fileCache.php");
  require_once("$root/phplib/intArray.php");
  require_once("$root/phplib/lock.php");
  require_once("$root/phplib/logging.php");
  require_once("$root/phplib/modelObjects.php");
  require_once("$root/phplib/os.php");
  require_once("$root/phplib/session.php");
  require_once("$root/phplib/smarty.php");
  require_once("$root/phplib/memcache.php");
}

function util_defineConstants() {
  define("ONE_MONTH_IN_SECONDS", 30 * 86400);
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

  define("ABBREV_NOT_REVIEWED", 0);
  define("ABBREV_AMBIGUOUS", 1);
  define("ABBREV_REVIEW_COMPLETE", 2);

  define("MAX_RECENT_LINKS", 20);
  
  $GLOBALS['wordStatuses'] = array(ST_ACTIVE => "Activă",
                                   ST_PENDING => "Temporară",
                                   ST_DELETED => "Ștearsă");

  define("SEARCH_REGEXP", 0);
  define("SEARCH_MULTIWORD", 1);
  define("SEARCH_INFLECTED", 2);
  define("SEARCH_APPROXIMATE", 3);
  define("SEARCH_DEF_ID", 4);
  define("SEARCH_LEXEM_ID", 5);
  define("SEARCH_FULL_TEXT", 6);
  define("SEARCH_WOTD", 7);

  define("INFINITY", 1000000000);

  define('UNKNOWN_ACCENT_SHIFT', 100);
  define('NO_ACCENT_SHIFT', 101);

  define('LOCK_FULL_TEXT_INDEX', 'full_text_index');

  define('PRIV_ADMIN', 0x01);
  define('PRIV_LOC', 0x02);
  define('PRIV_EDIT', 0x04);
  define('PRIV_GUIDE', 0x08);
  define('PRIV_WOTD', 0x10);
  define('NUM_PRIVILEGES', 5);
  $GLOBALS['PRIV_NAMES'] = array('Administrator', 'Moderator LOC', 'Moderator', 'Editor al ghidului de exprimare', 'Editor al cuvântului zilei');
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

function util_getRequestCheckboxArray($name, $separator) {
  $arr = util_getRequestParameter($name);
  return $arr ? implode($arr, $separator) : '';
}

function util_redirect($location) {
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
    session_setFlash('Nu aveți privilegii suficiente pentru a accesa această pagină.');
    util_redirect(util_getWwwRoot());
  }
}

function util_isModerator($type) {
  // Check the actual database, not the session user
  $userId = session_getUserId();
  $user = $userId ? User::get("id = $userId") : null;
  return $user ? ($user->moderator & $type) : false;
}

function util_assertNotMirror() {
  if (pref_isMirror()) {
    smarty_displayWithoutSkin('common/mirror_message.ihtml');
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
function util_redirectToFriendlyUrl($cuv, $sourceUrlName, $text, $showParadigm) {
  if (strpos($_SERVER['REQUEST_URI'], '/search.php?') === false) {
    return;    // The url is already friendly.
  }

  $cuv = urlencode($cuv);
  $sourceUrlName = urlencode($sourceUrlName);

  $sourcePart = $sourceUrlName ? "-{$sourceUrlName}" : '';
  $paradigmPart = $showParadigm ? '/paradigma' : '';

  if ($text) {
    $url = "text{$sourcePart}/{$cuv}";
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

/** Keep this in sync with docs/.htaccess and wwwbase/.htaccess **/
function util_isMobile($userAgent = null) {
  if (!util_isWebBasedScript()) {
    return false;
  }
  if (!$userAgent) {
    $userAgent = $_SERVER['HTTP_USER_AGENT'];
  }
  return preg_match('/^(DoCoMo|J-PHONE|KDDI|UP.Browser|DDIPOCKET|.*iPhone.*|.*iPod.*|.*BlackBerry.*|.*Windows.CE.*|.*LG.*|.*HTC.*|.*MOT.*|.*Motorola.*|.*Nokia.*|.*Samsung.*|.*SonyEricsson.*|.*Palm.*|.*Symbian.*|.*Android.*)/i', $userAgent);
}

function util_fetchUrl($url) {
  $ch = curl_init($url);
  curl_setopt($ch, CURLOPT_HEADER, 0);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  $result = curl_exec($ch);
  curl_close($ch);
  return $result;
}

?>
