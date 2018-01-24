<?php
/**
 * An abstract class defining methods common to all ads modules.
**/
abstract class AdsModule {
  // Return an array of (name, value) variables that define an ad.
  // The caller is responsible for passing these on to Smarty, then including
  // the corresponding Smarty template.
  // Returns null if the implementing class cannot serve a relevant ad.
  abstract function run($lexemes, $definitions);

  static function runAllModules($lexemes, $definitions) {
    $adsModules = Config::get('global.adsModulesH');
    if ($adsModules) {
      foreach ($adsModules as $adsModule) {
        require_once Core::getRootPath() . "phplib/ads/{$adsModule}/{$adsModule}AdsModule.php";
        $className = ucfirst($adsModule) . 'AdsModule';
        $module = new $className;
        $result = $module->run(empty($lexemes) ? null : $lexemes, empty($definitions) ? null : $definitions);
        if ($result) {
          SmartyWrap::assign('adsProvider', $adsModule);
          SmartyWrap::assign('adsProviderParams', $result);
          break;
        }
      }
    }
  }
}
