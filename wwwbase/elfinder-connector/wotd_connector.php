<?php

error_reporting(0); // Set E_ALL for debuging

include_once __DIR__ . '/elFinderConnector.class.php';
include_once __DIR__ . '/elFinder.class.php';
include_once __DIR__ . '/elFinderVolumeDriver.class.php';
include_once __DIR__ . '/elFinderVolumeFTP.class.php';
include_once __DIR__ . '/elFinderLogger.class.php';

include_once __DIR__ . '/../../phplib/util.php';

$myLogger = new elFinderSimpleLogger('../../log/wotdelflog');

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
      'path'          => Config::get('static.path') . 'img/wotd/',
      'timeout'       => Config::get('static.timeout'),
      'URL'           => Config::get('static.url') . 'img/wotd/',
      'alias'         => 'Imagini cuvântul zilei', // display this instead of root directory name
      'uploadAllow'   => array('image'), // mimetypes allowed to upload
      'imgLib'        => 'gd',

      // Thumbnails are still stored locally
      'tmbPath'       => '../img/wotd/thumb',
      'tmbURL'        => '../img/wotd/thumb',
    )
  )
);

// run elFinder
$connector = new elFinderConnector(new elFinder($opts));
$connector->run();

?>
