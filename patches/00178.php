<?php

// Create empty trees for entries that have no trees.

define('BATCH_SIZE', 1000);

$offset = 0;

do {
  $entries = Model::factory('Entry')
           ->limit(BATCH_SIZE)
           ->offset($offset)
           ->find_many();

  foreach ($entries as $e) {
    $tes = TreeEntry::get_all_by_entryId($e->id);
    if (!count($tes)) {
      $t = Tree::createAndSave($e->description);
      $te = Model::factory('TreeEntry')->create();
      $te->treeId = $t->id;
      $te->entryId = $e->id;
      $te->save();
    }
  }

  $offset += BATCH_SIZE;
  Log::info("Processed $offset lexems.");
} while (count($entries));
