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
  if (!Request::isAjax()) {
    FlashMessage::restoreFromSession();
  }
  SmartyWrap::init();
  DebugInfo::init();
  if (Request::isWeb() && Config::get('global.maintenanceMode')) {
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
 * Returns the absolute path of the dexonline folder in the file system.
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
 * Returns the root URL for dexonline (since it could be running in a subdirectory).
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
