<?php

class Visual extends BaseObject implements DatedObject {
  public static $_table = 'Visual';

  const STATIC_DIR = 'img/visual/';
  const STATIC_THUMB_DIR = 'img/visual/thumb/';
  const THUMB_SIZE = 150;

  private $entry = null;

  static function createFromFile($fileName) {
    $v = Model::factory('Visual')->create();
    $v->path = $fileName;
    $v->userId = User::getActiveId();

    $url = Config::get('static.url') . self::STATIC_DIR . $fileName;
    $dim = getimagesize($url);
    $v->width = $dim[0];
    $v->height = $dim[1];
    $v->save();

    $v->createThumb();

    return $v;
  }

  function getTitle() {
    if ($this->entry === null) {
      $this->entry = Entry::get_by_id($this->entryId);
    }
    return $this->entry ? $this->entry->description : '';
  }

  function getImageUrl() {
    return Config::get('static.url') . self::STATIC_DIR . $this->path;
  }

  function getThumbUrl() {
    return Config::get('static.url') . self::STATIC_THUMB_DIR . $this->path;
  }

  function thumbExists() {
    $f = new FtpUtil();
    return $f->staticServerFileExists(self::STATIC_THUMB_DIR . $this->path);
  }

  function createThumb() {
    $url = Config::get('static.url') . self::STATIC_DIR . $this->path;
    $ext = pathinfo($url, PATHINFO_EXTENSION);
    $localFile = Core::getTempPath() ."/a.{$ext}";
    $localThumbFile = Core::getTempPath() ."/thumb.{$ext}";
    $contents = file_get_contents($url);
    file_put_contents($localFile, $contents);
    $command = sprintf("convert -strip -geometry %sx%s -sharpen 1x1 '%s' '%s'",
                       self::THUMB_SIZE, self::THUMB_SIZE, $localFile, $localThumbFile);
    OS::executeAndAssert($command);
    $f = new FtpUtil();
    $f->staticServerPut($localThumbFile, self::STATIC_THUMB_DIR . $this->path);
    unlink($localFile);
    unlink($localThumbFile);
  }

  // Loads all Visuals that are associated with one of the entries,
  // either directly or through a VisualTag.
  static function loadAllForEntries($entries) {
    if (empty($entries)) {
      return [];
    }

    $map = [];
    $entryIds = Util::objectProperty($entries, 'id');

    $vs = Model::factory('Visual')
        ->where_in('entryId', $entryIds)
        ->find_many();
    foreach ($vs as $v) {
      $map[$v->id] = $v;
    }

    $vts = Model::factory('VisualTag')
         ->where_in('entryId', $entryIds)
         ->find_many();
    foreach ($vts as $vt) {
      $v = Visual::get_by_id($vt->imageId);
      $map[$v->id] = $v;
    }

    return array_values($map);
  }

  function delete() {
    // TODO: Delete thumbnail and its directory (if it becomes empty)
    VisualTag::delete_all_by_imageId($this->id);    
    return parent::delete();
  }
}
