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
    if (util_isWebBasedScript()) {
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
    }
    self::$theSmarty->registerPlugin('function', 'getDebugInfo', array('SmartyWrap', 'getDebugInfo'));
  }

  // Add $template.css and $template.js to the file lists, if they exist.
  static function addSameNameFiles($template) {
    $baseName = pathinfo($template)['filename'];

    // Add {$template}.css if the file exists
    $cssFile = "autoload/{$baseName}.css";
    $fileName = util_getRootPath() . 'wwwbase/css/' . $cssFile;
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

  // Replace $key => $fileName with key => [ 'file' => $fileName, 'date' => $date ],
  // where date is the date + hour of the last modification of each file.
  static function copyTimestamps($v, $prefix) {
    $path = util_getRootPath() . "wwwbase/{$prefix}/";
    $result = [];
    foreach ($v as $key => $fileName) {
      $timetamp = filemtime($path . $fileName);
      $date = date('YmdH', $timetamp);
      $result[$key] = [
        'file' => $fileName,
        'date' => $date,
      ];
    }
    return $result;
  }

  static function fetchSkin($templateName) {
    $skin = session_getSkin();
    self::addCss($skin, 'bootstrap');
    self::addJs('jquery', 'dex', 'bootstrap');
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
  	self::addCss('flex');
    self::addJs('dex', 'jquery');
    self::addSameNameFiles($templateName);
    print self::fetch($templateName);
  }

  static function fetch($templateName) {
    ksort(self::$cssFiles);
    ksort(self::$jsFiles);
    self::assign('cssFiles', self::copyTimestamps(self::$cssFiles, 'css'));
    self::assign('jsFiles', self::copyTimestamps(self::$jsFiles, 'js'));
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
        case 'bootstrap':
          self::$cssFiles[3] = 'bootstrap.min.css';
          // self::$cssFiles[4] = 'bootstrap-theme.min.css';
          break;
        case 'jqgrid':              self::$cssFiles[5] = 'ui.jqgrid.css'; break;
        case 'tablesorter':         self::$cssFiles[6] = 'tablesorter/theme.blue.css'; break;
        case 'elfinder':
          self::$cssFiles[7] = 'elfinder/css/elfinder.min.css';
          self::$cssFiles[8] = 'elfinderDev.css';
          break;
        case 'windowEngine':        self::$cssFiles[9] = 'jquery-wm/main.css'; break;
        case 'responsive':          self::$cssFiles[10] = 'responsive.css'; break;
        case 'mobile':              self::$cssFiles[11] = 'mobile.css'; break;
        case 'flex':                self::$cssFiles[12] = 'flex.css'; break;
        case 'paradigm':            self::$cssFiles[13] = 'paradigm.css'; break;
        case 'jcrop':               self::$cssFiles[14] = 'jcrop/jquery.Jcrop.min.css'; break;
        case 'select2':             self::$cssFiles[15] = 'select2/select2.css'; break;
        case 'gallery':
          self::$cssFiles[16] = 'colorbox/colorbox.css';
          self::$cssFiles[17] = 'visualDict.css';
          break;
        case 'textComplete':        self::$cssFiles[18] = 'jquery.textcomplete.css'; break;
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
        case 'bootstrap':        self::$jsFiles[3] = 'bootstrap.min.js'; break;
        case 'jqgrid':
          self::$jsFiles[4] = 'grid.locale-en.js';
          self::$jsFiles[5] = 'jquery.jqGrid.min.js';
          break;
        case 'jqnotice':         self::$jsFiles[6] = 'jquery.notice.js'; break;
        case 'jqTableDnd':       self::$jsFiles[7] = 'jquery.tablednd.0.8.min.js'; break;
        case 'tablesorter':
          self::$jsFiles[8] = 'jquery.tablesorter.min.js';
          self::$jsFiles[9] = 'tablesorter.dev.js';
          break;
        case 'pager':            self::$jsFiles[10] = 'jquery.tablesorter.pager.min.js'; break;
        case 'elfinder':         self::$jsFiles[11] = 'elfinder.min.js'; break;
        case 'windowEngine':     self::$jsFiles[12] = 'jquery-wm.js'; break;
        case 'cookie':           self::$jsFiles[13] = 'jquery.cookie.js'; break;
        case 'dex':              self::$jsFiles[14] = 'dex.js'; break;
        case 'jcrop':            self::$jsFiles[15] = 'jquery.Jcrop.min.js'; break;
        case 'select2':
          self::$jsFiles[16] = 'select2/select2.min.js';
          self::$jsFiles[17] = 'select2/i18n/ro.js';
          break;
        case 'select2Dev':       self::$jsFiles[18] = 'select2Dev.js'; break;
        case 'jcanvas':          self::$jsFiles[19] = 'jcanvas.min.js'; break;
        case 'gallery':
          self::$jsFiles[20] = 'colorbox/jquery.colorbox-min.js';
          self::$jsFiles[21] = 'colorbox/jquery.colorbox-ro.js';
          self::$jsFiles[22] = 'dexGallery.js';
          break;
        case 'modelDropdown':    self::$jsFiles[23] = 'modelDropdown.js'; break;
        case 'textComplete':     self::$jsFiles[24] = 'jquery.textcomplete.min.js'; break;
        case 'tinymce':          self::$jsFiles[25] = 'tinymce-4.4.0/tinymce.min.js'; break;
        case 'bootstrap':
          self::$jsFiles[26] = 'bootstrap/js/bootstrap.min.js';
          break;
        default:
          FlashMessage::add("Cannot load JS script {$id}");
          util_redirect(util_getWwwRoot());
      }
    }
  }

}

?>
