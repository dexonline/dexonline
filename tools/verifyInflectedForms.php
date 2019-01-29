<?php

/**
 * Regenerates all inflected forms and verifies they match the ones in the InflectedForm table.
 **/

require_once __DIR__ . '/../phplib/Core.php';

ini_set('memory_limit','8G');

const START_ID = 0;

// pass -f to fix some of the issues encountered
$opts = getopt('f');
$fix = isset($opts['f']);

$lexemes = Model::factory('Lexeme')
         ->where_gte('id', START_ID)
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
  checkSameIfs($l, $oldIfs, $fix);
  if (++$processed % 1000 == 0) {
    Log::info('%s lexemes processed.', $processed);
  }
}

/*************************************************************************/

function checkSameIfs($lexeme, $oldIfs, $fix) {
  try {
    $newIfs = $lexeme->generateInflectedForms();
  } catch (ParadigmException $e) {
    Log::error('In lexeme %s %s', $lexeme, editUrl($lexeme));
    Log::error('cannot generate paradigm');
    return;
  }

  if (count($oldIfs) != count($newIfs)) {
    Log::error('In lexeme %s %s', $lexeme, editUrl($lexeme));
    Log::error('%s old forms, %s new forms', count($oldIfs), count($newIfs));
    return;
  }

  $anyChanges = false;
  foreach ($oldIfs as $i => $oif) {
    $nif = $newIfs[$i];
    if (($oif->form != $nif->form) ||
        ($oif->inflectionId != $nif->inflectionId) ||
        ($oif->variant != $nif->variant)) {
      Log::error('In lexeme %s %s', $lexeme, editUrl($lexeme));
      Log::error('difference at position %s [%s][%d,%d] : [%s][%d,%d]',
                 $i,
                 $oif->form,
                 $oif->inflectionId,
                 $oif->variant,
                 $nif->form,
                 $nif->inflectionId,
                 $nif->variant);
      $anyChanges = true;
    }
  }

  if ($anyChanges && $fix) {
    $lexeme->deepSave();
  }
}

function editUrl($lexeme) {
  return "https://dexonline.ro/admin/lexemeEdit.php?lexemeId={$lexeme->id}";
}
