<?php

/**
 * Structure definitions from DEX '98.
 **/

require_once __DIR__ . '/../phplib/util.php';
require_once __DIR__ . '/../phplib/third-party/PHP-parsing-tool/Parser.php';
  
define('SOURCE_ID', 1);
define('MY_USER_ID', 1);
define('BATCH_SIZE', 1000);
define('START_AT', '');

$GRAMMAR = [
  'start' => [
    'entryWithInflectedForms " " filler',
  ],
  'entryWithInflectedForms' => [
    '"@" form homonym? "-"? boldForms ","? "@" formsAndPoss',
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
    '/ \(@[-0-9IV, ]+@\)/',
    '" #pers.# 3 #sg.#"',
    '" #pers.# 3"',
    '" (rar)"',
    '" (rar, @2@)"',
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
    '"#pron.#"',
    '"#s. f.#"',
    '"#s. m.# și #f.#"',
    '"#s. m.# #pl.#"',
    '"#s. m.#"',
    '"#s. n.#"',
    '"#subst.#"',
    '"Element de compunere"', // TODO remove
    '"#v.#"', // TODO remove
    'verbPos',
  ],
  'verbPos' => [
    '"#vb.# " verbGroup "." verbVoice??',
    '"#vb.# " verbGroup',
  ],
  'verbGroup' => [
    '("I" | "II" | "III" | "IV")',
  ],
  'verbVoice' => [
    '" #" ("Tranz." | "Refl." | "Intranz.") "#"',
  ],
  'filler' => [
    '/.*/',
  ],
];

Log::info('started');

$grammar = '';
foreach ($GRAMMAR as $name => $productions) {
  $grammar .= "{$name} ";
  foreach ($productions as $p) {
    $grammar .= " :=> {$p}";
  }
  $grammar .= ".\n";
}

$parser = new \ParserGenerator\Parser($grammar);

$offset = 0;

do {
  $defs = Model::factory('Definition')
        ->where('sourceId', SOURCE_ID)
        ->where('status', Definition::ST_ACTIVE)
        ->where_gte('lexicon', START_AT)
        ->order_by_asc('lexicon')
        ->order_by_asc('internalRep')
        ->limit(BATCH_SIZE)
        ->offset($offset)
        ->find_many();

  foreach ($defs as $d) {
    $parsed = $parser->parse($d->internalRep);
    if ($parsed) {
      // print "{$d->internalRep}\n";
      // foreach($parsed->findAll('entryWithInflectedForms') as $s) {
      //   echo "  entry: {$s}\n";
      // }
      // foreach($parsed->findAll('pos') as $s) {
      //   echo "  part of speech: {$s}\n";
      // }
    } else {
      Log::error("Cannot parse: {$d->internalRep}");
      print "{$d->internalRep}\n";
    }
  }

  $offset += BATCH_SIZE;
  // exit;
  Log::info("Processed $offset definitions.");
} while (count($defs));

Log::info('ended');


/*************************************************************************/

