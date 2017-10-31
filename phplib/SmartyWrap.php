<?php

class SmartyWrap {
  private static $theSmarty = null;
  private static $cssFiles = [];
  private static $jsFiles = [];

  static function init() {
    self::$theSmarty = new Smarty();
    self::$theSmarty->template_dir = Core::getRootPath() . 'templates';
    self::$theSmarty->compile_dir = Core::getRootPath() . 'templates_c';
    self::$theSmarty->inheritance_merge_compiled_includes = false; // This allows variable names in {include} tags
    if (Request::isWeb()) {
      self::assign('wwwRoot', Core::getWwwRoot());
      self::assign('imgRoot', Core::getImgRoot());
      self::assign('currentYear', date("Y"));
      self::assign('suggestNoBanner', Util::suggestNoBanner());
      self::assign('privateMode', Session::userPrefers(Preferences::PRIVATE_MODE));
      self::assign('cfg', Config::getAll());
    }
  }

  // Add $template.css and $template.js to the file lists, if they exist.
  static function addSameNameFiles($template) {
    $baseName = pathinfo($template)['filename'];

    // Add {$template}.css if the file exists
    $cssFile = "autoload/{$baseName}.css";
    $fileName = Core::getRootPath() . 'wwwbase/css/' . $cssFile;
    if (file_exists($fileName)) {
      self::$cssFiles[] = $cssFile;
    }

    // Add {$template}.js if the file exists
    $jsFile = "autoload/{$baseName}.js";
    $fileName = Core::getRootPath() . 'wwwbase/js/' . $jsFile;
    if (file_exists($fileName)) {
      self::$jsFiles[] = $jsFile;
    }
  }

  static function mergeResources($files, $type) {
    // compute the full file names and get the latest timestamp
    $full = [];
    $maxTimestamp = 0;
    foreach ($files as $file) {
      $name = sprintf('%swwwbase/%s/%s', Core::getRootPath(), $type, $file);
      $full[] = $name;
      $timestamp = filemtime($name);
      $maxTimestamp = max($maxTimestamp, $timestamp);
    }

    // compute the output file name
    $hash = md5(implode(',', $full));
    $outputDir = sprintf('%swwwbase/%s/merged/', Core::getRootPath(), $type);
    $output = sprintf('%s%s.%s', $outputDir, $hash, $type);

    // generate the output file if it doesn't exist or if it's too old
    if (!file_exists($output) || (filemtime($output) < $maxTimestamp)) {
      $tmpFile = tempnam(Core::getTempPath(), 'merge_');
      foreach ($full as $f) {
        $contents = file_get_contents($f);
        if ($type == 'css') {
          // replace image references
          $contents = preg_replace_callback(
            '/url\([\'"]?([^\'")]+)[\'"]?\)/',
            function($match) use ($f, $outputDir) {
              return self::convertImages($f, $outputDir, $match[1]);
            },
            $contents);
        }
        file_put_contents($tmpFile,  $contents . "\n", FILE_APPEND);
      }
      rename($tmpFile, $output);
      chmod($output, 0666);
    }

    // return the URL path and the timestamp
    $path = sprintf('%s%s/merged/%s.%s', Core::getWwwRoot(), $type, $hash, $type);
    $date = date('YmdHis', filemtime($output));
    return [
      'path' => $path,
      'date' => $date,
    ];
  }

  // Copy an image file and return a reference to it.
  // Assumes that $cssFile is being moved to $outputDir.
  // $url is the contents between parentheses in "url(...)"
  static function convertImages($cssFile, $outputDir, $url) {
    // only handle third-party files; do nothing for data URIs, fonts, own CSS files etc.
    if ((strpos($cssFile, 'third-party') !== false) &&
        !StringUtil::startsWith($url, 'data:')){
      // get the absolute and relative source image filename
      $absSrcImage = realpath(dirname($cssFile) . '/' . $url);
      $relImage = basename($absSrcImage);

      // get the relative and absolute destination directory
      $basename = basename($cssFile, '.custom.min.css');
      $basename = basename($basename, '.min.css');
      $basename = basename($basename, '.css');
      $relImageDir = $basename . '/';
      $absImageDir = $outputDir . $relImageDir;

      // get the relative and absolute image target filename
      $relDestImage = $relImageDir . $relImage;
      $absDestImage = $absImageDir . $relImage;

      if (!file_exists($absDestImage)) {
        @mkdir($absImageDir);
        copy($absSrcImage, $absDestImage);
      }
      $url = $relDestImage;
    }
    return "url($url)";
  }

