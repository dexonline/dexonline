<?php

class Visual extends BaseObject implements DatedObject {
  public static $_table = 'Visual';

  const STATIC_DIR = 'img/visual/';
  const STATIC_THUMB_DIR = 'img/visual/thumb/';
  const THUMB_SIZE = 200;

  static function createFromFile($fileName) {
    $v = Model::factory('Visual')->create();
    $v->path = $fileName;
    $v->userId = session_getUserId();

    $url = Config::get('static.url') . self::STATIC_DIR . $fileName;
    $dim = getimagesize($url);
    $v->width = $dim[0];
    $v->height = $dim[1];
    $v->save();

    $v->createThumb();

    return $v;
  }

  function getImageUrl() {
    return Config::get('static.url') . self::STATIC_DIR . $this->path;
  }

  function getThumbUrl() {
    return Config::get('static.url') . self::STATIC_THUMB_DIR . $this->path;
  }

  function thumbExists() {
    return FtpUtil::staticServerFileExists(self::STATIC_THUMB_DIR . $this->path);
  }

  function createThumb() {
    $url = Config::get('static.url') . self::STATIC_DIR . $this->path;
    $ext = pathinfo($url, PATHINFO_EXTENSION);
    $localFile = "/tmp/a.{$ext}";
    $localThumbFile = "/tmp/thumb.{$ext}";
    $contents = file_get_contents($url);
    file_put_contents($localFile, $contents);
    $command = sprintf("convert -strip -geometry %sx%s -sharpen 1x1 '%s' '%s'",
                       self::THUMB_SIZE, self::THUMB_SIZE, $localFile, $localThumbFile);
    OS::executeAndAssert($command);
    FtpUtil::staticServerPut($localThumbFile, self::STATIC_THUMB_DIR . $this->path);
    unlink($localFile);
    unlink($localThumbFile);
  }

  function delete() {
    // TODO: Delete thumbnail and its directory (if it becomes empty)
    VisualTag::delete_all_by_imageId($this->id);    
    return parent::delete();
  }
}

?>
