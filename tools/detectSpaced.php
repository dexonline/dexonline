<?php

/**
 * Detect spaced text that's written as f o o b a r instead of %foobar%.
 **/

require_once __DIR__ . '/../phplib/Core.php';

const START_ID = 534000;
const BATCH_SIZE = 10000;
const EXCLUDE_SOURCES = [32];

const ONOM_PATTERN = '/^@[^@]+@ v\.(( \p{L})+)( @[^@]+@)?$/u';
const PATTERN = '/\b([@#$(\[]*\p{L}[@#$)\]]*\s){4,}/u';

$offset = 0;
$found = 0;

do {
  $defs = Model::factory('Definition')
        ->where_in('status', [0, 3])
        ->where_gte('id', START_ID)
        ->where_not_in('sourceId', EXCLUDE_SOURCES)
        ->order_by_asc('id')
        ->limit(BATCH_SIZE)
        ->offset($offset)
        ->find_many();

  foreach ($defs as $d) {
    $matches = [];
    if (preg_match(ONOM_PATTERN, $d->internalRep, $matches, PREG_OFFSET_CAPTURE)) {
      $at = $matches[1][1];
      $len = strlen($matches[1][0]);
      $text = sprintf(' %%%s%%', str_replace(' ', '', $matches[1][0]));
      $s = $d->internalRep;
      $d->internalRep = substr($s, 0, $at) . $text . substr($s, $at + $len);

      printf("%s %s\n", defUrl($d), $d->internalRep);
      printf("[%50s] -> [%s]\n", $s, $d->internalRep);

      $found++;
    } else if (preg_match_all(PATTERN, $d->internalRep, $matches, PREG_OFFSET_CAPTURE)) {
    }
  }

  $offset += count($defs);
  Log::info("$offset definitions reprocessed, $found matches.");
} while (count($defs));

Log::info("$offset definitions reprocessed, $found matches.");

/*************************************************************************/

function defUrl($d) {
  return "https://dexonline.ro/admin/definitionEdit.php?definitionId={$d->id}";
}
