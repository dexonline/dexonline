<?php

include_once __DIR__ . '/autoload.php';
include_once __DIR__ . '/elFinderLogger.class.php';

include_once __DIR__ . '/../../phplib/Core.php';

$myLogger = new elFinderSimpleLogger('../../log/wotdelflog');

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
      'path'          => Config::get('static.path') . 'img/wotd/',
      'timeout'       => Config::get('static.timeout'),
      'URL'           => Config::get('static.url') . 'img/wotd/',
      'alias'         => 'Imagini cuvântul zilei', // display this instead of root directory name
      'uploadAllow'   => ['image'], // mimetypes allowed to upload
      'imgLib'        => 'gd',

      // Thumbnails are still stored locally
      'tmbPath'       => '../img/generated',
      'tmbURL'        => '../img/generated',
    ],
  ],
];

// run elFinder
$connector = new elFinderConnector(new elFinder($opts));
$connector->run();

?>
