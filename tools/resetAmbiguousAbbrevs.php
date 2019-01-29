<?php

require_once __DIR__ . '/../phplib/Core.php';

const BATCH_SIZE = 10000;
const START_ID = 0;
$offset = 0;
$modified = 0;

do {
  $defs = Model::factory('Definition')
    ->where_in('status', [0, 3])
    ->where_gte('id', START_ID)
    ->order_by_asc('id')
    ->limit(BATCH_SIZE)
    ->offset($offset)
    ->find_many();

  foreach ($defs as $d) {
    $oldAmbig = $d->hasAmbiguousAbbreviations;
    $d->process();
    if ($oldAmbig != $d->hasAmbiguousAbbreviations) {
      $d->save();
      $modified++;
    }
  }

  $offset += count($defs);
  Log::info("$offset definitions reprocessed, $modified modified.");
} while (count($defs));
