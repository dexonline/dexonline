<?php

require_once 'third-party/smarty-3.1.34/Smarty.class.php';

class Smart {

  private static $theSmarty = null;
  private static $cssFiles = [];
  private static $jsFiles = [];
  private static $includedResources = [];

  static function init() {
    self::$theSmarty = new Smarty();
    self::$theSmarty->template_dir = Config::ROOT . 'templates';
    self::$theSmarty->compile_dir = Config::ROOT . 'templates_c';
    // sufficient for now; generalize if more plugin sources are needed
    self::$theSmarty->addPluginsDir(__DIR__ . '/smarty-plugins');

    // This allows variable names in {include} tags
    self::$theSmarty->inheritance_merge_compiled_includes = false;

    if (Request::isWeb()) {
      self::assign([
        'currentYear' => date('Y'),
        'privateMode' => Util::isPrivateMode(),
        'advancedSearch' => Session::userPrefers(Preferences::SHOW_ADVANCED),
      ]);
    }
  }

  // Add $template.css and $template.js to the file lists, if they exist.
  static function addSameNameFiles($template) {
    $baseName = str_replace('.tpl', '', $template);

    // Add {$template}.css if the file exists
    $cssFile = "autoload/{$baseName}.css";
    $fileName = Config::ROOT . 'www/css/' . $cssFile;
    if (file_exists($fileName)) {
      self::$cssFiles[] = $cssFile;
    }

    // Add {$template}.js if the file exists
    $jsFile = "autoload/{$baseName}.js";
    $fileName = Config::ROOT . 'www/js/' . $jsFile;
    if (file_exists($fileName)) {
      self::$jsFiles[] = $jsFile;
    }
  }

  // add a CSS file relative to css/plugins/
  static function addPluginCss($name) {
    self::$cssFiles[] = "plugins/$name";
  }

  // add a JS file relative to js/plugins/
  static function addPluginJs($name) {
    self::$jsFiles[] = "plugins/$name";
  }

  // Returns lists of css and js files to include. Selects CSS and JS files
  // from the included resources and RESOURCE_MAP and adds self::$cssFiles
  // and self::$jsFiles at the end.
  static function orderResources() {
    // first add all dependencies
    $map = [];
    while ($key = array_pop(self::$includedResources)) {
      $map[$key] = true;
      $deps = Constant::RESOURCE_MAP[$key]['deps'] ?? [];
      foreach ($deps as $dep) {
        if (!isset($map[$dep])) {
          self::$includedResources[] = $dep;
        }
      }
    }

    // now collect CSS and JS files in map order
    $resultCss = [];
    $resultJs = [];
    foreach (Constant::RESOURCE_MAP as $key => $data) {
      if (isset($map[$key])) {
        $list = $data['css'] ?? [];
        foreach ($list as $css) {
          $resultCss[] = $css;
        }

        $list = $data['js'] ?? [];
        foreach ($list as $js) {
          $resultJs[] = $js;
        }
      }
    }

    // finally, append $cssFiles and $jsFiles
    $resultCss = array_merge($resultCss, self::$cssFiles);
    $resultJs = array_merge($resultJs, self::$jsFiles);
    return [ $resultCss, $resultJs ];
  }

  static function mergeResources($files, $type) {
    // compute the full file names and get the latest timestamp
    $full = [];
    $maxTimestamp = 0;
    foreach ($files as $file) {
      $name = sprintf('%swww/%s/%s', Config::ROOT, $type, $file);
      $full[] = $name;
      $timestamp = filemtime($name);
      $maxTimestamp = max($maxTimestamp, $timestamp);
    }

    // compute the output file name
    $hash = md5(implode(',', $full));
    $outputDir = sprintf('%swww/%s/merged/', Config::ROOT, $type);
    $output = sprintf('%s%s.%s', $outputDir, $hash, $type);

    // generate the output file if it doesn't exist or if it's too old
    if (!file_exists($output) || (filemtime($output) < $maxTimestamp)) {
      $tmpFile = tempnam(Config::TEMP_DIR, 'merge_');
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
    $path = sprintf('%s%s/merged/%s.%s', Config::URL_PREFIX, $type, $hash, $type);
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
        !Str::startsWith($url, 'data:')){

      // trim and save the anchor
      $parts = preg_split('/([#?])/', $url, 2, PREG_SPLIT_DELIM_CAPTURE);
      if (count($parts) > 1) {
        $url = $parts[0];
        $anchor = $parts[1] . $parts[2];
      } else {
        $anchor = '';
      }

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
      $url = $relDestImage . $anchor;
    }
    return "url($url)";
  }

