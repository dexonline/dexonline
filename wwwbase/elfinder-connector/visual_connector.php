<?php

include_once __DIR__ . '/autoload.php';
include_once __DIR__ . '/elFinderLogger.class.php';
include_once __DIR__ . '/VisualElFinder.php';

include_once __DIR__ . '/../../phplib/util.php';

$myLogger = new elFinderSimpleLogger(Config::get('logging.file'));

// https://github.com/Studio-42/elFinder/wiki/Connector-configuration-options
$opts = [
  'bind'  => [
    'mkdir mkfile rename duplicate upload rm paste' => [$myLogger, 'log'],
  ],
  'roots' => [
    [
      'driver'        => 'FTP',
      'host'          => Config::get('static.host'),
      'user'          => Config::get('static.user'),
      'pass'          => Config::get('static.password'),
      'path'          => Config::get('static.path') . 'img/visual/',
      'timeout'       => Config::get('static.timeout'),
      'URL'           => Config::get('static.url') . 'img/visual/',
      'alias'         => 'Ilustrații definiții',
      'uploadAllow'   => ['image'], // mimetypes allowed to upload
      'disabled'      => ['resize', 'mkfile', 'duplicate'], // list of not allowed commands
      'imgLib'        => 'gd',

      // Thumbnails are still stored locally
      'tmbPath'       => '../img/generated',
      'tmbURL'        => '../img/generated',
    ],
  ],
];

// run elFinder
$connector = new elFinderConnector(new VisualElFinder($opts));
$connector->run();

?>
