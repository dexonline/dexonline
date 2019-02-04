<?php

class Core {

  const AUTOLOAD_PATHS = [
    'lib',
    'lib/models',
    'lib/htmlize',
    'lib/parser',
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
  }

}

Core::init();
