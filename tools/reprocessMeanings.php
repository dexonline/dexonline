<?php

/**
 * Reprocess meanigns the meet certain criteria.
 **/

require_once __DIR__ . '/../phplib/Core.php';

define('BATCH_SIZE', 1000);
define('START_ID', 0);
$offset = 0;
$modified = 0;

$query = Model::factory('Meaning')
       ->where_like('internalRep', '%[%')
       ->where_gte('id', START_ID);

Log::info("Processing %s meanings.", $query->count());

do {
  $meanings = $query
            ->order_by_asc('id')
            ->limit(BATCH_SIZE)
            ->offset($offset)
            ->find_many();

  foreach ($meanings as $m) {
    $m->process();
    $m->save();
    // Log::info("Modified: [%s] rank %s", treeUrl($m->treeId), $m->displayOrder);
    $modified++;
  }

  $offset += count($meanings);
  Log::info("$offset meanings reprocessed, $modified modified.");
} while (count($meanings) == BATCH_SIZE);

Log::info("$offset meanings reprocessed, $modified modified.");

/*************************************************************************/

function treeUrl($treeId) {
  return "https://dexonline.ro/editTree.php?id={$treeId}";
}
