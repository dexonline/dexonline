<?php

/**
 * Structure definitions from DEX '98.
 **/

require_once __DIR__ . '/../phplib/Core.php';
require_once __DIR__ . '/../phplib/third-party/PHP-parsing-tool/Parser.php';

define('SOURCE_ID', 53);
define('BATCH_SIZE', 10000);
define('START_AT', '');
define('DEBUG', false);
$offset = 0;

$PARTS_OF_SPEECH = [
  'a', 'ad', 'af', 'afp', 'ai', 'am', 'amp', 'an', 'anh', 'ain', 'art', 'av',
  'c', 'ec', 'i', 'la', 'lc', 'nc', 'no', 'pd', 'pdf', 'pdm', 'pin', 'pir',
  'pnh', 'pp', 'ppl', 'ppr', 'pr', 'pron', 's', 'sf', 'sfa', 'sfi', 'sfn',
  'sfp', 'sfs', 'si', 'sm', 'sma', 'smf', 'smi', 'smn', 'smnf', 'smp', 'sms',
  'sn', 'sna', 'snf', 'snm', 'snp', 'sns', 'v', 'vi', 'vir', 'vit', 'vr',
  'vri', 'vt', 'vti', 'vtr', 'vtri',
];
$PARTS_OF_SPEECH = array_map(function($s) {
  return '"' . $s . '"';
}, $PARTS_OF_SPEECH);

$GRAMMAR = [
  'start' => [
    'definition',
    'reference',
  ],
  'definition' => [
    'formattedEntryWithInflectedForms (ws formattedPosList)? ws ignored',
  ],
  'reference' => [
    'formattedEntryWithInflectedForms ws formattedPosList ws formattedVz ws formattedForm',
  ],
  'formattedEntryWithInflectedForms' => [
    '/[$@]*/ entryWithInflectedForms /[$@]*/',
  ],
  'entryWithInflectedForms' => [
    '(form homonym?)+", "',
  ],
  'homonym' => [
    '/\^\d-?/',
    '/\^\{\d\}-?/', // if there is a dash, the number comes before it, e.g. aer^3-
  ],
  'formattedPosList' => [
    'formattedPos+", "',
  ],
  'formattedPos' => [
    '/[$@]*/ posHash /[$@]*/',
  ],
  'posHash' => [
    '"#" pos "#"',
    'pos',
  ],
  'pos' => $PARTS_OF_SPEECH,
  'formattedForm' => [
    '/[$@]*/ form homonym? /[$@]*/',
  ],
  'formattedVz' => [
    '/[$@]*/ "#vz#" /[$@]*/',
  ],
  'form' => [
    '"##" form "##"',
    "/[A-ZĂÂÎȘȚ]?[-~a-zăâîșțáắấéíî́óú()']+/u", // accept capitalized forms
  ],
  'ws' => [
    '/(\s|\n)+/',
  ],
  'ignored' => [
    '/.*/s',
  ],
];

$parser = makeParser($GRAMMAR);

do {
  $defs = Model::factory('Definition')
        ->where('sourceId', SOURCE_ID)
        ->where('status', Definition::ST_ACTIVE)
        ->where_gte('lexicon', START_AT)
        ->where_not_like('internalRep', '%▶%')
        ->where_not_like('internalRep', '%{{%')
        ->order_by_asc('lexicon')
        ->order_by_asc('id')
        ->limit(BATCH_SIZE)
        ->offset($offset)
        ->find_many();

  foreach ($defs as $d) {
    $parsed = $parser->parse($d->internalRep);
    if (!$parsed) {
      $errorPos = $parser->getError()['index'];
      $rep = substr_replace($d->internalRep, '***', $errorPos, 0);
      printf("Cannot parse %s [%s]\n", defUrl($d), $rep);
    } else {
      if (DEBUG) {
        printf("Parsed %s %s [%s]\n", $d->lexicon, $d->id, mb_substr($d->internalRep, 0, 120));
        var_dump($parsed->findFirst('formattedForm'));
      }
    }
    if (DEBUG) {
      exit;
    }
  }

  $offset += count($defs);
  Log::info("Processed $offset definitions.");
} while (count($defs));

Log::info('ended');

/*************************************************************************/

function makeParser($grammar) {
  $s = '';
  foreach ($grammar as $name => $productions) {
    $s .= "{$name} ";
    foreach ($productions as $p) {
      $s .= " :=> {$p}";
    }
    $s .= ".\n";
  }

  return new \ParserGenerator\Parser($s);
}

function defUrl($d) {
  return "https://dexonline.ro/admin/definitionEdit.php?definitionId={$d->id}";
}
