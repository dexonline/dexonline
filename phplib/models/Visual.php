<?php

class Visual extends BaseObject implements DatedObject {
  public static $_table = 'Visual';
  public static $parentDir = 'visual';
  public static $thumbDir = '.thumb';
  public static $cmd, $altPath;

  /** Retrieves the path relative to the visual folder */
  public static function getPath($givenPath) {
    $regex = '/' . self::$parentDir . '\/.+$/';
    preg_match($regex, $givenPath, $matches);
    
    return $matches[0];
  }

  /** Creates the absolute path of the thumb directory based on the $path parameter */
  static function thumbDirPath($path) {
    preg_match('/[^\/]+$/', $path, $name);
    $path = str_replace($name[0], '', $path);
    
    return util_getRootPath() . 'wwwbase/img/' . $path . self::$thumbDir;
  }

  /** Creates the absolute path of the thumbnail file based on the $path parameter */
  static function thumbPath($path) {
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

    $thumbPath = self::thumbPath($this->path);
    $thumbDirPath = self::thumbDirPath($this->path);

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
    $thumbDirPath = self::thumbDirPath($this->path);

    if(!file_exists($thumbDirPath)) {
      mkdir($thumbDirPath);
    }

    $thumbPath = self::thumbPath($this->path); 
    $thumb = new Imagick(util_getRootPath() . 'wwwbase/img/' . $this->path);
    $thumb->thumbnailImage(200, 200, true);
    $thumb->writeImage( $thumbPath);

    parent::save();
  }
}
?>
