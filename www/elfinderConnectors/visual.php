<?php

require_once '../../phplib/Core.php';
require_once '../../phplib/third-party/elfinder/autoload.php';

/**
 * Triggers for some commands.
 **/
class VisualElFinder extends elFinder {
	function __construct($opts) {
    parent::__construct($opts);
    $this->commands['tagimage'] = ['target' => true];
  }

  private function getPath($target) {
    $volume = $this->volume($target);
    $path = $volume->path($target);

    // Ignore the volume name (and leading /, if present)
    $pos = strpos($path, '/');
    if ($pos === FALSE) {
      return '';
    } else {
      return substr($path, 1 + $pos);
    }
  }

  protected function paste($args) {
    $result = parent::paste($args);
    $dest  = $this->getPath($args['dst']);
		$cut  = !empty($args['cut']);

    if ($cut) {
      foreach ($args['targets'] as $target) {
        // If the image has a corresponding Visual, update its .path field and move its thumbnail.
        $path = $this->getPath($target);
        $v = Visual::get_by_path($path);
        if ($v) {
          $base = basename($path);
          $v->path = $dest ? "$dest/$base" : $base;
          $v->save();
        }
      }
    }

    return $result;
  }

  protected function rename($args) {
    $result = parent::rename($args);
    $path  = $this->getPath($args['target']);
    $name = $args['name'];

    // If the image has a corresponding Visual, update its .path field and rename its thumbnail.
    $v = Visual::get_by_path($path);
    if ($v) {
      $dir = dirname($v->path);
      $v->path = ($dir == '.') ? $name : "$dir/$name";
      $v->save();
    }

    return $result;
  }

  protected function rm($args) {
    $result = parent::rm($args);

    foreach ($args['targets'] as $target) {
      // If the image has a corresponding Visual, remove it
      $path = $this->getPath($target);
      $v = Visual::get_by_path($path);
      if ($v) {
        $v->delete();
      }
    }

    return $result;
  }

  protected function tagimage($args) {
		$target = $args['target'];
    $path = $this->getPath($args['target']);
    return ['path' => $path];
  }
}

$opts = ElfinderUtil::getOptions('img/visual/', 'Dicționarul vizual');

// run elFinder
$connector = new elFinderConnector(new VisualElFinder($opts));
$connector->run();
