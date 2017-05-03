<?php

/**
 * Extract examples from meanings into separate submeanings.
 **/

require_once __DIR__ . '/../phplib/Core.php';

$trees = Model::factory('Tree')
       ->table_alias('t')
       ->select('t.*')
       ->distinct()
       ->join('Meaning', ['m.treeId', '=', 't.id'], 'm')
       ->where_not_equal('m.type', Meaning::TYPE_EXAMPLE)
       ->where_raw('m.internalRep rlike "\\\\. +\\\\$[^$@(][^$@]+\\\\$$"')
       ->order_by_asc('t.description')
       ->order_by_asc('m.breadcrumb')
       ->find_many();

foreach ($trees as $t) {
  $meanings = Model::factory('Meaning')
            ->where('treeId', $t->id)
            ->order_by_asc('displayOrder')
            ->find_many();

  $newMeanings = [];

  foreach ($meanings as $m) {
    if (preg_match("/^(.*\\.) +\\$([^$@(][^$@]+)\\$$/", $m->internalRep, $matches)) {
      printf("****** Processing [%s] [%s] %s\n", $t->description, $m->breadcrumb, $m->internalRep);

      $m->internalRep = $matches[1];
      $m->htmlRep = AdminStringUtil::htmlize($m->internalRep, 0);      
      $newMeanings[] = $m;

      $examples = preg_split('/\. /', $matches[2]);
      printf("  New internalRep: [{$m->internalRep}]\n");
      foreach ($examples as $e) {
        if (!StringUtil::endsWith($e, '.')) {
          $e .= '.';
        }

        $child = Model::factory('Meaning')->create();
        $child->parentId = $m->id;
        $child->type = Meaning::TYPE_EXAMPLE;
        $child->userId = $m->userId;
        $child->treeId = $m->treeId;
        $child->internalRep = $e;
        $child->htmlRep = AdminStringUtil::htmlize($child->internalRep, 0);
        $newMeanings[] = $child;

        printf("  Example: [$e]\n");
      }
    } else {
      $newMeanings[] = $m;
    }
  }

  Meaning::renumber($newMeanings);

  foreach ($newMeanings as $m) {
    $needsSources = !$m->id;
    printf("[T%s] [%s] %s\n", $m->type, $m->breadcrumb, $m->internalRep);
    $m->save();
    if ($needsSources) {
      $mss = MeaningSource::get_all_by_meaningId($m->parentId);
      foreach ($mss as $ms) {
        printf("* Associating source ID %s to meaning [%s]\n", $ms->sourceId, $m->internalRep);
        MeaningSource::associate($m->id, $ms->sourceId);
      }
    }
  }
}
