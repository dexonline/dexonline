<?php

/**
 * Structure definitions from DEX '98.
 **/

require_once __DIR__ . '/../phplib/Core.php';
require_once __DIR__ . '/../phplib/third-party/PHP-parsing-tool/Parser.php';

define('SOURCE_ID', 53);
define('BATCH_SIZE', 10000);
define('START_AT', 'bo');
define('DEBUG', false);
$offset = 0;

$PARTS_OF_SPEECH = [
  'a', 'ad', 'af', 'afi', 'afp', 'afpt', 'ai', 'ain', 'am', 'an', 'anh', 'apr',
  'ard', 'arh', 'arp', 'art', 'arti', 'av', 'avi', 'avr', 'c', 'ec', 'i', 'la', 'lav',
  'ls', 'nc', 'ncv', 'no', 'pd', 'pdf', 'pin', 'pî', 'pnh', 'pp', 'prl', 'prn', 's', 'sa',
  'sf', 'sfa', 'sfi', 'sfm', 'sfn', 'sfp', 'sfs', 'sfsa', 'si', 'sm', 'sma',
  'smf', 'smi', 'smn', 'smp', 'sms', 'sn', 'sna', 'snf', 'sni', 'snm', 'snp',
  'sns', 'ssg', 'ssga', 'ssp', 'v', 'va', 'vi', 'vi(a)', 'vif', 'vim', 'vir',
  'virp', 'virt', 'vit', 'vit(a)', 'vitr', 'viu', 'vp', 'vr', 'vr(a)', 'vra', 'vri',
  'vrim', 'vrp', 'vrr', 'vrt', 'vru', 'vt', 'vt(a)', 'vta', 'vtf', 'vtfr', 'vti',
  'vtir', 'vtr', 'vtr(a)', 'vtra', 'vtrf', 'vtri', 'vtrp', 'vtrr', 'vu',
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
    'entryWithInflectedForms (ws formattedPosList)? ws bracket ws ignored',
  ],
  'bracket' => [
    '/[$@]*/ "[" attestation /[^\\]]+/ "]" /[$@]*/',
  ],
  'attestation' => [
    /* '"#At:# " /.+?(?= \/ )/ " / "', */
    '"#At:# " /.+?\/(?!\d)/ ws?',
  ],
  'reference' => [
    'entryWithInflectedForms ws formattedPosList ws formattedVz ws formattedForm',
  ],
  'entryWithInflectedForms' => [
    '(/[$@]*/ form /[$@]*/ homonym? /[$@]*/)+/,[$@]* /',
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
    'fragment+/[- ]/',
    'fragment "-"', // prefixes
    '"-" fragment', // suffixes
  ],
  'fragment' => [
    "/[A-ZĂÂÎȘȚ]?([~a-zăâçîșțüáắấéíî́óúý()']|##)+/u", // accept capitalized forms
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
        ->order_by_asc('lexicon')
        ->order_by_asc('id')
        ->limit(BATCH_SIZE)
        ->offset($offset)
        ->find_many();

  foreach ($defs as $d) {
    // for now remove footnotes and invisible comments
    $rep = preg_replace("/\{\{.*\}\}/U", '', $d->internalRep);
    $rep = preg_replace("/▶.*◀/U", '', $rep);

    $parsed = $parser->parse($rep);
    if (!$parsed) {
      $expected = $parser->getError()['expected'][0];
      $errorPos = $parser->getError()['index'];
      $markedRep = substr_replace($rep, '***', $errorPos, 0);
      printf("%s [expected: %s] [%s]\n", defUrl($d), $expected, $markedRep);
    } else {
      if (DEBUG) {
        printf("Parsed %s %s [%s]\n", $d->lexicon, $d->id, mb_substr($d->internalRep, 0, 120));
        var_dump($parsed->findFirst('definition'));
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
