<?php

class SmartyWrap {
  private static $theSmarty = null;
  private static $cssFiles = [];
  private static $jsFiles = [];
	private static $includedCss = [];
	private static $includedJs = [];
	private static $cssMap = [
		'jqueryui' => [
			'third-party/smoothness-1.10.4/jquery-ui-1.10.4.custom.min.css'
		],
		'bootstrap' => [
			'third-party/bootstrap.min.css'
		],
		'jqgrid' => [
			'third-party/ui.jqgrid.css'
		],
		'tablesorter' => [
			'third-party/tablesorter/theme.bootstrap.css',
			'third-party/tablesorter/jquery.tablesorter.pager.min.css',
		],
		'elfinder' => [
			'third-party/elfinder/css/elfinder.min.css',
			'third-party/elfinder/css/theme.css',
		],
		'main' => [
			'main.css'
		],
		'admin' => [
			'admin.css'
		],
		'paradigm' => [
			'paradigm.css'
		],
		'jcrop' => [
			'third-party/jcrop/jquery.Jcrop.min.css'
		],
		'select2' => [
			'third-party/select2.min.css'
		],
		'gallery' => [
			'third-party/colorbox/colorbox.css',
			'gallery.css',
		],
		'textComplete' => [
			'third-party/jquery.textcomplete.css'
		],
		'tinymce' => [
			'tinymce.css'
		],
		'meaningTree' => [
			'meaningTree.css'
		],
		'editableMeaningTree' => [
			'editableMeaningTree.css'
		],
		'callToAction' => [
			'callToAction.css'
		],
		'privateMode' => [
			'opensans.css'
		],
		'colorpicker' => [
			'third-party/bootstrap-colorpicker.min.css'
		],
		'diff' => [
			'diff.css'
		],
		'bootstrap-spinedit' => [
			'third-party/bootstrap-spinedit.css'
		],
		'bootstrap-datepicker' => [
			'third-party/bootstrap-datepicker3.min.css'
		],
	];
  private static $jsMap = [
		'jquery' => [
			'third-party/jquery-1.12.4.min.js'
		],
		'jqueryui' => [
			'third-party/jquery-ui-1.10.3.custom.min.js'
		],
		'bootstrap' => [
			'third-party/bootstrap.min.js'
		],
		'jqgrid' => [
			'third-party/grid.locale-en.js',
			'third-party/jquery.jqGrid.min.js',
		],
		'jqTableDnd' => [
			'third-party/jquery.tablednd.0.8.min.js'
		],
		'tablesorter' => [
			'third-party/tablesorter/jquery.tablesorter.min.js',
			'third-party/tablesorter/jquery.tablesorter.widgets.js',
			'third-party/tablesorter/jquery.tablesorter.pager.min.js',
		],
		'elfinder' => [
			'third-party/elfinder.min.js'
		],
		'cookie' => [
			'third-party/jquery.cookie.js'
		],
		'dex' => [
			'dex.js'
		],
		'jcrop' => [
			'third-party/jquery.Jcrop.min.js'
		],
		'select2' => [
			'third-party/select2/select2.min.js',
			'third-party/select2/i18n/ro.js',
		],
		'select2Dev' => [
			'select2Dev.js'
		],
		'jcanvas' => [
			'third-party/jcanvas.min.js'
		],
		'pixijs' => [
			'third-party/pixi.min.js'
		],
		'gallery' => [
			'third-party/colorbox/jquery.colorbox-min.js',
			'third-party/colorbox/jquery.colorbox-ro.js',
			'dexGallery.js',
		],
		'modelDropdown' => [
			'modelDropdown.js'
		],
		'textComplete' => [
			'third-party/jquery.textcomplete.min.js'
		],
		'tinymce' => [
			'third-party/tinymce-4.4.0/tinymce.min.js',
			'tinymce.js',
		],
		'meaningTree' => [
			'meaningTree.js'
		],
		'hotkeys' => [
			'third-party/jquery.hotkeys.js',
			'hotkeys.js',
		],
		'callToAction' => [
			'callToAction.js'
		],
		'seedrandom' => [
			'third-party/seedrandom.min.js'
		],
		'colorpicker' => [
			'third-party/bootstrap-colorpicker.min.js'
		],
		'diff' => [
			'diff.js'
		],
		'diffSelector' => [
			'diffSelector.js'
		],
		'bootstrap-spinedit' => [
			'third-party/bootstrap-spinedit.js'
		],
		'frequentObjects' => [
			'frequentObjects.js'
		],
		'bootstrap-datepicker' => [
			'third-party/bootstrap-datepicker.min.js',
			'third-party/bootstrap-datepicker.ro.min.js',
		],
		'adminIndex' => [
			'adminIndex.js'
		],
		'admin' => [
			'admin.js'
		],
		'sprintf' => [
			'third-party/sprintf.min.js'
		],
	];


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

	static function orderResources($mapping, $selected) {
		$result = [];
		foreach ($mapping as $name => $files) {
			if (isset($selected[$name])) {
				$result = array_merge($result, $files);
			}
		}
		return $result;
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
      self::addJs('admin', 'hotkeys', 'sprintf');
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
	self::$cssFiles = array_merge(
		self::orderResources(self::$cssMap, self::$includedCss),
		self::$cssFiles
	);
	self::assign('cssFile', self::mergeResources(self::$cssFiles, 'css'));

	self::$jsFiles = array_merge(
		self::orderResources(self::$jsMap, self::$includedJs),
		self::$jsFiles
	);
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
			if (!isset(self::$cssMap[$id])) {
				FlashMessage::add("Cannot load CSS file {$id}");
				Util::redirect(Core::getWwwRoot());
			}
			self::$includedCss[$id] = true;
    }
  }

  static function addJs(/* Variable-length argument list */) {
    // Note the priorities. This allows files to be added in any order, regardless of dependencies
    foreach (func_get_args() as $id) {
			if (!isset(self::$jsMap[$id])) {
				FlashMessage::add("Cannot load JS script {$id}");
				Util::redirect(Core::getWwwRoot());
			}
			self::$includedJs[$id] = true;
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
