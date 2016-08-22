<?php

// Relations now point to trees instead of lexems.
// However, some lexem's entries are not associated with any trees.
// Create trees where necessary.

$rels = Model::factory('Relation')->find_many();

foreach ($rels as $r) {
  $l = Lexem::get_by_id($r->lexemId);
  $e = Entry::get_by_id($l->entryId);
  $tes = TreeEntry::get_all_by_entryId($e->id);

  if (!count($tes)) {
    $t = Tree::createAndSave($e->description);
    TreeEntry::associate($t->id, $e->id);
  } else {
    $t = Tree::get_by_id($tes[0]->treeId);
  }

  $r->treeId = $t->id;
  $r->save();
}
