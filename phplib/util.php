<?php

util_init();

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

function util_init() {
  mb_internal_encoding("UTF-8");
  setlocale(LC_ALL, "ro_RO.utf8");

  spl_autoload_register(); //clears the autoload stack
  spl_autoload_register("autoloadLibClass", false, true);
  spl_autoload_register("autoloadModelsClass", false, true);

  util_defineRootPath();
  util_defineWwwRoot();
  util_requireOtherFiles();
  DB::init();
  Session::init(); // init Session before SmartyWrap: SmartyWrap caches the person's nickname.
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

function util_suggestNoBanner() {
  if (isset($_SERVER['REQUEST_URI']) && preg_match('/(masturba|fute)/', $_SERVER['REQUEST_URI'])) {
    return true; // No banners on certain obscene pages
  }
  if (Session::getUser() && Session::getUser()->noAdsUntil > time()) {
    return true; // User is an active donor
  }
  return false;
}
