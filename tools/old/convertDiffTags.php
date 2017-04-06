<?php

/**
 * Convert meanings bearing [difference] tags to meanings of "difference" type.
 **/

require_once __DIR__ . '/../phplib/util.php';

define('DIFF_TAG_ID', 250);

Log::info('started');

// grab all the trees
$trees = Model::factory('Tree')
       ->table_alias('t')
       ->select('t.*')
       ->distinct()
       ->join('Meaning', ['t.id', '=', 'm.treeId'], 'm')
       ->join('ObjectTag', ['m.id', '=', 'ot.objectId'], 'ot')
       ->where('ot.objectType', ObjectTag::TYPE_MEANING)
       ->where('ot.tagId', DIFF_TAG_ID)
       ->order_by_asc('t.id')
       ->find_many();

// grab all the meaning IDs having the [difference] tag
$meanings = Model::factory('Meaning')
          ->table_alias('m')
          ->select('m.id')
          ->join('ObjectTag', ['m.id', '=', 'ot.objectId'], 'ot')
          ->where('ot.objectType', ObjectTag::TYPE_MEANING)
          ->where('ot.tagId', DIFF_TAG_ID)
          ->find_many();
$meaningIds = [];
foreach ($meanings as $m) {
  $meaningIds[$m->id] = true;
}

foreach ($trees as $t) {
  $meanings = Model::factory('Meaning')
            ->where('treeId', $t->id)
            ->order_by_asc('displayOrder')
            ->find_many();
  foreach ($meanings as $m) {
    if (isset($meaningIds[$m->id])) {
      $m->type = Meaning::TYPE_DIFF;
    }
  }

  Meaning::renumber($meanings);

  foreach ($meanings as $m) {
    $m->save();
    // printf("[tree:%s] [mId:%s] [do:%s] [type:%s] [bc:%s]\n",
    //        $m->treeId, $m->id, $m->displayOrder, $m->type, $m->breadcrumb);
  }
}

$tag = Tag::get_by_id(DIFF_TAG_ID);
$tag->delete();

Log::info('ended');
