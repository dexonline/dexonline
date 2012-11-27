<?php

class SmartyWrap {
  private static $theSmarty = null;
  private static $cssFiles = array();
  private static $jsFiles = array();

  static function init($smartyClass) {
    require_once(pref_getSmartyClass());
    self::$theSmarty = new Smarty();
    self::$theSmarty->template_dir = util_getRootPath() . 'templates';
    self::$theSmarty->compile_dir = util_getRootPath() . 'templates_c';
    self::assign('wwwRoot', util_getWwwRoot());
    self::assign('imgRoot', util_getImgRoot());
    self::assign('sources', Model::factory('Source')->order_by_desc('isOfficial')->order_by_asc('displayOrder')->find_many());
    self::assign('sUser', session_getUser());
    self::assign('is_mirror', pref_isMirror());
    self::assign('nick', session_getUserNick());
    self::assign('contact_email', pref_getContactEmail());
    self::assign('hostedBy', pref_getHostedBy());
    self::assign('currentYear', date("Y"));
    self::assign('bannerType', pref_getServerPreference('bannerType'));
    self::assign('developmentMode', pref_getServerPreference('developmentMode'));
    self::assign('isMobile', util_isMobile());
    self::assign('suggestNoBanner', util_suggestNoBanner());
    self::assign('GLOBALS', $GLOBALS);
    self::registerFunction('getDebugInfo', 'SmartyWrap::function_getDebugInfo');
  }

  static function display() {
    print self::fetchSkin();
  }

  static function fetchSkin() {
    $skin = session_getSkin();
    self::addCss($skin);
    self::addJs('jquery', 'dex');

    // Set some skin variables based on the skin preferences in the config file.
    // Also assign some skin-specific variables so we don't compute them unless we need them
    $skinVariables = session_getSkinPreferences($skin);
    switch ($skin) {
    case 'zepu':
      $skinVariables['afterSearchBoxBanner'] = true;
      break;
    case 'polar':
      $wordCount = Definition::getWordCount();
      $wordCountRough = $wordCount - ($wordCount % 10000);
      self::assign('words_total', util_formatNumber($wordCount, 0));
      self::assign('words_rough', util_formatNumber($wordCountRough, 0));
      self::assign('words_last_month', util_formatNumber(Definition::getWordCountLastMonth(), 0));
      break;
    case 'mobile':
      self::assign('words_total', util_formatNumber(Definition::getWordCount(), 0));
      self::assign('words_last_month', util_formatNumber(Definition::getWordCountLastMonth(), 0));
      self::addJs('mobile');
      break;
    }
    self::assign('skinVariables', $skinVariables);

    self::registerOutputFilters();
    return self::fetch("$skin/pageLayout.ihtml");
  }

  static function displayCommonPageWithSkin($templateName) {
    print self::fetchCommonPageWithSkin($templateName);
  }

  static function fetchCommonPageWithSkin($templateName) {
    self::assign('contentTemplateName', "common/$templateName");  
    return self::fetchSkin();
  }

  static function displayPageWithSkin($templateName) {
    $skin = session_getSkin();
    self::assign('contentTemplateName', "$skin/$templateName");
    self::display();
  }

  static function displayWithoutSkin($templateName) {
    self::registerOutputFilters();
    print self::fetch($templateName);
  }

  static function displayAdminPage($templateName) {
    self::assign('templateName', $templateName);
    self::addCss('flex');
    self::addJs('dex', 'flex');
    print self::fetch('admin/pageLayout.ihtml');
  }

  static function fetch($templateName) {
    ksort(self::$cssFiles);
    ksort(self::$jsFiles);
    self::assign('cssFiles', self::$cssFiles);
    self::assign('jsFiles', self::$jsFiles);
    return self::$theSmarty->fetch($templateName);
  }

  static function assign($variable, $value) {
    self::$theSmarty->assign($variable, $value);
  }

  static function filter_display_st_cedilla_below($tpl_output, &$smarty) {
    $tpl_output = StringUtil::replace_st($tpl_output);
    return $tpl_output;
  }

  static function filter_display_old_orthography($tpl_output, &$smarty) {
    $tpl_output = StringUtil::replace_ai($tpl_output);
    return $tpl_output;
  }

  static function registerOutputFilters() {
    if (session_user_prefers('CEDILLA_BELOW')) {
      self::registerOutputFilter('self::filter_display_st_cedilla_below');
    }
    if (session_user_prefers('OLD_ORTHOGRAPHY')) {
      self::registerOutputFilter('self::filter_display_old_orthography');
    }
  }

