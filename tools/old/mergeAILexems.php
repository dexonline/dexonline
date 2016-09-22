<?php

// Merges lexems/entries using the pre-1993 orthography (î) into post-1993 lexems (â).
// Merges even proper nouns. If a proper noun exists in both forms, it is highly unlikely that
// both forms are correct. Thus, it is easier to correct a single form in the future, if needed.

require_once __DIR__ . '/../../phplib/util.php';

Log::notice('started');

$lexems = Model::factory('Lexem')
        ->where_raw('binary formNoAccent like "%â%"')
        ->order_by_asc('formNoAccent')
        ->find_many();

$i = 0;
foreach ($lexems as $l) {
  $iform = preg_replace("/(?<=[A-Za-zĂȘȚŞŢășşțţ])â(?=[A-Za-zĂȘȚŞŢășşțţ])/",
                        "$1î$2", $l->formNoAccent);
  $iform = preg_replace("/(r[ou]m)î(n)/i", "\${1}â\${2}", $iform);

  if ($iform != $l->formNoAccent) {
    $candidates = Model::factory('Lexem')
                ->where_raw("formNoAccent = binary '{$iform}'")
                ->find_many();
    if (count($candidates) > 1) {
      foreach ($candidates as $c) {
        print "merging {$c} {$c->id} {$c->entryId} into {$l} {$l->id}\n";
        // TODO
        // * delete empty trees for $c's entry
        // * merge entries
        // * delete $c
      }
    }
  }

  if (++$i % 100 == 0) {
    Log::info('processed %d/%d lexems', $i, count($lexems));
  }
}

Log::notice('finished');