  /* Prepare and display a template. */
  /* $hardened = assume nothing about the availability of the database */
  static function display($templateName, $hardened = false) {
    self::addCss('main', 'bootstrap', 'select2');
    self::addJs('jquery', 'dex', 'bootstrap', 'select2');
    if (Config::get('search.acEnable')) {
      self::addCss('jqueryui');
      self::addJs('jqueryui');
    }
    if (Config::get('global.callToAction') &&
        !isset($_COOKIE['hideCallToAction'])) { // CTA campaign active and user did not hide it
      self::addCss('callToAction');
      self::addJs('callToAction', 'cookie');
      self::assign('callToAction', true);
    }
    if (User::can(User::PRIV_ANY)) {
      self::addJs('admin', 'hotkeys');
    }
    if (Session::userPrefers(Preferences::PRIVATE_MODE)) {
      self::addCss('privateMode');
    }
    self::addSameNameFiles($templateName);
    self::$cssFiles[] = "responsive.css";
    self::assign('skinVariables', Config::getSection('skin'));
    if (!$hardened) {
      $sources = Model::factory('Source')
               ->order_by_desc('dropdownOrder')
               ->order_by_asc('displayOrder')
               ->find_many();
      self::assign('sources', $sources);
      if (User::can(User::PRIV_ANY)) {
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
    self::assign('cssFile', self::mergeResources(self::$cssFiles, 'css'));
    self::assign('jsFile', self::mergeResources(self::$jsFiles, 'js'));
    self::assign('flashMessages', FlashMessage::getMessages());
    return self::$theSmarty->fetch($templateName);
  }

  static function assign($variable, $value) {
    self::$theSmarty->assign($variable, $value);
  }

  static function registerOutputFilters() {
    if (Session::userPrefers(Preferences::CEDILLA_BELOW)) {
      self::$theSmarty->registerFilter('output', ['StringUtil', 'replace_st']);
    }
    if (Session::userPrefers(Preferences::OLD_ORTHOGRAPHY)) {
      self::$theSmarty->registerFilter('output', ['StringUtil', 'replace_ai']);
    }
    self::$theSmarty->registerFilter('output', ['SmartyWrap', 'minifyOutput']);
  }

 static function addCss(/* Variable-length argument list */) {
    // Note the priorities. This allows files to be added in any order, regardless of dependencies
    foreach (func_get_args() as $id) {
      switch($id) {
        case 'jqueryui':            self::$cssFiles[1] = 'third-party/smoothness-1.10.4/jquery-ui-1.10.4.custom.min.css'; break;
        case 'bootstrap':           self::$cssFiles[2] = 'third-party/bootstrap.min.css'; break;
        case 'jqgrid':              self::$cssFiles[3] = 'third-party/ui.jqgrid.css'; break;
        case 'tablesorter':
          self::$cssFiles[4] = 'third-party/tablesorter/theme.bootstrap.css';
          self::$cssFiles[5] = 'third-party/tablesorter/jquery.tablesorter.pager.min.css';
          break;
        case 'elfinder':
          self::$cssFiles[6] = 'third-party/elfinder/css/elfinder.min.css';
          self::$cssFiles[7] = 'third-party/elfinder/css/theme.css';
          break;
        case 'main':                self::$cssFiles[8] = 'main.css'; break;
        case 'admin':               self::$cssFiles[9] = 'admin.css'; break;
        case 'paradigm':            self::$cssFiles[10] = 'paradigm.css'; break;
        case 'jcrop':               self::$cssFiles[11] = 'third-party/jcrop/jquery.Jcrop.min.css'; break;
        case 'select2':             self::$cssFiles[12] = 'third-party/select2.min.css'; break;
        case 'gallery':
          self::$cssFiles[13] = 'third-party/colorbox/colorbox.css';
          self::$cssFiles[14] = 'gallery.css';
          break;
        case 'textComplete':        self::$cssFiles[15] = 'third-party/jquery.textcomplete.css'; break;
        case 'tinymce':             self::$cssFiles[16] = 'tinymce.css'; break;
        case 'meaningTree':         self::$cssFiles[17] = 'meaningTree.css'; break;
        case 'editableMeaningTree': self::$cssFiles[18] = 'editableMeaningTree.css'; break;
        case 'callToAction':        self::$cssFiles[19] = 'callToAction.css'; break;
        case 'privateMode':         self::$cssFiles[20] = 'opensans.css'; break;
        case 'colorpicker':
          self::$cssFiles[21] = 'third-party/bootstrap-colorpicker.min.css';
          break;
        case 'diff':                self::$cssFiles[22] = 'diff.css'; break;
        case 'bootstrap-spinedit':  self::$cssFiles[23] = 'third-party/bootstrap-spinedit.css'; break;
        case 'bootstrap-datepicker':
          self::$cssFiles[24] = 'third-party/bootstrap-datepicker3.min.css';
          break;
        default:
          FlashMessage::add("Cannot load CSS file {$id}");
          Util::redirect(Core::getWwwRoot());
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
        case 'jqTableDnd':    self::$jsFiles[6] = 'third-party/jquery.tablednd.0.8.min.js'; break;
        case 'tablesorter':
          self::$jsFiles[7] = 'third-party/tablesorter/jquery.tablesorter.min.js';
          self::$jsFiles[8] = 'third-party/tablesorter/jquery.tablesorter.widgets.js';
          self::$jsFiles[9] = 'third-party/tablesorter/jquery.tablesorter.pager.min.js';
          break;
        case 'elfinder':      self::$jsFiles[10] = 'third-party/elfinder.min.js'; break;
        case 'cookie':        self::$jsFiles[11] = 'third-party/jquery.cookie.js'; break;
        case 'dex':           self::$jsFiles[12] = 'dex.js'; break;
        case 'jcrop':         self::$jsFiles[13] = 'third-party/jquery.Jcrop.min.js'; break;
        case 'select2':
          self::$jsFiles[14] = 'third-party/select2/select2.min.js';
          self::$jsFiles[15] = 'third-party/select2/i18n/ro.js';
          break;
        case 'select2Dev':    self::$jsFiles[16] = 'select2Dev.js'; break;
        case 'jcanvas':       self::$jsFiles[17] = 'third-party/jcanvas.min.js'; break;
        case 'pixijs':        self::$jsFiles[18] = 'third-party/pixi.min.js'; break;
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
        case 'callToAction':  self::$jsFiles[29] = 'callToAction.js'; break;
        case 'seedrandom':    self::$jsFiles[30] = 'third-party/seedrandom.min.js'; break;
        case 'colorpicker':   self::$jsFiles[31] = 'third-party/bootstrap-colorpicker.min.js'; break;
        case 'diff':          self::$jsFiles[32] = 'diff.js'; break;
        case 'diffSelector':  self::$jsFiles[33] = 'diffSelector.js'; break;
        case 'bootstrap-spinedit':  self::$jsFiles[34] = 'third-party/bootstrap-spinedit.js'; break;
        case 'frequentObjects':  self::$jsFiles[35] = 'frequentObjects.js'; break;
        case 'bootstrap-datepicker':
          self::$jsFiles[36] = 'third-party/bootstrap-datepicker.min.js';
          self::$jsFiles[37] = 'third-party/bootstrap-datepicker.ro.min.js';
          break;
        case 'adminIndex':    self::$jsFiles[38] = 'adminIndex.js'; break;
        case 'admin':         self::$jsFiles[39] = 'admin.js'; break;
        default:
          FlashMessage::add("Cannot load JS script {$id}");
          Util::redirect(Core::getWwwRoot());
      }
    }
  }

  // Based on `third-party/smarty/plugins/outputfilter.trimwhitespace.php`.
  // This one doesn't strip IE comments (there are none in the templates)
  // and removes spaces more aggressively --- the templates don't contain
  // the errors the original function was attempting to work around.
  static function minifyOutput($source, Smarty_Internal_Template $smarty)
  {
    $store = array();
    $_store = 0;
    $_offset = 0;

    // Unify Line-Breaks to \n.
    $source = preg_replace("/\015\012|\015|\012/", "\n", $source);

    // Remove HTML comments.
    $source = preg_replace( '#<!--.*?-->#ms', '', $source );

    // Capture html elements not to be messed with. For example, whitespace
    // is used in `textarea` content by editors in order to more nicely format
    // their definitions.
    $_offset = 0;
    if (preg_match_all('#<(script|pre|textarea)[^>]*>.*?</\\1>#is', $source, $matches, PREG_OFFSET_CAPTURE | PREG_SET_ORDER)) {
        foreach ($matches as $match) {
            $store[] = $match[0][0];
            $_length = strlen($match[0][0]);
            $replace = '@!@SMARTY:' . $_store . ':SMARTY@!@';
            $source = substr_replace($source, $replace, $match[0][1] - $_offset, $_length);

            $_offset += $_length - strlen($replace);
            $_store++;
        }
    }

    $expressions = array(
        '#\n+#Ss' => ' ',
        '#\s+#Ss' => ' ',
    );

    $source = preg_replace( array_keys($expressions), array_values($expressions), $source );

    $_offset = 0;
    if (preg_match_all('#@!@SMARTY:([0-9]+):SMARTY@!@#is', $source, $matches, PREG_OFFSET_CAPTURE | PREG_SET_ORDER)) {
        foreach ($matches as $match) {
            $_length = strlen($match[0][0]);
            $replace = $store[$match[1][0]];
            $source = substr_replace($source, $replace, $match[0][1] + $_offset, $_length);

            $_offset += strlen($replace) - $_length;
            $_store++;
        }
    }

    return $source;
  }
}
?>
