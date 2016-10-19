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
      self::assign('sUser', session_getUser());
      self::assign('nick', session_getUserNick());
      self::assign('currentYear', date("Y"));
      self::assign('suggestNoBanner', util_suggestNoBanner());
      self::assign('cfg', Config::getAll());
      self::assign('GLOBALS', $GLOBALS);
    }
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
    self::$cssFiles[] = "responsive.css";

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

  /* Prepare and display a template. */
  static function display($templateName, $hardened = false) {
    self::addCss('main', 'bootstrap', 'select2');
    self::addJs('jquery', 'dex', 'bootstrap', 'select2');
    if (Config::get('search.acEnable')) {
      self::addCss('jqueryui');
      self::addJs('jqueryui');
    }
    if (util_isModerator(PRIV_EDIT)) {
      self::addJs('hotkeys');
    }
    self::addSameNameFiles($templateName);
    self::assign('skinVariables', Config::getSection('skin'));
    if (!$hardened) {
      $sources = Model::factory('Source')
               ->order_by_asc('displayOrder')
               ->find_many();
      self::assign('sources', $sources);
      if (util_isModerator(PRIV_ANY)) {
        self::assign('recentLinks', RecentLink::load());
      }
    }
    self::registerOutputFilters();
    print self::fetch($templateName);
  }

  static function displayWithoutSkin($templateName) {
    self::registerOutputFilters();
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

 static function addCss(/* Variable-length argument list */) {
    // Note the priorities. This allows files to be added in any order, regardless of dependencies
    foreach (func_get_args() as $id) {
      switch($id) {
        case 'jqueryui':            self::$cssFiles[1] = 'third-party/smoothness-1.10.4/jquery-ui-1.10.4.custom.min.css'; break;
        case 'bootstrap':           self::$cssFiles[2] = 'bootstrap.min.css'; break;
        case 'jqgrid':              self::$cssFiles[3] = 'third-party/ui.jqgrid.css'; break;
        case 'tablesorter':
          self::$cssFiles[4] = 'third-party/tablesorter/theme.bootstrap.css';
          self::$cssFiles[5] = 'third-party/tablesorter/jquery.tablesorter.pager.min.css';
          break;
        case 'elfinder':
          self::$cssFiles[6] = 'third-party/elfinder/css/elfinder.min.css';
          break;
        case 'main':                self::$cssFiles[7] = 'main.css'; break;
        case 'admin':               self::$cssFiles[8] = 'admin.css'; break;
        case 'paradigm':            self::$cssFiles[9] = 'paradigm.css'; break;
        case 'jcrop':               self::$cssFiles[10] = 'third-party/jcrop/jquery.Jcrop.min.css'; break;
        case 'select2':             self::$cssFiles[11] = 'third-party/select2.min.css'; break;
        case 'gallery':
          self::$cssFiles[12] = 'third-party/colorbox/colorbox.css';
          self::$cssFiles[13] = 'gallery.css';
          break;
        case 'textComplete':        self::$cssFiles[14] = 'third-party/jquery.textcomplete.css'; break;
        case 'tinymce':             self::$cssFiles[15] = 'tinymce.css'; break;
        case 'meaningTree':         self::$cssFiles[16] = 'meaningTree.css'; break;
        case 'responsive':         self::$cssFiles[100] = 'responsive.css'; break;
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
        case 'jquery':        self::$jsFiles[1] = 'third-party/jquery-1.12.4.min.js'; break;
        case 'jqueryui':      self::$jsFiles[2] = 'third-party/jquery-ui-1.10.3.custom.min.js'; break;
        case 'bootstrap':     self::$jsFiles[3] = 'third-party/bootstrap.min.js'; break;
        case 'jqgrid':
          self::$jsFiles[4] = 'third-party/grid.locale-en.js';
          self::$jsFiles[5] = 'third-party/jquery.jqGrid.min.js';
          break;
        case 'jqnotice':      self::$jsFiles[6] = 'third-party/jquery.notice.js'; break;
        case 'jqTableDnd':    self::$jsFiles[7] = 'third-party/jquery.tablednd.0.8.min.js'; break;
        case 'tablesorter':
          self::$jsFiles[8] = 'third-party/tablesorter/jquery.tablesorter.min.js';
          self::$jsFiles[9] = 'third-party/tablesorter/jquery.tablesorter.widgets.js';
          self::$jsFiles[10] = 'third-party/tablesorter/jquery.tablesorter.pager.min.js';
          break;
        case 'elfinder':      self::$jsFiles[11] = 'third-party/elfinder.min.js'; break;
        case 'cookie':        self::$jsFiles[12] = 'third-party/jquery.cookie.js'; break;
        case 'dex':           self::$jsFiles[13] = 'dex.js'; break;
        case 'jcrop':         self::$jsFiles[14] = 'third-party/jquery.Jcrop.min.js'; break;
        case 'select2':
          self::$jsFiles[15] = 'third-party/select2/select2.min.js';
          self::$jsFiles[16] = 'third-party/select2/i18n/ro.js';
          break;
        case 'select2Dev':    self::$jsFiles[17] = 'select2Dev.js'; break;
        case 'jcanvas':       self::$jsFiles[18] = 'third-party/jcanvas.min.js'; break;
        case 'gallery':
          self::$jsFiles[19] = 'third-party/colorbox/jquery.colorbox-min.js';
          self::$jsFiles[20] = 'third-party/colorbox/jquery.colorbox-ro.js';
          self::$jsFiles[21] = 'dexGallery.js';
          break;
        case 'modelDropdown': self::$jsFiles[22] = 'modelDropdown.js'; break;
        case 'textComplete':  self::$jsFiles[23] = 'third-party/jquery.textcomplete.min.js'; break;
        case 'tinymce':
          self::$jsFiles[24] = 'third-party/tinymce-4.4.0/tinymce.min.js';
          self::$jsFiles[25] = 'tinymce.js';
          break;
        case 'meaningTree':   self::$jsFiles[26] = 'meaningTree.js'; break;
        case 'hotkeys':
          self::$jsFiles[27] = 'third-party/jquery.hotkeys.js';
          self::$jsFiles[28] = 'hotkeys.js';
          break;
        default:
          FlashMessage::add("Cannot load JS script {$id}");
          util_redirect(util_getWwwRoot());
      }
    }
  }

}

?>
