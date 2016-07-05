<?php

class Tree extends BaseObject implements DatedObject {
  public static $_table = 'Tree';

  static function createAndSave($description) {
    $t = Model::factory('Tree')->create();
    $t->description = $description;
    $t->save();
    return $t;
  }

  public function delete() {
    TreeEntry::delete_all_by_treeId($this->id);
    Log::warning("Deleted tree {$this->id} ({$this->description})");
    parent::delete();
  }

}

?>
