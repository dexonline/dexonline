<?php

// Code common to all our Elfinder instances

class ElfinderUtil {
  //
  const DIRS = [
    'wotd'     => ['subdir' => 'img/wotd',     'alias' => 'Imagini pentru cuvântul zilei'],
    'expresii' => ['subdir' => 'img/expresii', 'alias' => 'Expresii vizuale'],
    'wotm'     => ['subdir' => 'img/wotm',     'alias' => 'Imagini pentru cuvântul lunii'],
    'proverbe' => ['subdir' => 'img/proverbe', 'alias' => 'Proverbe ilustrate'],
    'top'      => ['subdir' => 'img/top',      'alias' => 'Topul căutărilor'],
    ];

  // default elFinder (wotd, top)
  static function getOptionsMultiRoot() {
    $logger = new ElfinderSimpleLogger(Config::LOG_FILE);

    $subdir_wotd = 'img/wotd';
    $alias_wotd = 'Imagini pentru cuvântul zilei';
    $path_wotd = Config::STATIC_PATH . $subdir_wotd;
    @mkdir($path_wotd, 0777, true); // make sure the full path exists
    $root_wotd = [
          'driver'        => 'LocalFileSystem',
          'path'          => $path_wotd,
          'URL'           => Config::STATIC_URL . $subdir_wotd,
          'alias'         => $alias_wotd,
          'uploadAllow'   => ['image'], // mimetypes allowed to upload
          'disabled'      => ['resize', 'mkfile'],
          'imgLib'        => 'gd',
          // Thumbnails are still stored locally
          'tmbPath'       => Config::ROOT . 'www/img/generated',
          'tmbURL'        => Config::URL_PREFIX . 'img/generated',
        ];

    $subdir_provb = ElfinderUtil::DIRS['proverbe']['subdir'];
    $alias_provb = ElfinderUtil::DIRS['proverbe']['alias'];;
    $path_provb = Config::STATIC_PATH . $subdir_provb;
    @mkdir($path_provb, 0777, true); // make sure the full path exists
    $root_provb = [
      'driver'        => 'LocalFileSystem',
      'path'          => $path_provb,
      'URL'           => Config::STATIC_URL . $subdir_provb,
      'alias'         => $alias_provb,
      'uploadAllow'   => ['image'], // mimetypes allowed to upload
      'disabled'      => ['resize', 'mkfile'],
      'imgLib'        => 'gd',
      // Thumbnails are still stored locally
      'tmbPath'       => Config::ROOT . 'www/img/generated',
      'tmbURL'        => Config::URL_PREFIX . 'img/generated',
    ];

    $subdir_top = 'img/top/';
    $alias_top = 'Topul căutărilor';
    $path_top = Config::STATIC_PATH . $subdir_top;
    @mkdir($path_top, 0777, true); // make sure the full path exists
    $root_top = [
      'driver'        => 'LocalFileSystem',
      'path'          => $path_top,
      'URL'           => Config::STATIC_URL . $subdir_top,
      'alias'         => $alias_top,
      'uploadAllow'   => ['image'], // mimetypes allowed to upload
      'disabled'      => ['resize', 'mkfile'],
      'imgLib'        => 'gd',
      // Thumbnails are still stored locally
      'tmbPath'       => Config::ROOT . 'www/img/generated',
      'tmbURL'        => Config::URL_PREFIX . 'img/generated',
    ];

    $opts = [
      'bind'  => [
        'mkdir mkfile rename duplicate upload rm paste' => [$logger, 'log'],
        'mkdir mkfile rename duplicate upload rm paste' => ['StaticUtil::generateStaticFileList'],
        'upload.presave' => ['ElfinderUtil::cleanupFileName'],
      ],

      'roots' => [
        $root_wotd,
        $root_provb,
        $root_top,
      ],
    ];

    return $opts;
  }

  // $subdirectory: path relative to the volume root
  // $alias: text to display instead of the volume name
  static function getOptions($subdirectory, $alias) {
    $logger = new ElfinderSimpleLogger(Config::LOG_FILE);

    $root = [
      'driver'        => 'LocalFileSystem',
      'path'          => Config::STATIC_PATH . $subdirectory,
      'URL'           => Config::STATIC_URL . $subdirectory,
    ];
    @mkdir($root['path'], 0777, true); // make sure the full path exists

    $opts = [
      'bind'  => [
        'mkdir mkfile rename duplicate upload rm paste' => [$logger, 'log'],
        'mkdir mkfile rename duplicate upload rm paste' => ['StaticUtil::generateStaticFileList'],
        'upload.presave' => ['ElfinderUtil::cleanupFileName'],
      ],
      'roots' => [
        array_merge($root, [
          'alias'         => $alias,
          'uploadAllow'   => ['image'], // mimetypes allowed to upload
          'disabled'      => ['resize', 'mkfile'],
          'imgLib'        => 'gd',

          // Thumbnails are still stored locally
          'tmbPath'       => Config::ROOT . 'www/img/generated',
          'tmbURL'        => Config::URL_PREFIX . 'img/generated',
        ]),
      ],
    ];

    return $opts;
  }

  static function cleanupFileName(&$path, &$name, $tmpname, $elfinder, $volume) {
    $name = Str::cleanup($name);

    // a bit of standardization
    // lowercase extension, e.g. JPG -> jpg
    $name = preg_replace_callback('/\.\w+$/', function($m) {
      return strtolower($m[0]);
    }, $name);

    $name = str_replace([' ', '_'], '-', $name);
    $name = str_replace('-.', '.', $name);
    $name = str_replace('jpeg', 'jpg', $name);

    // play the guessing game
    if (Str::endsWith($name, '.')) {
      $name .= 'jpg';
    }
  }
}
