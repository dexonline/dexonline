<?php

class SmartyWrap {
  private static $theSmarty = null;
  private static $cssFiles = array();
  private static $jsFiles = array();

  static function init() {
    self::$theSmarty = new Smarty();
    self::$theSmarty->template_dir = util_getRootPath() . 'templates';
    self::$theSmarty->compile_dir = util_getRootPath() . 'templates_c';
    self::assign('wwwRoot', util_getWwwRoot());
    self::assign('imgRoot', util_getImgRoot());
    self::assign('sources', Model::factory('Source')->order_by_desc('isOfficial')->order_by_asc('displayOrder')->find_many());
    self::assign('sUser', session_getUser());
    self::assign('is_mirror', Config::get('global.mirror'));
    self::assign('nick', session_getUserNick());
    self::assign('contact_email', Config::get('global.contact'));
    self::assign('hostedBy', Config::get('global.hostedBy'));
    self::assign('currentYear', date("Y"));
    self::assign('bannerType', Config::get('global.bannerType'));
    self::assign('developmentMode', Config::get('global.developmentMode'));
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
    $skinVariables = Config::getSection("skin-{$skin}");
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
    self::addJs('dex', 'flex', 'jquery');
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
    if (session_user_prefers(Preferences::CEDILLA_BELOW)) {
      self::registerOutputFilter('SmartyWrap::filter_display_st_cedilla_below');
    }
    if (session_user_prefers(Preferences::OLD_ORTHOGRAPHY)) {
      self::registerOutputFilter('SmartyWrap::filter_display_old_orthography');
    }
  }

  static function registerOutputFilter($functionName) {
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
      case 'jqueryui':           self::$cssFiles[1] = 'lightness-1.10.3/jquery-ui-1.10.3.custom.min.css'; break;
      case 'jqgrid':             self::$cssFiles[2] = 'ui.jqgrid.css?v=3'; break;
      case 'elfinder':           self::$cssFiles[4] = 'elfinder/css/elfinder.min.css?v=2'; break;
      case 'windowEngine':       self::$cssFiles[5] = 'jquery-wm/main.css'; break;
      case 'zepu':               self::$cssFiles[6] = 'zepu.css?v=56'; break;
      case 'polar':              self::$cssFiles[7] = 'polar.css?v=31'; break;
      case 'mobile':             self::$cssFiles[8] = 'mobile.css?v=15'; break;
      case 'flex':               self::$cssFiles[9] = 'flex.css?v=10'; break;
      case 'paradigm':           self::$cssFiles[10] = 'paradigm.css?v=1'; break;
      case 'hangman':            self::$cssFiles[11] = 'hangman.css?v=3'; break;
      case 'mill':               self::$cssFiles[12] = 'mill.css?v=2'; break;
      case 'lexemEdit':          self::$cssFiles[13] = 'lexemEdit.css?v=5'; break;
      case 'jcrop':              self::$cssFiles[14] = 'jcrop/jquery.Jcrop.min.css?v=3'; break;
      case 'easyui':
        self::$cssFiles[15] = 'easyui/default/easyui.css?v=2';
        self::$cssFiles[16] = 'easyui/icon.css?v=2';
        break;
      case 'select2':            self::$cssFiles[17] = 'select2/select2.css?v=3'; break;
      case 'gallery':            
        self::$cssFiles[18] = 'colorbox/colorbox.css?v=1'; 
        self::$cssFiles[19] = 'visualDict.css';
        break;
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
      case 'jquery':           self::$jsFiles[1] = 'jquery-1.10.2.min.js'; break; 
      case 'jqueryui':         self::$jsFiles[2] = 'jquery-ui-1.10.3.custom.min.js'; break;
      case 'jqgrid':
        self::$jsFiles[3] = 'grid.locale-en.js?v=2';
        self::$jsFiles[4] = 'jquery.jqGrid.min.js?v=3';
        break;
      case 'jqnotice':         self::$jsFiles[5] = 'jquery.notice.js'; break;
      case 'jqTableDnd':       self::$jsFiles[6] = 'jquery.tablednd.0.7.min.js?v=1'; break;
      case 'tablesorter':      self::$jsFiles[7] = 'jquery.tablesorter.min.js?v=2'; break;
      case 'pager':            self::$jsFiles[8] = 'jquery.tablesorter.pager.js'; break;
      case 'elfinder':         self::$jsFiles[9] = 'elfinder.min.js?v=1'; break; 
      case 'windowEngine':     self::$jsFiles[10] = 'jquery-wm.js'; break;
      case 'cookie':           self::$jsFiles[11] = 'jquery.cookie.js?v=1'; break;
      case 'dex':              self::$jsFiles[12] = 'dex.js?v=24'; break;
      case 'flex':             self::$jsFiles[13] = 'flex.js?v=2'; break;
      case 'mobile':           self::$jsFiles[14] = 'mobile.js?v=2'; break;
      case 'hangman':          self::$jsFiles[15] = 'hangman.js?v=5'; break;
      case 'mill':             self::$jsFiles[16] = 'mill.js?v=3'; break;
      case 'wotd':             self::$jsFiles[17] = 'wotd.js?v=1';
      case 'lexemEdit':        self::$jsFiles[18] = 'lexemEdit.js?v=6'; break;
      case 'jcrop':            self::$jsFiles[19] = 'jquery.Jcrop.min.js?v=2'; break;
      case 'easyui':           self::$jsFiles[20] = 'jquery.easyui.min.js?v=2'; break;
      case 'select2':          self::$jsFiles[21] = 'select2.min.js?v=3'; break;
      case 'select2Dev':       self::$jsFiles[22] = 'select2Dev.js?v=2'; break;
      case 'visualTag':        self::$jsFiles[23] = 'visualTag.js'; break;
      case 'gallery':          
        self::$jsFiles[24] = 'colorbox/jquery.colorbox-min.js';
        self::$jsFiles[25] = 'colorbox/jquery.colorbox-ro.js';
        self::$jsFiles[26] = 'dexGallery.js?v=2';
        self::$jsFiles[27] = 'jcanvas.min.js';
        break;
      default:
        FlashMessage::add("Cannot load JS script {$id}");
        util_redirect(util_getWwwRoot());
      }
    }
  }
}

?>
