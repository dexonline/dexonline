<?php

error_reporting(E_ALL); // Set E_ALL for debuging

include_once __DIR__ . '/elFinderConnector.class.php';
include_once __DIR__ . '/elFinder.class.php';
include_once __DIR__ . '/elFinderVolumeDriver.class.php';
include_once __DIR__ . '/elFinderVolumeFTP.class.php';
include_once __DIR__ . '/elFinderLogger.class.php';
include_once __DIR__ . '/VisualElFinder.php';

include_once __DIR__ . '/../../phplib/util.php';

$myLogger = new elFinderSimpleLogger(Config::get('logging.file'));

// https://github.com/Studio-42/elFinder/wiki/Connector-configuration-options
$opts = array(
  'debug' => true,
  'bind'  => array('mkdir mkfile rename duplicate upload rm paste' => array($myLogger, 'log'),
                   ),
  'roots' => array(
    array(
      'driver'        => 'FTP',
      'host'          => Config::get('static.host'),
      'user'          => Config::get('static.user'),
      'pass'          => Config::get('static.password'),
      'path'          => Config::get('static.path') . 'img/visual/',
      'timeout'       => Config::get('static.timeout'),
      'URL'           => Config::get('static.url') . 'img/visual/',
      'alias'         => 'Ilustrații definiții',
      'uploadAllow'   => array('image'), // mimetypes allowed to upload
      'disabled'      => array('resize', 'mkfile', 'duplicate'), // list of not allowed commands
      'imgLib'        => 'gd',

      // Thumbnails are still stored locally
      'tmbPath'       => '../img/generated',
      'tmbURL'        => '../img/generated',
    )
  )
);

// run elFinder
$connector = new elFinderConnector(new VisualElFinder($opts));
$connector->run();

?>
