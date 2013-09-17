<?php
class Visual extends BaseObject implements DatedObject {
  public static $_table = 'Visual';
  public static $parentDir = 'visual';
  public static $thumbDir = '.thumb';
  public static $thumbSize = 200;

  /** Retrieves the path relative to the visual folder */
  public static function getPath($givenPath) {
    $regex = '/' . self::$parentDir . '\/.+$/';
    preg_match($regex, $givenPath, $matches);
    
    return $matches[0];
  }

  public static function getImageWww($givenPath) {
    return util_getImgRoot() . '/' . $givenPath;
  }

  public static function getThumbWww($givenPath) {
    return util_getImgRoot() . '/' . dirname($givenPath) . '/' . self::$thumbDir . '/' . basename($givenPath);
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

  /** Extended by creating uploaded image thumbnail */
  function save() {
    $path = util_getRootPath() . 'wwwbase/img/' . $this->path;
    
    // Saves the image size for further use when scale is needed
    if(!$this->width || !$this->height) {
      $dim = getimagesize($path);
      $this->width = $dim[0];
      $this->height = $dim[1];
    }

    // Make a directory into which to generate or copy the thumbnail
    @mkdir($this->getThumbDir(), 0777);

    // Load the original Visual to determine if this is a move/rename or a copy/upload. It slows things down a little, but it's stateless.
    $original = ($this->id) ? self::get_by_id($this->id) : null;

    // Generate thumbnails for uploads or copy-pastes
    if (!$original) {
      $thumb = new Imagick($path);
      $thumb->thumbnailImage(0, self::$thumbSize);
      $thumb->sharpenImage(1, 1);
      $thumb->writeImage($this->getThumbPath());
    }

    // Move thumbnails for renames and cut-pastes
    if ($original && ($original->path != $this->path)) {
      $oldThumbPath = $original->getThumbPath();
      $newThumbPath = $this->getThumbPath();
      if (file_exists($oldThumbPath)) {
        rename($oldThumbPath, $newThumbPath);
      }
    }

    return parent::save();
  }
}
?>
