<?php

class SmartyWrap {
  private static $theSmarty = null;
  private static $cssFiles = [];
  private static $jsFiles = [];

  static function init() {
    self::$theSmarty = new Smarty();
    self::$theSmarty->template_dir = util_getRootPath() . 'templates';
    self::$theSmarty->compile_dir = util_getRootPath() . 'templates_c';
    self::$theSmarty->inheritance_merge_compiled_includes = false; // This allows variable names in {include} tags
    self::assign('wwwRoot', util_getWwwRoot());
    self::assign('imgRoot', util_getImgRoot());
    self::assign('sources', Model::factory('Source')->order_by_desc('isOfficial')->order_by_asc('displayOrder')->find_many());
    self::assign('sUser', session_getUser());
    self::assign('nick', session_getUserNick());
    self::assign('currentYear', date("Y"));
    self::assign('isMobile', util_isMobile());
    self::assign('suggestNoBanner', util_suggestNoBanner());
    self::assign('cfg', Config::getAll());
    self::assign('GLOBALS', $GLOBALS);
    self::$theSmarty->registerPlugin('function', 'getDebugInfo', array('SmartyWrap', 'getDebugInfo'));
  }

  // Add $template.css and $template.js to the file lists, if they exist.
  static function addSameNameFiles($template) {
    $baseName = pathinfo($template)['filename'];

    // Add {$template}.css if the file exists
    $cssFile = "autoload/{$baseName}.css";
    $fileName = util_getRootPath() . 'wwwbase/styles/' . $cssFile;
    if (file_exists($fileName)) {
      self::$cssFiles[] = $cssFile;
    }

    // Add {$template}.js if the file exists
    $jsFile = "autoload/{$baseName}.js";
    $fileName = util_getRootPath() . 'wwwbase/js/' . $jsFile;
    if (file_exists($fileName)) {
      self::$jsFiles[] = $jsFile;
    }
  }

  static function fetchSkin($templateName) {
    $skin = session_getSkin();
    self::addCss($skin, 'flash');
    self::addJs('jquery', 'dex');
    if (Config::get('search.acEnable')) {
        self::addCss('jqueryui');
        self::addJs('jqueryui');
    }
    self::addSameNameFiles($templateName);
    $skinVariables = array_merge(Config::getSection("skin-default"),
                                 Config::getSection("skin-{$skin}"));
    self::assign('skinVariables', $skinVariables);
    self::registerOutputFilters();
    return self::fetch($templateName);
  }

  /* Common case: render the $templateName inside pageLayout.tpl and with the user-preferred skin */
  static function display($templateName) {
    print self::fetchSkin($templateName);
  }

  static function displayPageWithSkin($templateName) {
    $skin = session_getSkin();
    print self::fetchSkin("{$skin}/$templateName");
  }

  static function displayWithoutSkin($templateName) {
    self::registerOutputFilters();
    print self::fetch($templateName);
  }

  static function displayAdminPage($templateName) {
    self::assign('templateName', $templateName);
  	self::addCss('flex', 'flash');
    self::addJs('dex', 'jquery');
    self::addSameNameFiles($templateName);
    print self::fetch($templateName);
  }

  static function fetch($templateName) {
    ksort(self::$cssFiles);
    ksort(self::$jsFiles);
    self::assign('cssFiles', self::$cssFiles);
    self::assign('jsFiles', self::$jsFiles);
    self::assign('flashMessages', FlashMessage::getMessages());
    return self::$theSmarty->fetch($templateName);
  }

  static function assign($variable, $value) {
    self::$theSmarty->assign($variable, $value);
  }

  static function registerOutputFilters() {
    if (session_user_prefers(Preferences::CEDILLA_BELOW)) {
      self::$theSmarty->registerFilter('output', array('StringUtil', 'replace_st'));
    }
    if (session_user_prefers(Preferences::OLD_ORTHOGRAPHY)) {
      self::$theSmarty->registerFilter('output', array('StringUtil', 'replace_ai'));
    }
  }

  static function getDebugInfo() {
    $data = DebugInfo::getDebugInfo();
    if (!$data['enabled']) {
      return '';
    }
    SmartyWrap::assign('debug_messages', $data['messages']);
    SmartyWrap::assign('debug_runningTimeMillis', $data['runningTimeMillis']);
    SmartyWrap::assign('debug_ormQueryLog', $data['ormQueryLog']);
    return SmartyWrap::fetch('bits/debugInfo.tpl');
  }