  static function registerOutputFilter($smarty, $functionName) {
    if (method_exists(self::$theSmarty, 'registerFilter')) {
      // Smarty v3 syntax
      self::$theSmarty->registerFilter('output', $functionName);
    } else {
      self::$theSmarty->register_outputfilter($functionName);
    }
  }

  static function registerFunction($smartyTagName, $functionName) {
    if (method_exists(self::$theSmarty, 'registerPlugin')) {
      // Smarty v3 syntax
      self::$theSmarty->registerPlugin('function', $smartyTagName, $functionName);
    } else {
      self::$theSmarty->register_function($smartyTagName, $functionName);
    }
  }

  static function function_getDebugInfo($params, &$smarty) {
    return DebugInfo::getDebugInfo();
  }

  static function addCss(/* Variable-length argument list */) {
    // Note the priorities. This allows files to be added in any order, regardless of dependencies
    foreach (func_get_args() as $id) {
      switch($id) {
      case 'jquery_smoothness':  self::$cssFiles[1] = 'jquery-ui-1.8.5.custom.css'; break;
      case 'jqgrid':
        self::$cssFiles[2] = 'ui.jqgrid.css?v=1';
        self::$cssFiles[3] = 'jquery-ui-1.8.5.custom.css?v=1';
        break;
      case 'autocomplete':       self::$cssFiles[4] = 'jquery.autocomplete.css?v=1'; break;
      case 'elfinder':           self::$cssFiles[5] = 'elfinder.css'; break;
      case 'zepu':               self::$cssFiles[6] = 'zepu.css?v=44'; break;
      case 'polar':              self::$cssFiles[7] = 'polar.css?v=29'; break;
      case 'mobile':             self::$cssFiles[8] = 'mobile.css?v=14'; break;
      case 'flex':               self::$cssFiles[9] = 'flex.css?v=7'; break;
      case 'paradigm':           self::$cssFiles[10] = 'paradigm.css?v=1'; break;
      case 'hangman':            self::$cssFiles[11] = 'hangman.css?v=2'; break;
      case 'mill':               self::$cssFiles[12] = 'mill.css?v=1'; break;
      default:
        FlashMessage::add("Cannot load CSS file {$id}");
        util_redirect(util_getWwwRoot());
      }
    }
  }

  static function addJs(/* Variable-length argument list */) {
    // Note the priorities. This allows files to be added in any order, regardless of dependencies
    foreach (func_get_args() as $id) {
      switch($id) {
      case 'jquery':           self::$jsFiles[1] = 'jquery-1.7.1.min.js'; break; 
      case 'jqueryui':         self::$jsFiles[2] = 'jquery-ui-1.8.17.custom.min.js'; break;
      case 'jqgrid':
        self::$jsFiles[3] = 'grid.locale-en.js?v=1';
        self::$jsFiles[4] = 'jquery.datepicker.pack.js?v=1';
        self::$jsFiles[5] = 'jquery.jqGrid.min.js?v=1';
        self::$jsFiles[6] = 'jqgrid.init.js?v=3';
        break;
      case 'jqnotice':         self::$jsFiles[7] = 'jquery.notice.js'; break;
      case 'jqTableDnd':       self::$jsFiles[8] = 'jquery.tablednd_0_5.js?v=1'; break;
      case 'tablesorter':      self::$jsFiles[9] = 'jquery.tablesorter.min.js'; break;
      case 'pager':            self::$jsFiles[10] = 'jquery.tablesorter.pager.js'; break;
      case 'autocomplete':     self::$jsFiles[11] = 'jquery.autocomplete.pack.js'; break;
      case 'elfinder':         self::$jsFiles[12] = 'elfinder.min.js'; break; 
      case 'dex':              self::$jsFiles[13] = 'dex.js?v=21'; break;
      case 'flex':             self::$jsFiles[14] = 'flex.js?v=2'; break;
      case 'mobile':           self::$jsFiles[15] = 'mobile.js?v=2'; break;
      case 'hangman':          self::$jsFiles[16] = 'hangman.js?v=3'; break;
      case 'mill':             self::$jsFiles[17] = 'mill.js?v=2'; break;
      default:
        FlashMessage::add("Cannot load JS script {$id}");
        util_redirect(util_getWwwRoot());
      }
    }
  }
}

?>
