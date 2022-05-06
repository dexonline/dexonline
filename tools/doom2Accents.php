<?php

/**
 * Restores the original accents in DOOM2 (underlined, not built-in).
 * Does not handle special cases such as "consommé".
 **/

require_once __DIR__ . '/../lib/Core.php';

const DOOM2_SOURCE_ID = 19;

function main() {
  $defs = Model::factory('Definition')
    ->where('sourceId', DOOM2_SOURCE_ID)
    ->where('status', Definition::ST_ACTIVE)
    ->order_by_asc('lexicon')
    ->find_many();

  foreach ($defs as $d) {
    $orig = $d->internalRep;
    $d->internalRep = Str::changeAccents($d->internalRep);
    if ($d->internalRep != $orig) {
      printf("%d [%s] %s\n", $d->id, $d->lexicon, $d->internalRep);
      $d->save();
    }
  }
}

main();
