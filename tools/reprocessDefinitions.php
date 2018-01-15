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
    $errors = [];
    $newRep = Str::sanitize($d->internalRep, $d->sourceId, $errors);
    $newHtmlRep = Str::htmlize($newRep, $d->sourceId);
    if ($newRep !== $d->internalRep || $newHtmlRep !== $d->htmlRep) {
//      printf("**** %d %3d %s %s\n", $d->id, $d->sourceId, defUrl($d), $d->lexicon);
      /* for ($i = 0; $i < min(mb_strlen($d->htmlRep), mb_strlen($newHtmlRep)); $i++) { */
      /*   printf("%d %d %d [%s] [%s]\n", */
      /*   $i, */
      /*   Str::ord(Str::getCharAt($d->htmlRep, $i)), */
      /*   Str::ord(Str::getCharAt($newHtmlRep, $i)), */
      /*   Str::getCharAt($d->htmlRep, $i), */
      /*   Str::getCharAt($newHtmlRep, $i)); */
      /* } */
      $d->internalRep = $newRep;
      $d->htmlRep = $newHtmlRep;
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
