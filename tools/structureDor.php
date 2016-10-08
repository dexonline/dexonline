<?php

/**
 * Parse definitions from DOR, add LexemSources and mark them as structured
 * when possible.
 *
 * TODO
 * - split inflected forms containing slashes
 * - ignore the "simb." inflection (chemical symbols)
 **/

require_once __DIR__ . '/../phplib/util.php';

$DOR_SOURCE_ID = 38;
$URL = 'https://dexonline.ro/admin/definitionEdit.php?definitionId='; // for edit links

// regex parts
$PART_PRON = '( \[.*(pr\.|cit\.).*\])';
$PART_HYPH = '( \(sil\. [^)]+\))';
$PART_COMMENT = '( \([^)]+\))';

$REGEX_WORD = "/^@([^@]+)@\s+/";
$REGEX_POS = "/^([-a-zăâîșț1-3.\/()+ ]+)({$PART_PRON}|{$PART_HYPH})*([,;] |$)/";
$REGEX_INFL = "/^([-a-zăâîșț1-3.() ]+) [$]([-a-zA-ZÅÁăâîșțĂÂÎȘȚáéíóúýắấäüµΩ'., \\/]+)[$]{$PART_HYPH}?{$PART_COMMENT}?([,;] |$)/";

$defs = Model::factory('Definition')
      ->select('id')
      ->where('sourceId', $DOR_SOURCE_ID)
      ->where('structured', 0)
      ->where_in('status', [Definition::ST_ACTIVE, Definition::ST_HIDDEN])
      ->order_by_asc('lexicon')
      ->find_many();
$errorCount = 0;
$inflMap = [];

foreach ($defs as $i => $defId) {
  try {
    $d = Definition::get_by_id($defId->id);

    // cleanup
    $s = $d->internalRep;
    $s = str_replace(');$', '$);', $s);
    $s = str_replace(',$ ', '$, ', $s);
    $s = str_replace(';$ ', '$; ', $s);
    $s = str_replace(')$ ', '$) ', $s);
    $s = preg_replace('/\)\$$/', '$)', $s);

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
        'pronunciation' => $m[2],
        'hyphenation' => $m[4],
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

    if ($s) {
      throw new Exception('Cannot parse inflection list');
    }

    foreach ($inflList as $rec) {
      if ((strpos($rec['form'], '.') !== false) &&
          ($rec['inflection'] != 'abr.')) {
        throw new Exception('Inflected form contains a dot');
      }
    }

    foreach ($posList as $pos) {
      foreach ($inflList as $infl) {
        // Log::info("[%s] [%s] [%s] = [%s]", $baseForm, $pos['pos'], $infl['inflection'], $infl['form']);
        $inflMap[$pos['pos']][] = $baseForm;
        $inflMap[$pos['pos']] = array_slice($inflMap[$pos['pos']], 0, 10);
      }
    }
    // printf("Base form: [{$baseForm}]\n");
    // print_r($posList);
    // print_r($inflList);

  } catch (Exception $e) {
    Log::warning('%s: %s [%s%d]', $e->getMessage(), $d->internalRep, $URL, $d->id);
    $errorCount++;
  }
  
  if ($i % 1000 == 0) {
    Log::info('Processed %d / %d definitions.', $i, count($defs));
  }
}

Log::warning('Processed %d definitions, skipped %d due to parsing errors.',
             count($defs), $errorCount);

foreach ($inflMap as $pos => $wordList) {
  print("{$pos}:");
  foreach ($wordList as $i => $form) {
    print(" [$form]");
  }
  print("\n");
}
