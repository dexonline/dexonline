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
  DB::init();
  Session::init();
  if (!util_isAjax()) {
    FlashMessage::restoreFromSession();
  }
  SmartyWrap::init();
  DebugInfo::init();
  if (util_isWebBasedScript() && Config::get('global.maintenanceMode')) {
    SmartyWrap::display('maintenance.tpl', true);
    exit;
  }
  util_initAdvancedSearchPreference();
}

function util_initAdvancedSearchPreference() {
  $advancedSearch = Session::userPrefers(Preferences::SHOW_ADVANCED);
  SmartyWrap::assign('advancedSearch', $advancedSearch);
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

function util_isAjax() {
  return isset($_SERVER['REQUEST_URI']) &&
    StringUtil::startsWith($_SERVER['REQUEST_URI'], util_getWwwRoot() . 'ajax/');
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

function util_assertNotMirror() {
  if (Config::get('global.mirror')) {
    SmartyWrap::display('mirror_message.tpl');
    exit;
  }
}

function util_assertNotLoggedIn() {
  if (Session::getUser()) {
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
function util_redirectToFriendlyUrl($cuv, $entryId, $lexemId, $sourceUrlName, $text, $showParadigm,
                                    $format, $all) {
  if (strpos($_SERVER['REQUEST_URI'], '/search.php?') === false) {
    return;    // The url is already friendly.
  }

  if ($format['name'] != 'html') {
    return;
  }

  $cuv = urlencode($cuv);
  $sourceUrlName = urlencode($sourceUrlName);

  $sourcePart = $sourceUrlName ? "-{$sourceUrlName}" : '';
  $paradigmPart = $showParadigm ? '/paradigma' : '';
  $allPart = ($all && !$showParadigm) ? '/expandat' : '';

  if ($text) {
    $url = "text{$sourcePart}/{$cuv}";
  } else if ($entryId) {
    $e = Entry::get_by_id($entryId);
    if (!$e) {
      util_redirect(util_getWwwRoot());
    }
    $short = $e->getShortDescription();
    $url = "intrare{$sourcePart}/{$short}/{$e->id}/{$paradigmPart}";
  } else if ($lexemId) {
    $l = Lexem::get_by_id($lexemId);
    if (!$l) {
      util_redirect(util_getWwwRoot());
    }
    $url = "lexem/{$l->formNoAccent}/{$l->id}";
  } else {
    $url = "definitie{$sourcePart}/{$cuv}{$paradigmPart}";
  }

  util_redirect(util_getWwwRoot() . $url . $allPart);
}

function util_suggestNoBanner() {
  if (isset($_SERVER['REQUEST_URI']) && preg_match('/(masturba|fute)/', $_SERVER['REQUEST_URI'])) {
    return true; // No banners on certain obscene pages
  }
  if (Session::getUser() && Session::getUser()->noAdsUntil > time()) {
    return true; // User is an active donor
  }
  return false;
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
    'Count.ambiguousEntries',
    count(Entry::loadAmbiguous())
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
