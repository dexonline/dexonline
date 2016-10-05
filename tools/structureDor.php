<?php

/**
 * Parse definitions from DOR, add LexemSources and mark them as structured
 * when possible.
 **/

require_once __DIR__ . '/../phplib/util.php';

$DOR_SOURCE_ID = 38;
$REGEX_WORD = '/^@([^@]+)@\s+/';
$REGEX_POS = '/^([a-z. ]+)( \(sil\. \$[-a-zăâîșț]+\$\))?([,;] |$)/';
$REGEX_INFL = '/^([-a-z. ]+) \$([-a-zăâîșțáéíóú]+)\$( \(sil\. \$[-a-zăâîșț]+\$\))?([,;] |$)/';

$defs = Model::factory('Definition')
      ->select('id')
      ->where('sourceId', $DOR_SOURCE_ID)
      ->where('structured', 0)
      //      ->where_in('id', [623577, 656145])
      ->where_in('status', [Definition::ST_ACTIVE, Definition::ST_HIDDEN])
      ->find_many();

foreach ($defs as $i => $defId) {
  try {
    $d = Definition::get_by_id($defId->id);

    // cleanup
    $s = str_replace('$ $', ' ', $d->internalRep);
    $s = str_replace('@ @', ' ', $s);
    $s = str_replace(',$ ', '$, ', $s);
    $s = str_replace(';$ ', '$; ', $s);

    // match the word being defined
    if (!preg_match($REGEX_WORD, $s, $m)) {
      throw new Exception('Cannot parse word');
    }
    $s = substr($s, strlen($m[0]));

    $baseForm = $m[1];

    // match the part(s) of speech
    $posList = [];
    while (preg_match($REGEX_POS, $s, $m)) {
      $s = substr($s, strlen($m[0]));
      $posList[] = [
        'pos' => $m[1],
        'extra' => $m[2],
      ];
    }

    if (empty($posList)) {
      throw new Exception('Cannot parse part of speech');
    }

    // match the inflections and inflected forms
    $inflList = [];
    while (preg_match($REGEX_INFL, $s, $m)) {
      $s = substr($s, strlen($m[0]));
      $inflList[]= [
        'inflection' => $m[1],
        'form' => $m[2],
        'extra' => $m[3],
      ];
    }

    // printf("Base form: [{$baseForm}]\n");
    // print_r($posList);
    // print_r($inflList);

  } catch (Exception $e) {
    Log::warning('%s: %s', $e->getMessage(), $d->internalRep);
    exit;
  }
  
  if ($i % 1000 == 0) {
    Log::info('Processed %d / %d definitions.', $i, count($defs));
  }
}
