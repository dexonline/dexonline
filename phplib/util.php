<?php
util_initEverything();

function util_initEverything() {
  // smarty < session_start/end : smarty caches the person's nickname.
  util_configurePhp();
  util_defineRootPath();
  util_defineWwwRoot();
  // At this point the server preferences are loaded (when
  // util_requireOtherFiles() includes serverPreferences.php)
  util_requireOtherFiles();
  debug_init();
  util_defineConstants();
  $GLOBALS['util_db'] = db_init(pref_getDbHost(), pref_getDbUser(),
				pref_getDbPassword(), pref_getDbDatabase());
  session_init();
  text_init();

  if (util_isWebBasedScript()) {
    smarty_init();
  }
}

function util_ConfigurePhp() {
  error_reporting(E_ALL);
  ini_set("display_errors", "On");
  // This cannot be configured here and has to be configured in
  // wwwbase/.htaccess
  // ini_set("magic_quotes_gpc", "Off");
  ini_set("session.use_trans_sid", "0");
}

function util_defineRootPath() {
  $fileName = realpath($_SERVER['SCRIPT_FILENAME']);
  $pos = strrpos($fileName, '/wwwbase/');
  if ($pos === FALSE) {
    // Some offline scripts, such as dict-server.php, run from the tools
    // directory.
    $pos = strrpos($fileName, '/tools/');
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
  require_once("$root/phplib/db.php");
  require_once("$root/phplib/debugInfo.php");
  require_once("$root/phplib/fileCache.php");
  require_once("$root/phplib/intArray.php");
  require_once("$root/phplib/lock.php");
  require_once("$root/phplib/logging.php");
  require_once("$root/phplib/modelObjects.php");
  require_once("$root/phplib/os.php");
  require_once("$root/phplib/serverPreferences.php");
  require_once("$root/phplib/session.php");
  require_once("$root/phplib/smarty.php");
  require_once("$root/phplib/textProcessing.php");
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

  define("MAX_RECENT_LINKS", 20);
  
  $GLOBALS['wordStatuses'] = array(ST_ACTIVE => "Activă",
                                   ST_PENDING => "Temporară",
                                   ST_DELETED => "Ștearsă");

  define("SEARCH_REGEXP", 0);
  define("SEARCH_MULTIWORD", 1);
  define("SEARCH_WORDLIST", 2);
  define("SEARCH_APPROXIMATE", 3);
  define("SEARCH_DEF_ID", 4);
  define("SEARCH_LEXEM_ID", 5);
  define("SEARCH_FULL_TEXT", 6);

  define("INFINITY", 1000000000);

  define('INFL_M_OFFSET', 1);
  define('INFL_F_OFFSET', 9);
  define('INFL_N_OFFSET', 17);
  define('INFL_A_OFFSET', 25);
  define('INFL_P_OFFSET', 41);
  define('INFL_V_OFFSET', 49);
  define('INFL_V_PREZ_OFFSET', 54);

  define('UNKNOWN_ACCENT_SHIFT', 100);
  define('NO_ACCENT_SHIFT', 101);

  define('LOCK_FULL_TEXT_INDEX', 'full_text_index');
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

function util_assertModeratorStatus() {
  if (!session_userIsModerator()) {
    smarty_assign('errorMessage', 'Nu aveți acces ca moderator! ' .
                  'Vă rugăm să vă <a href="' . util_getWwwRoot() . 'login.php">autentificați</a> ' .
                  'mai întâi.');
    smarty_displayWithoutSkin('common/errorMessage.ihtml');
    exit;
  }
}

function util_assertFlexModeratorStatus() {
  if (!session_userIsFlexModerator()) {
    smarty_assign('errorMessage', 'Dex Flex este un proiect în lucru. ' .
                  'Momentan, lista utilizatorilor care au acces este foarte ' .
                  'limitată.');
    smarty_displayWithoutSkin('common/errorMessage.ihtml');
    exit;
  }
}

function util_assertNotMirror() {
  if (pref_isMirror()) {
    smarty_displayWithoutSkin('common/mirror_message.ihtml');
    exit;
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
 * 1) http://dexonline.ro/definitie[-<sursa>]/<cuvânt>[/paradigma]
 * 2) http://dexonline.ro/lexem[-<sursa>]/<lexemId>[/paradigma]
 * 3) http://dexonline.ro/text[-<sursa>]/<text>
 */
function util_redirectToFriendlyUrl($cuv, $lexemId, $sourceUrlName, $text, $showParadigm) {
  if (strpos($_SERVER['REQUEST_URI'], '/search.php?') === false) {
    return;    // The url is already friendly.
  }

  $cuv = urlencode($cuv);
  $sourceUrlName = urlencode($sourceUrlName);

  $sourcePart = $sourceUrlName ? "-{$sourceUrlName}" : '';
  $paradigmPart = $showParadigm ? '/paradigma' : '';

  if ($text) {
    $url = "text{$sourcePart}/{$cuv}";
  } else if ($lexemId) {
    $url = "lexem{$sourcePart}/{$lexemId}{$paradigmPart}";
  } else {
    $url = "definitie{$sourcePart}/{$cuv}{$paradigmPart}";
  }

  util_redirect(util_getWwwRoot() . $url);
  exit();
}

?>
