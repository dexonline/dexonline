<?php

// Renumber all trees

// select trees that have meanings
$trees = Model::factory('Tree')
       ->table_alias('t')
       ->select('t.*')
       ->join('Meaning', ['t.id', '=', 'm.treeId'], 'm')
       ->group_by('t.id')
       ->order_by_asc('t.description')
       ->find_many();

foreach ($trees as $t) {
  $meanings = Model::factory('Meaning')
            ->where('treeId', $t->id)
            ->order_by_asc('displayOrder')
            ->find_many();

  Meaning::renumber($meanings);

  foreach ($meanings as $m) {
    $m->save();
  }

  Log::info("renumbered tree {$t->description}");
}
