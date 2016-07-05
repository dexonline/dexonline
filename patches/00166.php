<?php

// Create Trees as containers for meaning trees.
// Associate these trees with entries as dictated by the old Meaning.lexemId
// field.

$lexemTreeMap = []; // maps lexem IDs to tree IDs
$meanings = Model::factory('Meaning')->find_many();

foreach ($meanings as $m) {
  if (!isset($lexemTreeMap[$m->lexemId])) {
    // load the lexem to get its form
    $l = Lexem::get_by_id($m->lexemId);

    // create a new tree
    $t = Tree::createAndSave((string)($l));

    // map this tree to the lexem's entry
    TreeEntry::associate($t->id, $l->entryId);

    // add a record to the lexem => tree map
    $lexemTreeMap[$l->id] = $t->id;
  }

  // set the Meaning.treeId field
  $m->treeId = $lexemTreeMap[$m->lexemId];
  $m->save();
}
