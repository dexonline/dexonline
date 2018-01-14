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
//    ->where('sourceId', 53)
    ->where_not_in('sourceId', EXCLUDE_SOURCES)
    ->order_by_asc('id')
    ->limit(BATCH_SIZE)
    ->offset($offset)
    ->find_many();

  foreach ($defs as $d) {
    $errors = [];
    $newRep = Str::sanitize($d->internalRep, $d->sourceId, $errors);
    //    $newHtmlRep = Str::htmlize($newRep, $d->sourceId);
    //    if ($newRep !== $d->internalRep || $newHtmlRep !== $d->htmlRep) {
    // if ($newRep !== $d->internalRep) {
    //   printf("%d\n[%s]\n[%s]\n\n", $d->id, $d->internalRep, $newRep);
    //   $modified++;
    //   // $d->internalRep = $newRep;
    //   // $d->htmlRep = $newHtmlRep;
    //   // $d->save();
    // }
    if (count($errors)) {
      printf("**** %d %s %s\n", $d->id, defUrl($d), $d->lexicon);
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
