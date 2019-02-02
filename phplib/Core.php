<?php

class Core {

  private static $wwwRoot;
  private static $tempPath;

  const AUTOLOAD_PATHS = [
    'phplib',
    'phplib/models',
    'phplib/htmlize',
    'phplib/parser',
  ];

  static function autoload($className) {
    foreach (self::AUTOLOAD_PATHS as $path) {
      $filename = Config::ROOT . $path . '/' . $className . '.php';
      if (file_exists($filename)) {
        require_once $filename;
        return;
      }
    }
  }

  static function init() {
    require_once __DIR__ . '/../Config.php';

    spl_autoload_register(); // clear the autoload stack
    spl_autoload_register('Core::autoload', false, true);

    self::defineWwwRoot();
    self::defineTempPath();
    self::requireOtherFiles();
    DB::init();
    Session::init(); // init Session before SmartyWrap: SmartyWrap caches the person's nickname.
    if (!Request::isAjax()) {
      FlashMessage::restoreFromSession();
    }
    SmartyWrap::init();
    LocaleUtil::init();
    DebugInfo::init();
    Plugin::init();
    if (Request::isWeb() && Config::MAINTENANCE_MODE) {
      SmartyWrap::display('maintenance.tpl', true);
      exit;
    }
    self::initAdvancedSearchPreference();
  }

  static function initAdvancedSearchPreference() {
    $advancedSearch = Session::userPrefers(Preferences::SHOW_ADVANCED);
    SmartyWrap::assign('advancedSearch', $advancedSearch);
  }

  /**
   * Returns the home page URL path.
   * Algorithm: compare the current URL with the absolute file name.
   * Travel up both paths until we encounter /wwwbase/ in the file name.
   **/
  static function defineWwwRoot() {
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
    self::$wwwRoot = $result;
  }

  /**
   * Returns the root URL for dexonline (since it could be running in a subdirectory).
   */
  static function getWwwRoot() {
    return self::$wwwRoot;
  }

  static function getImgRoot() {
    return self::getWwwRoot() . 'img';
  }

  static function getCssRoot() {
    return self::getWwwRoot() . 'css';
  }

  static function requireOtherFiles() {
    $tp = __DIR__ . '/third-party';
    require_once "{$tp}/smarty-3.1.30/Smarty.class.php";
    require_once "{$tp}/idiorm/idiorm.php";
    require_once "{$tp}/idiorm/paris.php";
  }

  static function getTempPath() {
    return self::$tempPath;
  }

  static function defineTempPath() {
    $temp = Config::TEMP_DIR ?: sys_get_temp_dir();
    if (is_dir($temp) && is_writable($temp)) {
      self::$tempPath = $temp;
    } else {
      throw new Exception('Directorul temporar specificat nu poate fi accesat.');
    }
  }
}

Core::init();
