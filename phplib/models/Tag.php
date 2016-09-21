<?php

class Tag extends BaseObject implements DatedObject {
  public static $_table = 'Tag';

  // fields populated during loadTree()
  public $canDelete = 1;
  public $children = [];

  static function loadByDefinitionId($defId) {
    return Model::factory('Tag')
      ->select('Tag.*')
      ->join('DefinitionTag', ['Tag.id', '=', 'tagId'])
      ->where('DefinitionTag.definitionId', $defId)
      ->order_by_asc('value')
      ->find_many();
  }

  static function loadByMeaningId($meaningId) {
    return Model::factory('Tag')
      ->select('Tag.*')
      ->join('MeaningTag', ['Tag.id', '=', 'tagId'])
      ->where('MeaningTag.meaningId', $meaningId)
      ->order_by_asc('value')
      ->find_many();
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
    foreach (['DefinitionTag', 'MeaningTag', 'LexemTag'] as $table) {
      $usedIds = Model::factory($table)
               ->select('tagId')
               ->distinct()
               ->find_many();
      foreach ($usedIds as $rec) {
        $map[$rec->tagId]->canDelete = 0;
      }
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

  public function delete() {
    Log::warning("Deleted tag {$this->id} ({$this->value})");
    parent::delete();
  }
}

?>
