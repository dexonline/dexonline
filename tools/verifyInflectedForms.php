<?php

/**
 * Regenerates all inflected forms and verifies they match the ones in the InflectedForm table.
 **/

require_once __DIR__ . '/../phplib/Core.php';

ini_set('memory_limit','8G');

$lexemes = Model::factory('Lexeme')
         ->where_gte('id', 120000)
         ->order_by_asc('id')
         ->find_many();

Log::info('Processing %s lexemes.', count($lexemes));

$processed = 0;

foreach ($lexemes as $l) {
  $oldIfs = Model::factory('InflectedForm')
          ->where('lexemeId', $l->id)
          ->order_by_asc('inflectionId')
          ->order_by_asc('variant')
          ->find_many();
  $newIfs = $l->generateInflectedForms();
  checkSameIfs($l, $oldIfs, $newIfs);
  if (++$processed % 1000 == 0) {
    Log::info('%s lexemes processed.', $processed);
  }
}

/*************************************************************************/

function checkSameIfs($lexeme, $oldIfs, $newIfs) {
  if (count($oldIfs) != count($newIfs)) {
    Log::error('%s old forms, %s new forms for %s', count($oldIfs), count($newIfs), $lexeme);
    return;
  }
  foreach ($oldIfs as $i => $oif) {
    $nif = $newIfs[$i];
    if (($oif->form != $nif->form) ||
        ($oif->inflectionId != $nif->inflectionId) ||
        ($oif->variant != $nif->variant)) {
      Log::error('difference at position %s lexeme %s [%s][%d,%d] : [%s][%d,%d]',
                $i,
                $lexeme,
                $oif->form,
                $oif->inflectionId,
                $oif->variant,
                $nif->form,
                $nif->inflectionId,
                $nif->variant);
    }
  }
}
