<?php

class TreeEntry extends BaseObject implements DatedObject {
  public static $_table = 'TreeEntry';

  public static function associate($treeId, $entryId) {
    // The tree and the entry should exist
    $tree = Tree::get_by_id($treeId);
    $entry = Entry::get_by_id($entryId);
    if (!$tree || !$entry) {
      return;
    }

    // The association itself should not exist.
    $te = self::get_by_treeId_entryId($treeId, $entryId);
    if (!$te) {
      $te = Model::factory('TreeEntry')->create();
      $te->treeId = $treeId;
      $te->entryId = $entryId;
      $te->save();
    }
  }
}

?>
