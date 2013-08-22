<?php

class Visual extends BaseObject implements DatedObject {
  public static $_table = 'Visual';
  public static $parentDir = 'visual';
  public static $thumbDir = '.thumb';
  public static $thumbSize = 200;
  public static $cmd, $oldThumbPath;

  /** Retrieves the path relative to the visual folder */
  public static function getPath($givenPath) {
    $regex = '/' . self::$parentDir . '\/.+$/';
    preg_match($regex, $givenPath, $matches);
    
    return $matches[0];
  }

  /** Creates the absolute path of the thumb directory based on the $path parameter */
  static function getThumbDirPath($path) {
    preg_match('/[^\/]+$/', $path, $name);
    $path = str_replace($name[0], '', $path);
    
    return util_getRootPath() . 'wwwbase/img/' . $path . self::$thumbDir;
  }

  /** Creates the absolute path of the thumbnail file based on the $path parameter */
  static function getThumbPath($path) {
    preg_match('/[^\/]+$/', $path, $name);
    $path = str_replace($name[0], '', $path);

    return util_getRootPath() . 'wwwbase/img/' . $path . self::$thumbDir . '/' . $name[0];
  }

  /** Checks if the directory specified in $path is empty */
  static function isDirEmpty($path) {
    $files = scandir($path);
    if(count($files) == 2) {
      return true;
    } else {
      return false;
    }
  }

  /** Extended by deleting removed image thumbnails */
  function delete() {
    VisualTag::deleteByImageId($this->id);    

    $thumbPath = self::getThumbPath($this->path);
    $thumbDirPath = self::getThumbDirPath($this->path);

    if(file_exists($thumbPath)) {
      unlink($thumbPath);
    }

    if(file_exists($thumbDirPath) && self::isDirEmpty($thumbDirPath)) {
      rmdir($thumbDirPath);
    }
    
    parent::delete();
  }

  /** Extended by creating uploaded image thumbnail */
  function save() {
    switch(self::$cmd) {
    case 'upload':
    case 'copy-paste':
      $thumbDirPath = self::getThumbDirPath($this->path);

      if(!file_exists($thumbDirPath)) {
        mkdir($thumbDirPath, 0777);
      }

      $thumbPath = self::getThumbPath($this->path); 
      $thumb = new Imagick(util_getRootPath() . 'wwwbase/img/' . $this->path);
      $thumb->thumbnailImage(self::$thumbSize, self::$thumbSize, true);
      $thumb->writeImage( $thumbPath);
    break;

    case 'rename':
    case 'cut-paste':
      $newThumbPath = self::getThumbPath($this->path);

      if(file_exists(self::$oldThumbPath)) {
        rename(self::$oldThumbPath, $newThumbPath);
      }
    break;
    }

    parent::save();
  }
}
?>
