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
define('TAGS_TO_IGNORE', [
  404, // incomplete definition, usually missing everything after the [...]
  405, // missing etymology
]);


$offset = 0;

$PARTS_OF_SPEECH = [
  'a', 'ad', 'ada', 'af', 'afi', 'afp', 'afpt', 'afs', 'ai', 'ain', 'am', 'amp', 'an', 'anh', 'apr',
  'ard', 'arh', 'arp', 'art', 'arti', 'av', 'avi', 'avr', 'c', 'ec', 'i', 'la', 'lav',
  'lc', 'ls', 'nc', 'ncv', 'nf', 'no', 'pd', 'pdf', 'pdm', 'pin', 'pir', 'pî', 'pnh', 'pnhi',
  'pp', 'ppl', 'ppr', 'prl', 'prli', 'prn', 's', 'sa',
  'sf', 'sfa', 'sfi', 'sfm', 'sfn', 'sfp', 'sfpa', 'sfs', 'sfsa', 'si', 'sm', 'sma',
  'smf', 'smi', 'smn', 'smnf', 'smp', 'sms', 'smsa', 'sn', 'sna', 'snf', 'sni', 'snm', 'snp',
  'sns', 'ssg', 'ssga', 'ssp', 'v', 'va', 'vi', 'vi(a)', 'vif', 'vim', 'vir',
  'virp', 'virt', 'vit', 'vit(a)', 'vitr', 'viu', 'vp', 'vr', 'vr(a)', 'vra', 'vri',
  'vrim', 'vrp', 'vrr', 'vrt', 'vru', 'vt', 'vt(a)', 'vta', 'vt(f)', 'vtf', 'vtfr', 'vti',
  'vti(a)', 'vtir', 'vtr', 'vtr(a)', 'vtra', 'vtrf', 'vtri', 'vtrp', 'vtrr', 'vu',
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
    '/[$@]*/ "[" attestation (morphology formattedSlash ws?)* etymology "]" /[$@]*/',
  ],
  'attestation' => [
    '"#At:#" ws /.+?\/(?! ?[\d\w\/])/s ws?',
  ],
  'morphology' => [
    'abbreviation',
    'accent',
    'alsoWritten',
    'cases',
    'plural',
    'tenses',
    'pronunciation',
    'variants',
  ],
  'formattedSlash' => [
    '/[$@]*\/[$@]*/',
  ],
  'abbreviation' => [
    '"#Abr#:" /[^\/]+/s',
  ],
  'accent' => [
    '("A:"|"#A:#"|"#A și:#"|"A și (#înv#):") /[^\/]+/s',
    '"A: #nct#" ws',
  ],
  'alsoWritten' => [
    '("S:"|"#S:#"|"#S și:#") /[^\/]+/s',
  ],
  'cases' => [
    '("#Ac#:"|"#D:#"|"#G-D#:") /[^\/]+/s',
  ],
  'plural' => [
    '("#Pl:#"|"#Pl#:"|"#Pl# și:") /[^\/]+/s',
  ],
  'pronunciation' => [
    '("#P:#"|"#P și:#") ws pronunciationList+/( și )|( )/ ws'
  ],
  'pronunciationList' => [
    'morphologyParent? /[$@]+/ morphologyForm+", " ","? /[$@]+/',
    '"?"',
  ],
  'morphologyParent' => [
    '/\(.*?\)/ ws',
  ],
  'morphologyForm' => [
    '/[-~]*/ fragment+/[- ]/ /[-~]*/'
  ],
  'tenses' => [
    '("#Cj#:"|"#Cnd#:"|"#Grz#:"|"#Im#:"|"#Imt#:"|"#In#:"|"#Mp#:"|"#Par#:"|"#Ps:#"|"#Pzi:#") /[^\/]+/s',
  ],
  'variants' => [
    '"#V:#" ws variantsList+" " ws',
  ],
  'variantsList' => [
    'morphologyParent? /[$@]+/ (morphologyForm homonym?)+", " /[$@,]+/ variantDetails',
  ],
  'variantDetails' => [
    '(variantPosList|variantMorphInfo|variantMeaning)*',
  ],
  'variantPosList' => [
    'ws "$"? posHash+", " /[$,]*/'
  ],
  'variantMorphInfo' => [
    'ws "(" ("#Pl:#"|"#Pl#:"|"#pl#"|"#pl#:"|"#S și:#"|"#A și:#"|"#P:#"|"#Pzi:#"|"#Pzi:# 3"|"#pzi:#") " $" /[^$)]+/ "$"? ")" /[$,]*/',
    'ws "(#A:# #nct#)" ","?',
    'ws "(#A:# #ns#)" ","?',
    'ws "(#Pl:# #nct#)" ","?',
  ],
  'variantMeaning' => [
    'ws /\(@\d+@\)/',
  ],
  'etymology' => [
    '"#E:#" ws /([^\[\]]*\[[^\[\]]+\])*[^\[\]]*/',
  ],
  'reference' => [
    'entryWithInflectedForms ws formattedPosList ws formattedVz ws formattedForm',
    '(prefixForm|suffixForm) ws formattedVz ws formattedForm',
  ],
  'entryWithInflectedForms' => [
    '(/[$@]*/ form /[$@]*/ homonym? "-"? /[$@]*/)+/,[$@]* /',
  ],
  'prefixForm' => [
    '/[$@]*/ fragment /[$@]*/ homonym? "-" /[$@]*/',
  ],
  'suffixForm' => [
    '/[$@]*/ "-" /[$@]*/ fragment /[$@]*/ homonym? /[$@]*/',
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
    '/[$@]*/ form homonym? "-"? /[$@]*/',
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
    "/[A-ZĂÂÎȘȚ]?([~a-zăâçîöșțüáắấéíî́óúý()']|##)+/u", // accept capitalized forms
  ],
  'ws' => [
    '/(\s|\n)+/',
  ],
  'ignored' => [
    '/.*/s',
  ],
];

$subquery = sprintf(
  'select objectId from ObjectTag where objectType = %s and tagId in (%s)',
  ObjectTag::TYPE_DEFINITION,
  implode(',', TAGS_TO_IGNORE)
);
$parser = makeParser($GRAMMAR);

do {
  $defs = Model::factory('Definition')
        ->where('sourceId', SOURCE_ID)
        ->where('status', Definition::ST_ACTIVE)
        ->where_gte('lexicon', START_AT)
        ->where_raw("id not in ({$subquery})")
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
      $errorPos = $parser->getError()['index'];
      $markedRep = substr_replace($rep, red('***'), $errorPos, 0);
      printf("%s [%s]\n", defUrl($d), $markedRep);
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

function red($s) {
  return "\033[01;31m{$s}\033[0m";
}
