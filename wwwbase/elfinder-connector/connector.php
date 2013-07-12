<?php

error_reporting(E_ALL); // Set E_ALL for debuging

include_once __DIR__ . '/elFinderConnector.class.php';
include_once __DIR__ . '/elFinder.class.php';
include_once __DIR__ . '/elFinderVolumeDriver.class.php';
include_once __DIR__ . '/elFinderVolumeLocalFileSystem.class.php';
// Required for MySQL storage connector
// include_once __DIR__ . '/elFinderVolumeMySQL.class.php';
// Required for FTP connector support
// include_once __DIR__ . '/elFinderVolumeFTP.class.php';

if(function_exists('date_default_timezone_set')) {
	date_default_timezone_set('Europe/Moscow');
}

include_once __DIR__ . '/../../phplib/util.php';
include_once __DIR__ . '/elFinder.class.php';

/**
 * Simple function to demonstrate how to control file access using "accessControl" callback.
 * This method will disable accessing files/folders starting from '.' (dot)
 *
 * @param  string  $attr  attribute name (read|write|locked|hidden)
 * @param  string  $path  file path relative to volume root directory started with directory separator
 * @return bool|null
 **/

function access($attr, $path, $data, $volume) {
	return (strpos(basename($path), '.')) === 0       // if file/folder begins with '.' (dot)
		? !($attr == 'read' || $attr == 'write')    // set read+write to false, other (locked+hidden) set to true
		:  null;                                    // else elFinder decide it itself
}

/**
 * Simple logger function.
 * Demonstrate how to work with elFinder event api.
 *
 * @package elFinder
 * @author Dmitry (dio) Levashov
 **/
class elFinderSimpleLogger {
    
    /**
     * Log file path
     *
     * @var string
     **/
    protected $file = '';
    
    /**
     * constructor
     *
     * @return void
     * @author Dmitry (dio) Levashov
     **/
    public function __construct($path) {
        $this->file = $path;
        $dir = dirname($path);
        if (!is_dir($dir)) {
            mkdir($dir);
        }
    }
    
    /**
     * Create log record
     *
     * @param  string   $cmd       command name
     * @param  array    $result    command result
     * @param  array    $args      command arguments from client
     * @param  elFinder $elfinder  elFinder instance
     * @return void|true
     * @author Dmitry (dio) Levashov
     **/
    public function log($cmd, $result, $args, $elfinder) {
        $log = $cmd.' ['.date('d.m H:s')."]\n";
        
        if (!empty($result['error'])) {
            $log .= "\tERROR: ".implode(' ', $result['error'])."\n";
        }
        
        if (!empty($result['warning'])) {
            $log .= "\tWARNING: ".implode(' ', $result['warning'])."\n";
        }
        
        if (!empty($result['removed'])) {
            foreach ($result['removed'] as $file) {
                // removed file contain additional field "realpath"
                $log .= "\tREMOVED: ".$file['realpath']."\n";
            }
        }
        
        if (!empty($result['added'])) {
            foreach ($result['added'] as $file) {
                $log .= "\tADDED: ".$elfinder->realpath($file['hash'])."\n";
            }
        }
        
        if (!empty($result['changed'])) {
            foreach ($result['changed'] as $file) {
                $log .= "\tCHANGED: ".$elfinder->realpath($file['hash'])."\n";
            }
        }
        
        $this->write($log);
    }
    
    /**
     * Write log into file
     *
     * @param  string  $log  log record
     * @return void
     * @author Dmitry (dio) Levashov
     **/
    protected function write($log) {
        if (($fp = @fopen($this->file, 'a'))) {
            fwrite($fp, $log."\n");
            fclose($fp);
        }
    }
}

$myLogger = new elFinderSimpleLogger('../img/wotd/_log.txt');

// https://github.com/Studio-42/elFinder/wiki/Connector-configuration-options
$opts = array(
	'debug' => true,
	'bind'	=> array(
			'mkdir mkfile rename duplicate upload rm paste' => array($myLogger, 'log')),
	'roots' => array(
		array(
			'driver'        => 'LocalFileSystem', // driver for accessing file system (REQUIRED)
			'path'          => '../img/wotd/', // path to files (REQUIRED)
			'URL'			=> util_getFullServerUrl() . '/img/wotd/', // URL to files (REQUIRED)
			'accessControl' => 'access', // disable and hide dot starting files (OPTIONAL)
			'alias'			=> 'Imagini cuvântul zilei', // display this instead of root directory name
			'uploadAllow'	=> array('image'), // mimetypes allowed to upload
			'disabled'		=> array('resize'), // list of not allowed commands
			'imgLib'		=> 'gd', // image manipulation library (imagick, mogrify, gd)
			'tmbPath'		=> '.tmb', // directory name for image thumbnails. Set to "" to avoid thumbnails generation
		)
	)
);

// run elFinder
$connector = new elFinderConnector(new elFinder($opts));
$connector->run();

