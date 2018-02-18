<?php

/**
 * Find meanings A that contain mentions to other meanings B. Regenerate the
 * HTML for A, because it contains B's breadcrumb. If B is moved in its tree,
 * then the breadcrumb becomes incorrect.
 **/

require_once __DIR__ . '/../phplib/Core.php';

define('BATCH_SIZE', 1000);
define('START_ID', 0);
$offset = 0;
$modified = 0;

$query = Model::factory('Meaning')
       ->where_like('internalRep', '%[%') // catch-all regexp, will refine later
       ->where_gte('id', START_ID);

Log::info("Examining %s meanings.", $query->count());

do {
  $meanings = $query
            ->order_by_asc('id')
            ->limit(BATCH_SIZE)
            ->offset($offset)
            ->find_many();

  foreach ($meanings as $m) {
    if (preg_match('/[-a-zăâîșț]\[[0-9]/', $m->internalRep)) {
      $oldRep = $m->htmlRep;
      $m->process(false);

      if ($m->htmlRep != $oldRep) {
        $m->save();
        Log::info("Modified: [%s] rank %s", treeUrl($m->treeId), $m->displayOrder);
        $modified++;
      }
    }
  }

  $offset += count($meanings);
  Log::info("$offset meanings reprocessed, $modified modified.");
} while (count($meanings) == BATCH_SIZE);

/*************************************************************************/

function treeUrl($treeId) {
  return "https://dexonline.ro/editTree.php?id={$treeId}";
}
