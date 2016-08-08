<?php

// Variants now have one empty meaning with sources and tags on it.
// Move the sources and tags to the lexem.

$variants = Model::factory('Lexem')
          ->where_gt('variantOfId', 0)
          ->find_many();

foreach ($variants as $v) {
  $p = Lexem::get_by_id($v->variantOfId);

  $trees = Model::factory('Tree')
         ->table_alias('t')
         ->select('t.*')
         ->join('TreeEntry', ['t.id', '=', 'te.treeId'], 'te')
         ->where('te.entryId', $v->entryId)
         ->find_many();
  foreach ($trees as $t) {
    Log::info("Processing meanings for {$v->form} {$v->variantOfId} ==> {$p->form}");
    $meanings = Meaning::get_all_by_treeId($t->id);
    assert(count($meanings) == 1);
    $m = $meanings[0];
    assert($m->internalRep == '');

    // Convert MeaningSources on $m to LexemSources on $v
    $mss = MeaningSource::get_all_by_meaningId($m->id);
    foreach ($mss as $ms) {
      Log::info("Moving {$ms->sourceId} to lexem");
      $ls = Model::factory('LexemSource')->create();
      $ls->lexemId = $v->id;
      $ls->sourceId = $ms->sourceId;
      $ls->save();
    }
    MeaningSource::delete_all_by_meaningId($m->id);

    // Convert MeaningTags on $m to LexemTags on $v
    $mts = MeaningTag::get_all_by_meaningId($m->id);
    foreach ($mts as $mt) {
      Log::info("Moving {$mt->tagId} to lexem");
      $lt = Model::factory('LexemTag')->create();
      $lt->lexemId = $v->id;
      $lt->tagId = $mt->tagId;
      $lt->save();
    }
    MeaningTag::delete_all_by_meaningId($m->id);

    // There shouldn't be any relations. If there are, print a big warning.
    $relations = Relation::get_all_by_meaningId($m->id);
    if (count($relations)) {
      print "**************** Warning: {$v->form} has relations, please migrate them manually.";
    }

    $t->status = Tree::ST_HIDDEN;
    $t->save();
  }

  // Move $v's entry's definitions to $p's entry
  $eds = EntryDefinition::get_all_by_entryId($v->entryId);
  foreach ($eds as $ed) {
    EntryDefinition::associate($p->entryId, $ed->definitionId);
  }
  EntryDefinition::delete_all_by_entryId($v->entryId);

  // Move $v's entry's trees to $p's entry. The code above made them hidden.
  $tes = TreeEntry::get_all_by_entryId($v->entryId);
  foreach ($tes as $te) {
    TreeEntry::associate($te->treeId, $p->entryId);
  }
  TreeEntry::delete_all_by_entryId($v->entryId);

  // If there are any Visuals or VisualTags, print a big warning
  $visuals = Visual::get_all_by_entryId($v->entryId);
  $visualTags = VisualTag::get_all_by_entryId($v->entryId);
  if (count($visuals) || count($visualTags)) {
      print
        "**************** Warning: {$v->form} has visual / visual tags, " .
        "please migrate them manually.\n";
  }

  // Move the variant lexem to the principal's entry
  $oldEntryId = $v->entryId;
  $v->entryId = $p->entryId;
  $v->variantOfId = 0;
  $v->main = 0;
  $v->save();

  // If the entry has no lexems left, delete it
  $lexems = Lexem::get_all_by_entryId($oldEntryId);
  if (!count($lexems)) {
    $e = Entry::get_by_id($oldEntryId);
    $e->delete();
  }
}
