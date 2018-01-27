<?php

/**
 * Detect spaced text that's written as f o o b a r instead of %foobar%.
 **/

require_once __DIR__ . '/../phplib/Core.php';

// const START_ID = 0;
const START_ID = 821000;
const BATCH_SIZE = 10000;
const INCLUDE_SOURCES = [57];
const EXCLUDE_SOURCES = [32];

// Onomastic: @Actimie@ v. E f t i m i e @III 2.@
const PATTERN1 = '/^@[^@]+@ v\.(( \p{L})+)( @[^@]+@)?$/u';

//@Deodor@ v. D i o d o r.
const PATTERN2 = '/^@[^@]+@ v\.(( \p{L})+)\\.?$/u';

const PATTERN3 = '/\b([@#$(\[]*\p{L}[@#$)\]]*\s){4,}/u';

$offset = 0;
$found = 0;

do {
  $defs = Model::factory('Definition')
        ->where_in('status', [0, 3])
        ->where_gte('id', START_ID)
        //        ->where_in('sourceId', INCLUDE_SOURCES)
        ->where_not_in('sourceId', EXCLUDE_SOURCES)
        ->order_by_asc('id')
        ->limit(BATCH_SIZE)
        ->offset($offset)
        ->find_many();

  foreach ($defs as $d) {
    $matches = [];
    $s = $d->internalRep;
    $case = 0;

    if (preg_match(PATTERN1, $d->internalRep, $matches, PREG_OFFSET_CAPTURE)) {
      $at = $matches[1][1];
      $len = strlen($matches[1][0]);
      $text = sprintf(' %%%s%%', str_replace(' ', '', $matches[1][0]));
      $d->internalRep = substr($s, 0, $at) . $text . substr($s, $at + $len);

      $case = 1;

    } else if (preg_match(PATTERN2, $d->internalRep, $matches, PREG_OFFSET_CAPTURE)) {
      $at = $matches[1][1];
      $len = strlen($matches[1][0]);
      $text = sprintf(' %%%s%%', str_replace(' ', '', $matches[1][0]));
      $d->internalRep = substr($s, 0, $at) . $text . substr($s, $at + $len);

      $case = 2;

    } else if (preg_match_all(PATTERN3, $d->internalRep, $matches, PREG_OFFSET_CAPTURE)) {
      printf("%s %s\n", defUrl($d), $d->internalRep);
      $found++;
    }

    if ($case) {
      printf("%s case %d [%s] -> [%s]\n", defUrl($d), $case, $s, $d->internalRep);
      $d->htmlRep = Str::htmlize($d->internalRep, $d->sourceId);
      $d->save();
      $found++;
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
