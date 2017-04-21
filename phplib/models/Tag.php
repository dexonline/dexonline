<?php

class Tag extends BaseObject implements DatedObject {
  public static $_table = 'Tag';

  // fields populated during loadTree()
  public $canDelete = 1;
  public $children = [];

  static function loadByObject($objectType, $objectId) {
    return Model::factory('Tag')
      ->select('Tag.*')
      ->join('ObjectTag', ['Tag.id', '=', 'tagId'])
      ->where('ObjectTag.objectType', $objectType)
      ->where('ObjectTag.objectId', $objectId)
      ->order_by_asc('ObjectTag.id')
      ->find_many();
  }

  static function loadByDefinitionId($defId) {
    return self::loadByObject(ObjectTag::TYPE_DEFINITION, $defId);
  }

  static function loadByMeaningId($meaningId) {
    return self::loadByObject(ObjectTag::TYPE_MEANING, $meaningId);
  }

  // Returns an array of root tags with their $children and $canDelete fields populated
  static function loadTree() {
    $tags = Model::factory('Tag')->order_by_asc('displayOrder')->find_many();

    // Map the tags by id
    $map = [];
    foreach ($tags as $t) {
      $map[$t->id] = $t;
    }

    // Mark tags which can be deleted
    $usedIds = Model::factory('ObjectTag')
             ->select('tagId')
             ->distinct()
             ->find_many();
    foreach ($usedIds as $rec) {
      $map[$rec->tagId]->canDelete = 0;
    }

    // Make each tag its parent's child
    foreach ($tags as $t) {
      if ($t->parentId) {
        $p = $map[$t->parentId];
        $p->children[$t->displayOrder] = $t;
      }
    }

    // Return just the roots
    return array_filter($tags, function($t) {
      return !$t->parentId;
    });
  }

  function getAncestors() {
    $p = $this;
    $result = [];

    do {
      array_unshift($result, $p);
      $p = Tag::get_by_id($p->parentId);
    } while ($p);

    return $result;
  }

  function delete() {
    ObjectTag::delete_all_by_tagId($this->id);
    parent::delete();
    Log::warning("Deleted tag {$this->id} ({$this->value})");
  }

  function __toString() {
    return "[{$this->value}]";
  }
}

?>
