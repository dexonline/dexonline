<?php

/**
 * Restores the original ortography in DLRM definitions (î, not â).
 **/

require_once __DIR__ . '/../lib/Core.php';

const DLRM_SOURCE_ID = 15;

// Adapted from Str::replace_ai.
function replace_ai($s) {
  $char_map = [
    'â' => 'î',
    'Â' => 'Î',
    'ấ' => 'î́',
    'Ấ' => 'Î́',
  ];

  foreach ($char_map as $a => $i) {
    $s = preg_replace("/\b{$a}\b/", $i, $s);
    $s = preg_replace("/(r[ou]m)$i(n)/i", "\${1}$a\${2}", $s);
  }

  // sunt(em,eți) -> sînt(em,eți)
  $s = preg_replace("/\bsunt(em|eți)?/i", "sînt\${1}", $s);

  return $s;
}

function main() {
  $defs = Model::factory('Definition')
    ->where('sourceId', DLRM_SOURCE_ID)
    ->where('status', Definition::ST_ACTIVE)
    ->order_by_asc('lexicon')
    ->find_many();

  foreach ($defs as $d) {
    $orig = $d->internalRep;
    $d->internalRep = replace_ai($d->internalRep);
    if ($d->internalRep != $orig) {
      printf("%d [%s] %s\n", $d->id, $d->lexicon, substr($d->internalRep, 0, 50));
      $d->save();
    }
  }
}

main();
