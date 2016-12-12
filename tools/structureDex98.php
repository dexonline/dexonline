<?php

/**
 * Structure definitions from DEX '98.
 **/

require_once __DIR__ . '/../phplib/util.php';
require_once __DIR__ . '/../phplib/third-party/PHP-parsing-tool/Parser.php';
  
define('SOURCE_ID', 1);
define('MY_USER_ID', 1);
define('BATCH_SIZE', 1);
define('START_AT', 'spic');

$GRAMMAR = [
  'start' => [
    'entryWithInflectedForms " " meaning squareBracket? (" - " etymology)?',
    'prefixEntry meaning squareBracket? (" - " etymology)?',
    'entryWithInflectedForms " " reference',   // just a reference to the main form
  ],
  'entryWithInflectedForms' => [
    '"@" form homonym? "-"? boldForms ","? "@" formsAndPoss',
  ],
  'prefixEntry' => [
    '"@" form homonym? "-"? "@ " ?"Element de compunere"',
  ],
  'boldForms' => [
    '", " form boldForms',
    '""',
  ],
  'formsAndPoss' => [
    '(italicForms " " poss)+","',
  ],
  'italicForms' => [
    '(usage* " $" forms italicEnding)*',
    'usage+',
  ],
  'italicEnding' => [
    '",$"',
    '"$ și"',
  ],
  'usage' => [
    '/ \(@[-0-9A-EIV, ]+@\)/',
    '" #pers.# 3 #sg.#"',
    '/ #pers.# [1-6]/',
    '" (#pop.#)"',
    '" (rar)"',
    '" (rar, @2@)"',
    '" (@2,@ rar)"',
    '" și"',
  ],
  'forms' => [
    'form+", "',
  ],
  'form' => [
    '/[-a-zA-ZáäăắâấçÇÁÄĂẮÂẤéÉíîî́ÍÎÎ́óöÓÖșȘțȚúÚýÝ\\\\\'(). ]+/',
  ],
  'homonym' => [
    '/\^\d/',
  ],
  'poss' => [
    'pos+", "',
  ],
  'pos' => [
    '"#adj.# #interog.# #f.#"',
    '"#adj.# #interog.#"',
    '"#adj.# #invar.#"',
    '"#adj.# #m.#"',
    '"#adj.# #n.#"',
    '"#adj.# #f.#"',
    '"#adj. dem.#"',
    '"#adj. nehot.#"',
    '"#adj. pos.#"',
    '"#adj.#"',
    '"#adv.#"',
    '"#adv. interog.#"',
    '"#art.#"',
    '"#art. nehot.#"',
    '"#conj.#"',
    '"#interj.#"',
    '"#loc. adj.#"',
    '"#loc. adv.#"',
    '"#num. card.#"',
    '"#num. col.#"',
    '"#num. ord.#"',
    '"#prep.#"',
    '"#pron. dem.#"',
    '"#pron. interog.#"',
    '"#pron.# #interog.#"',
    '"#pron. interog.-rel.#"',
    '"#pron.# #invar.#"',
    '"#pron. neg.#"',
    '"#pron. nehot.#"',
    '"#pron. pers.# #f.#"',
    '"#pron. pers.#"',
    '"#pron. pos.#"',
    '"#pron. refl.#"',
    '"#pron.#"',
    '"#s. f.# #invar.#"',
    '"#s. f.# #pl.#"',
    '"#s. f.#"',
    '"#s. m.# #invar.#"',
    '"#s. m.# #pl.#"',
    '"#s. m.# și #f.#"',
    '"#s. m.# și #n.#"',
    '"#s. m.#"',
    '"#s. n.# #pl.#"',
    '"#s. n.#"',
    '"#subst.#"',
    '"#v.#"',
    'verbPos',
  ],
  'verbPos' => [
    '"#vb.# " verbGroup "." verbVoice??',
    '"#vb.# " verbGroup "."?',
    '"#vb.# #intranz.#"',
  ],
  'verbGroup' => [
    '("I" | "II" | "III" | "IV")',
  ],
  'verbVoice' => [
    '" #" ("Tranz." | "Refl." | "Intranz.") "#"',
  ],

  'reference' => [
    '/#[vV]\.# @/ form homonym? "-"? "."? "@"',
    '"@" form homonym? "-"? "."? "@"',
  ],

  'meaning' => [
    '/.*?(?=( - | \\[|$))/',
  ],

  'squareBracket' => [
    '" [" /[^]]*/ "]"'
  ],

  'etymology' => [
    '/.*/',
  ],

];

$MEANING_GRAMMAR = [
  'start' => [
    'arabMeaning+" "',
    'unnumbered',
  ],
  'arabMeaning' => [
    '/@[1-9]\.@ / unnumbered',
  ],
  'unnumbered' => [
    '/.*?(?=( @[1-9]\.@ |$))/',
  ],

  'filler' => [
    '/.*/',
  ],
];

Log::info('started');

$parser = makeParser($GRAMMAR);
$meaningParser = makeParser($MEANING_GRAMMAR);

$offset = 0;

do {
  $defs = Model::factory('Definition')
        ->where('sourceId', SOURCE_ID)
        ->where('status', Definition::ST_ACTIVE)
        ->where_gte('lexicon', START_AT)
        ->order_by_asc('lexicon')
        ->limit(BATCH_SIZE)
        ->offset($offset)
        ->find_many();

  foreach ($defs as $d) {
    $parsed = $parser->parse($d->internalRep);
    if (!$parsed) {
      // Log::error('Cannot parse: %s', $d->internalRep);
    } else {

      $meaning = $parsed->findFirst('meaning');
      if (!$meaning) {
        Log::error('No meaning: %s', $d->internalRep);
      }

      $parsed = $meaningParser->parse($meaning);
      if ($parsed) {
        var_dump($parsed->dump());
      } else {
        Log::error('Cannot parse meaning: %s', $meaning);
      }
    }
  }

  $offset += BATCH_SIZE;
  exit;
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
