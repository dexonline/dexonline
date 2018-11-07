<?php

// Code common to all our Elfinder instances

class ElfinderUtil {

  // $subdirectory: path relative to the volume root
  // $alias: text to display instead of the volume name
  static function getOptions($subdirectory, $alias) {
    $logger = new ElfinderSimpleLogger(Config::get('logging.file'));

    $driver = Config::get('elfinder.driver');
    switch ($driver) {
      case 'ftp':
        $root = [
          'driver'        => 'FTP',
          'host'          => Config::get('static.host'),
          'user'          => Config::get('static.user'),
          'pass'          => Config::get('static.password'),
          'path'          => Config::get('static.path') . $subdirectory,
          'timeout'       => Config::get('static.timeout'),
          'URL'           => Config::get('static.url') . $subdirectory,
        ];
        break;

      case 'local':
        $root = [
          'driver'        => 'LocalFileSystem',
          'path'          => Config::get('elfinder.path') . '/' . $subdirectory,
          'URL'           => Config::get('elfinder.url') . '/' . $subdirectory,
        ];
        @mkdir($root['path'], 0777, true); // make sure the full path exists
        break;

      default:
        $root = [];
    }

    $opts = [
      'bind'  => [
        'mkdir mkfile rename duplicate upload rm paste' => [$logger, 'log'],
        'upload.presave' => ['ElfinderUtil::cleanupFileName'],
      ],
      'roots' => [
        array_merge($root, [
          'alias'         => $alias,
          'uploadAllow'   => ['image'], // mimetypes allowed to upload
          'disabled'      => ['resize', 'mkfile'],
          'imgLib'        => 'gd',

          // Thumbnails are still stored locally
          'tmbPath'       => '../img/generated',
          'tmbURL'        => '../img/generated',
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
