<?php

require_once __DIR__ . '/../phplib/Core.php';

define('BATCH_SIZE', 10000);
define('START_ID', 0);
define('EXCLUDE_SOURCES', [17, 42, 53]);
$offset = 0;
$modified = 0;

do {
  $defs = Model::factory('Definition')
    ->where_in('status', [0, 3])
    ->where_gte('id', START_ID)
//    ->where_not_in('sourceId', EXCLUDE_SOURCES)
    ->order_by_asc('id')
    ->limit(BATCH_SIZE)
    ->offset($offset)
    ->find_many();

  foreach ($defs as $d) {
    $oldRep = $d->internalRep;
    $oldHtml = HtmlConverter::convert($d);
    $d->process();
    $html = HtmlConverter::convert($d);
    if ($oldRep !== $d->internalRep || $oldHtml !== $html) {
      // printf("**** %d %3d %s %s\n", $d->id, $d->sourceId, defUrl($d), $d->lexicon);
      $d->save();
      $modified++;
    }
  }

  $offset += count($defs);
  Log::info("$offset definitions reprocessed, $modified modified.");
} while (count($defs));

Log::info("$offset definitions reprocessed, $modified modified.");

/*************************************************************************/

function defUrl($d) {
  return "https://dexonline.ro/admin/definitionEdit.php?definitionId={$d->id}";
}
