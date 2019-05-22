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

    $url = Config::STATIC_URL . self::STATIC_DIR . $fileName;
    $dim = getimagesize($url);
    $v->width = $dim[0];
    $v->height = $dim[1];
    $v->save();

    return $v;
  }

  function getTitle() {
    if ($this->entry === null) {
      $this->entry = Entry::get_by_id($this->entryId);
    }
    return $this->entry ? $this->entry->description : '';
  }

  function getImageUrl() {
    return Config::STATIC_URL . self::STATIC_DIR . $this->path;
  }

  function getThumbUrl() {
    StaticUtil::ensureThumb(
      self::STATIC_DIR . $this->path,
      self::STATIC_THUMB_DIR . $this->path,
      self::THUMB_SIZE);
    return Config::STATIC_URL . self::STATIC_THUMB_DIR . $this->path;
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
