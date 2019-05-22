<?php

// Code common to all our Elfinder instances

class ElfinderUtil {

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
  }
}
