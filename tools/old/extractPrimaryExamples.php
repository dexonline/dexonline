<?php

/**
 * Extract examples from primary meanings into submeanings of the primary meaning above.
 **/

require_once __DIR__ . '/../phplib/Core.php';

$trees = Model::factory('Tree')
       ->table_alias('t')
       ->select('t.*')
       ->distinct()
       ->join('Meaning', ['m.treeId', '=', 't.id'], 'm')
       ->where('m.type', Meaning::TYPE_EXAMPLE)
       ->where('m.parentId', 0)
       ->order_by_asc('t.description')
       ->find_many();

foreach ($trees as $t) {
  Log::info("******** Processing tree {$t->description}");

  $meanings = Model::factory('Meaning')
            ->where('treeId', $t->id)
            ->order_by_asc('displayOrder')
            ->find_many();
  $newMeanings = [];

  $lastPrimary = null;

  foreach ($meanings as $m) {
    if (!$m->parentId) { // look at primary meanings
      if ($m->type == Meaning::TYPE_MEANING) {
        $lastPrimary = $m;
      } else if ($m->type == Meaning::TYPE_EXAMPLE) {
        if ($lastPrimary) {
          Log::info('Moving example under meaning %s for tree %s',
                    $lastPrimary->breadcrumb, $t->description);
          $m->parentId = $lastPrimary->id;
        } else {
          Log::warning("Tree {$t->description} has examples before first primary meaning");
        }
      }
    }
    $newMeanings[] = $m;
  }

  Meaning::renumber($newMeanings);
  foreach ($newMeanings as $m) {
    // printf("[ID:%s] [P:%s] [T:%s] [%s] %s\n", $m->id, $m->parentId, $m->type, $m->breadcrumb,
    //        $m->internalRep);
    $m->save();
  }
}