  /* Prepare and display a template. */
  /* $hardened = assume nothing about the availability of the database */
  static function display($templateName, $hardened = false) {
    self::addResources('main', 'jquery', 'bootstrap', 'select2');
    if (Config::SEARCH_AC_ENABLED) {
      self::addResources('jqueryui');
    }
    if (User::getActiveId()) {
      self::addResources('loggedIn');
    }
    if (User::can(User::PRIV_ANY)) {
      self::addResources('admin', 'pageModal', 'sprintf');
    }
    if (Util::isPrivateMode()) {
      self::addResources('privateMode');
    }
    self::addSameNameFiles($templateName);
    self::$cssFiles[] = "responsive.css";
    if (!$hardened) {
      if (User::can(User::PRIV_ANY)) {
        self::assign('recentLinks', RecentLink::load());
      }
    }
    self::registerOutputFilters();
    Plugin::notify('cssJsSmarty');
    print self::fetch($templateName);
  }

  static function displayWithoutSkin($templateName) {
    self::registerOutputFilters();
    print self::fetch($templateName);
  }

  static function fetch($templateName) {
    list ($cssFiles, $jsFiles) = self::orderResources();
    self::assign('cssFile', self::mergeResources($cssFiles, 'css'));
    self::assign('jsFile', self::mergeResources($jsFiles, 'js'));

    self::assign('flashMessages', FlashMessage::getMessages());
    return self::$theSmarty->fetch($templateName);
  }

  /**
   * Can be called as
   * assign($name, $value) or
   * assign([$name1 => $value1, $name2 => $value2, ...])
   **/
  static function assign($arg1, $arg2 = null) {
    if (is_array($arg1)) {
      foreach ($arg1 as $name => $value) {
        self::$theSmarty->assign($name, $value);
      }
    } else {
      self::$theSmarty->assign($arg1, $arg2);
    }
  }

  static function registerOutputFilters() {
    if (Session::userPrefers(Preferences::CEDILLA_BELOW)) {
      self::$theSmarty->registerFilter('output', ['Str', 'replace_st']);
    }
    if (Session::userPrefers(Preferences::OLD_ORTHOGRAPHY)) {
      self::$theSmarty->registerFilter('output', ['Str', 'replace_ai']);
    }
    self::$theSmarty->registerFilter('output', ['Smart', 'minifyOutput']);
    self::$theSmarty->registerPlugin('modifier', 'nf', 'LocaleUtil::number');
  }

  // Marks required CSS and JS files for inclusion.
  // $keys: array of keys in Constant::RESOURCE_MAP
  static function addResources(...$keys) {
    foreach ($keys as $key) {
      if (!isset(Constant::RESOURCE_MAP[$key])) {
        FlashMessage::add("Unknown resource ID {$key}");
        Util::redirectToHome();
      }
      self::$includedResources[] = $key;
    }
  }

  // Based on `third-party/smarty-*/plugins/outputfilter.trimwhitespace.php`.
  // This one doesn't strip IE comments (there are none in the templates)
  // and removes spaces more aggressively --- the templates don't contain
  // the errors the original function was attempting to work around.
  static function minifyOutput($source, Smarty_Internal_Template $smarty)
  {
    $store = [];
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

    $expressions = [
      '#\n+#Ss' => ' ',
      '#\s+#Ss' => ' ',
    ];

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
