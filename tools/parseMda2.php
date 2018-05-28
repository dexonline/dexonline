<?php

/**
 * Structure definitions from DEX '98.
 **/

require_once __DIR__ . '/../phplib/Core.php';
require_once __DIR__ . '/../phplib/third-party/PHP-parsing-tool/Parser.php';

define('SOURCE_ID', 53);
define('BATCH_SIZE', 10000);
define('START_AT', 'babaluc');
define('DEBUG', false);
$offset = 0;

$PARTS_OF_SPEECH = [
  'a',
  'af',
  'ai',
  'av',
  'c',
  'i',
  'la',
  'no',
  'pd',
  'pp',
  'pr',
  'sf',
  'sfn',
  'sfp',
  'sm',
  'smf',
  'smi',
  'smn',
  'smp',
  'sms',
  'sn',
  'snm',
  'snp',
  'sns',
  'v',
  'vi',
  'vt',
  'vtr',
];
$PARTS_OF_SPEECH = array_map(function($s) {
  return '"' . $s . '"';
}, $PARTS_OF_SPEECH);

$GRAMMAR = [
  'start' => [
    'reference',
  ],
  'reference' => [
    //    'formattedEntryWithInflectedForms " " formattedPosList " " formattedVz " " ignored',
    'formattedEntryWithInflectedForms " " formattedPosList " " formattedVz " " formattedForm',
  ],
  'formattedEntryWithInflectedForms' => [
    '/[$@]*/ entryWithInflectedForms /[$@]*/',
  ],
  'entryWithInflectedForms' => [
    '(form homonym?)+", "',
  ],
  'homonym' => [
    '/\^\d/',
    '/\^\{\d\}/',
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
    "/[-~a-zăâîșțáắấéíóú']+/u",
  ],
  'ignored' => [
    '/.*/',
  ],
];

$parser = makeParser($GRAMMAR);

do {
  $defs = Model::factory('Definition')
        ->where('sourceId', SOURCE_ID)
        ->where('status', Definition::ST_ACTIVE)
        ->where_gte('lexicon', START_AT)
        ->order_by_asc('lexicon')
        ->order_by_asc('id')
        ->limit(BATCH_SIZE)
        ->offset($offset)
        ->find_many();

  foreach ($defs as $d) {
    $parsed = $parser->parse($d->internalRep);
    if (!$parsed) {
      printf("Cannot parse %s %s [%s]\n", $d->lexicon, $d->id, mb_substr($d->internalRep, 0, 120));
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
