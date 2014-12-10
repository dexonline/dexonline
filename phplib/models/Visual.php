<?php

class Visual extends BaseObject implements DatedObject {
  const STATIC_DIR = 'img/visual/';
  const STATIC_THUMB_DIR = 'img/visual/thumb/';
  public static $_table = 'Visual';
  public static $parentDir = 'visual';
  public static $thumbDir = '.thumb';
  public static $thumbSize = 200;

  static function createFromFile($fileName) {
    $v = Model::factory('Visual')->create();
    $v->path = $fileName;
    $v->userId = session_getUserId();

    $dim = getimagesize(Config::get('static.url') . self::STATIC_DIR . $fileName);
    $v->width = $dim[0];
    $v->height = $dim[1];

    $v->save();
    return $v;
  }

  /** Retrieves the path relative to the visual folder */
  static function getPath($givenPath) {
    $regex = '/' . self::$parentDir . '\/.+$/';
    preg_match($regex, $givenPath, $matches);
    
    return $matches[0];
  }

  function getImageUrl() {
    return Config::get('static.url') . self::STATIC_DIR . $this->path;
  }

  function getThumbUrl() {
    return Config::get('static.url') . self::STATIC_THUMB_DIR . $this->path;
  }

  /** Returns the absolute path of the thumb directory */
  function getThumbDir() {
    return util_getRootPath() . 'wwwbase/img/' . dirname($this->path) . '/' . self::$thumbDir;
  }

  /** Returns the absolute path of the thumbnail file */
  function getThumbPath() {
    return $this->getThumbDir() . '/' . basename($this->path);
  }

  /** Extended by deleting removed image thumbnails */
  function delete() {
    VisualTag::deleteByImageId($this->id);    

    $thumbPath = $this->getThumbPath();
    $thumbDirPath = $this->getThumbDir();

    if(file_exists($thumbPath)) {
      unlink($thumbPath);
    }

    if(file_exists($thumbDirPath) && OS::isDirEmpty($thumbDirPath)) {
      rmdir($thumbDirPath);
    }
    
    return parent::delete();
  }

  static function loadUntrackedFiles() {
    $imageExtensions = array('png', 'PNG', 'jpg', 'JPG', 'jpeg', 'JPEG', 'gif', 'GIF');

    // Create a map of all the files on the static server
    $staticFiles = file(Config::get('static.url') . 'fileList.txt');
    $map = array();
    $len = strlen(self::STATIC_DIR);
    foreach ($staticFiles as $f) {
      $f = trim($f);
      if (StringUtil::startsWith($f, self::STATIC_DIR) &&
          !StringUtil::startsWith($f, self::STATIC_THUMB_DIR) &&
          in_array(pathinfo($f, PATHINFO_EXTENSION), $imageExtensions)) {
        $fileName = substr($f, $len);
        $map[$fileName] = true;
      }
    }

    // Delete those we are already tracking
    $vs = Model::factory('Visual')->find_many();
    foreach ($vs as $v) {
      if (array_key_exists($v->path, $map)) {
        unset($map[$v->path]);
      }
    }
    return array_keys($map);
  }

}

?>
