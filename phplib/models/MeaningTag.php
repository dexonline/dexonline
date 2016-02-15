<?php

class MeaningTag extends BaseObject implements DatedObject {
  public static $_table = 'MeaningTag';

  // fields populated during loadTree()
  public $canDelete = 1;
  public $children = [];

  static function loadByMeaningId($meaningId) {
    return Model::factory('MeaningTag')
      ->select('MeaningTag.*')
      ->join('MeaningTagMap', array('MeaningTag.id', '=', 'meaningTagId'))
      ->where('MeaningTagMap.meaningId', $meaningId)
      ->order_by_asc('value')
      ->find_many();
  }

  // Returns an array of root tags with their $children and $canDelete fields populated
  static function loadTree() {
    $tags = Model::factory('MeaningTag')->order_by_asc('displayOrder')->find_many();

    // Map the tags by id
    $map = [];
    foreach ($tags as $t) {
      $map[$t->id] = $t;
    }

    // Mark tags which can be deleted
    $usedIds = Model::factory('MeaningTagMap')
             ->select('meaningTagId')
             ->distinct()
             ->find_many();
    foreach ($usedIds as $rec) {
      $map[$rec->meaningTagId]->canDelete = 0;
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
}

?>