 static function addCss(/* Variable-length argument list */) {
    // Note the priorities. This allows files to be added in any order, regardless of dependencies
    foreach (func_get_args() as $id) {
      switch($id) {
        case 'jqueryui':            self::$cssFiles[1] = 'lightness-1.10.3/jquery-ui-1.10.3.custom.min.css'; break;
        case 'jqueryui-smoothness': self::$cssFiles[2] = 'smoothness-1.10.4/jquery-ui-1.10.4.custom.min.css'; break;
        case 'jqgrid':              self::$cssFiles[3] = 'ui.jqgrid.css?v=3'; break;
        case 'tablesorter':         self::$cssFiles[4] = 'tablesorter/theme.blue.css'; break;
        case 'elfinder':
          self::$cssFiles[5] = 'elfinder/css/elfinder.min.css?v=2';
          self::$cssFiles[6] = 'elfinderDev.css';
          break;
        case 'windowEngine':        self::$cssFiles[7] = 'jquery-wm/main.css'; break;
        case 'zepu':                self::$cssFiles[8] = 'zepu.css?v=82'; break;
        case 'polar':               self::$cssFiles[9] = 'polar.css?v=37'; break;
        case 'mobile':              self::$cssFiles[10] = 'mobile.css?v=23'; break;
        case 'flex':                self::$cssFiles[11] = 'flex.css?v=18'; break;
        case 'paradigm':            self::$cssFiles[12] = 'paradigm.css?v=3'; break;
        case 'jcrop':               self::$cssFiles[13] = 'jcrop/jquery.Jcrop.min.css?v=3'; break;
        case 'select2':             self::$cssFiles[14] = 'select2/select2.css?v=3'; break;
        case 'gallery':
          self::$cssFiles[15] = 'colorbox/colorbox.css?v=1';
          self::$cssFiles[16] = 'visualDict.css?v=3';
          break;
        case 'textComplete':        self::$cssFiles[17] = 'jquery.textcomplete.css'; break;
        case 'flash':               self::$cssFiles[18] = 'flash.css'; break;
        case 'scramble':             self::$cssFiles[19] = 'scramble.css'; break;
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
        case 'jqTableDnd':       self::$jsFiles[6] = 'jquery.tablednd.0.8.min.js?v=1'; break;
        case 'tablesorter':
          self::$jsFiles[7] = 'jquery.tablesorter.min.js?v=5';
          self::$jsFiles[8] = 'tablesorter.dev.js?v=3';
          break;
        case 'pager':            self::$jsFiles[9] = 'jquery.tablesorter.pager.min.js'; break;
        case 'elfinder':         self::$jsFiles[10] = 'elfinder.min.js?v=1'; break;
        case 'windowEngine':     self::$jsFiles[11] = 'jquery-wm.js'; break;
        case 'cookie':           self::$jsFiles[12] = 'jquery.cookie.js?v=1'; break;
        case 'dex':              self::$jsFiles[13] = 'dex.js?v=36'; break;
        case 'jcrop':            self::$jsFiles[14] = 'jquery.Jcrop.min.js?v=2'; break;
        case 'select2':          self::$jsFiles[15] = 'select2.min.js?v=3'; break;
        case 'select2Dev':       self::$jsFiles[16] = 'select2Dev.js?v=8'; break;
        case 'gallery':
          self::$jsFiles[17] = 'colorbox/jquery.colorbox-min.js';
          self::$jsFiles[18] = 'colorbox/jquery.colorbox-ro.js';
          self::$jsFiles[19] = 'dexGallery.js?v=2';
          self::$jsFiles[20] = 'jcanvas.min.js';
          break;
        case 'modelDropdown':    self::$jsFiles[21] = 'modelDropdown.js'; break;
        case 'textComplete':     self::$jsFiles[22] = 'jquery.textcomplete.min.js'; break;
        case 'tinymce':          self::$jsFiles[23] = 'tinymce-4.3.4/tinymce.min.js'; break;
        case 'scramble':         
          self::$jsFiles[24] = 'scramble.js'; 
          self::$jsFiles[25] = 'jcanvas.min.js';
          break;
        default:
          FlashMessage::add("Cannot load JS script {$id}");
          util_redirect(util_getWwwRoot());
      }
    }
  }
  
}

?>
