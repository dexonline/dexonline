<?php

/**
 * Structure definitions from DEX '98.
 **/

require_once __DIR__ . '/../phplib/util.php';
require_once __DIR__ . '/../phplib/third-party/PHP-parsing-tool/Parser.php';
ini_set('memory_limit', '1024M');

define('SOURCE_ID', 1);
define('MY_USER_ID', 1);
define('BATCH_SIZE', 10000);
define('START_AT', 'adâncime');

$GRAMMAR = [
  'start' => [
    'entryWithInflectedForms " " reference',   // just a reference to the main form
    'entryWithInflectedForms " " meaning squareBracket? (" - " etymology)?',
    'prefixEntry meaning squareBracket? (" - " etymology)?',
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
    '/ \(@[-0-9A-EIVX, ]+@\)/',
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
    'caps+" "',
    'romans',
  ],
  'caps' => [
    '/@[A-E]\.@ / romans',
  ],
  'romans' => [
    '(basic " ")? roman+" "',
    'arabs',
  ],
  'roman' => [
    '/@[IVX]+\.@/ " " arabs',
  ],
  'arabs' => [
    '(basic " ")? arab+" "',
    'doubleAsterisk',
  ],
  'arab' => [   // @1.@, @2.@, ...
    '/@\d+\.@ / doubleAsterisk',
  ],
  'doubleAsterisk' => [ // includes ** and *
    'singleAsterisk+" ** "',
  ],
  'singleAsterisk' => [ // includes *
    'basic+" * "',
  ],
  'basic' => [
    '/.*?(?=( @[A-E]+\.@ | @[IVX]+\.@ | @\d\.@ | \*\* | \* |$))/',
  ],

  'filler' => [
    '/.*/',
  ],
];

$ETYMOLOGY_GRAMMAR = [
  'start' => [
    'reference',
    'fromLang',
    'import',
    'withSuffix',
    'glue',
    'confer',
    'regression',
    'latin',
    'specials',
  ],
  'reference' => [
    '"#V.# " formDot',
    '"Din " formDot',
    '"De la " formDot',
  ],
  'fromLang' => [
    '"Din " (lang " " formComma " ")* lang " " formDot',
  ],
  'import' => [
    '"#Cuv.# " lang',
    '"#Loc.# " lang',
    '"#Expr.# " lang',
  ],
  'withSuffix' => [
    'formNone " + #suf.# " suffixDot',
  ],
  'glue' => [
    '(formNone " + ")+ formDot',
  ],
  'confer' => [
    '"#Cf.# " conferSourceDot',
    '"#Cf.# " lang " " conferSourceDot',
  ],
  'regression' => [
    '"Din " formNone " (derivat regresiv)."',
  ],
  'latin' => [
    '"#Lat.# " formDot',
    '"#Lat.# " formNone translation "."',
  ],
  'specials' => [
    '"Denumire comercială."',
    '"#Et. nec.#"',
    '"Formație onomatopeică."',
    '"Onomatopee."',
    '"Probabil formație onomatopeică."',
  ],
  'lang' => [
    '/#[^#]+#/',
  ],
  'formComma' => [
    '/@[^@,]+,@/',
  ],
  'formDot' => [
    '/@[^@.]+\.@/',
  ],
  'formNone' => [
    '/@[^@]+@/',
  ],
  'suffixDot' => [
    '/\$-[^$.]+\.\$/',
  ],
  'conferSourceDot' => [
    '/%[^%.]+\.%/',
  ],
  'translation' => [
    '/"[^"]+"/',
  ],
];

Log::info('started');

// build  a tag map, mapped by value
$tagMap = [];
$tags = Model::factory('Tag')->find_many();
foreach ($tags as $t) {
  $tagMap[$t->value] = $t;
}

// augment the tag map with some hard-coded translations
$tagMap['adjectival'] = $tagMap['(și) adjectival'];
$tagMap['argotic'] = $tagMap['argou; argotic'];
$tagMap['articol; articulat'] = $tagMap['articulat'];
$tagMap['colectiv'] = $tagMap['(cu sens) colectiv'];
$tagMap['cu sens colectiv'] = $tagMap['(cu sens) colectiv'];
$tagMap['în expresie'] = $tagMap['expresie'];
$tagMap['în locuțiune adverbială'] = $tagMap['locuțiune adverbială'];
$tagMap['în sintagma'] = $tagMap['(în) sintagmă'];
$tagMap['în sintagmele'] = $tagMap['(în) sintagmă'];
$tagMap['la plural'] = $tagMap['(la) plural'];
$tagMap['la singular'] = $tagMap['(la) singular'];
$tagMap['plural'] = $tagMap['(la) plural'];
$tagMap['singular'] = $tagMap['(la) singular'];
$tagMap['substantivat'] = $tagMap['(și) substantivat'];
$tagMap['termen bisericesc'] = $tagMap['(termen) bisericesc'];
$tagMap['termen militar'] = $tagMap['(termen) militar'];

$parser = makeParser($GRAMMAR);
$meaningParser = makeParser($MEANING_GRAMMAR);
$etymologyParser = makeParser($ETYMOLOGY_GRAMMAR);

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
    $rep = preprocess($d->internalRep);

    $parsed = $parser->parse($rep);
    if (!$parsed) {
      // Log::error('Cannot parse: %s', $rep);
    } else {
      $meaning = $parsed->findFirst('meaning');
      $etymology = $parsed->findFirst('etymology');
      $reference = $parsed->findFirst('reference');

      if ($meaning) {
        $parsed = $meaningParser->parse($meaning);
        if ($parsed) {
          createMeanings($parsed, $d);
          createEtymologies($etymology, $etymologyParser, $d);
        } else {
          Log::error('Cannot parse meaning for [%s]: [%s]', $d->lexicon, $meaning);
        }
      } else if ($reference) {
        mergeVariant($d, $reference);
      } else {
        Log::error('No meaning nor reference: %s', $rep);
      }
    }
    // exit;
  }

  $offset += BATCH_SIZE;
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

function preprocess($rep) {
  $rep = preg_replace('/ @([A-E]\.) ([IVX]+\.)@ /', ' @$1@ @$2@ ', $rep);
  $rep = preg_replace('/ @([IVX]+\.) (\d\.)@ /', ' @$1@ @$2@ ', $rep);
  return $rep;
}

function mergeVariant($def, $reference) {
  $form = $reference->findFirst('form');
  $form = preg_replace('/\.$/', '', $form); // remove the final dot.

  $defEntries = Model::Factory('Entry')
              ->table_alias('e')
              ->select('e.*')
              ->join('EntryDefinition', ['ed.entryId', '=', 'e.id'], 'ed')
              ->where('ed.definitionId', $def->id)
              ->find_many();
  if (count($defEntries) != 1) {
    // Log::error('Cannot merge %s into %s because %s has %d entries.',
    //            $def->lexicon, $form, $def->lexicon, count($defEntries));
    return;
  }

  $formEntries = Model::Factory('Entry')
               ->table_alias('e')
               ->select('e.*')
               ->join('EntryLexem', ['el.entryId', '=', 'e.id'], 'el')
               ->join('Lexem', ['l.id', '=', 'el.lexemId'], 'l')
               ->where_raw('(l.formNoAccent = binary ?)', [ $form ])
               ->find_many();
  if (count($formEntries) != 1) {
    // Log::error('Cannot merge %s into %s because %s has %d entries.',
    //            $def->lexicon, $form, $form, count($formEntries));
    return;
  }

  if ($defEntries[0]->id == $formEntries[0]->id) {
    // nothing to do, already in the same entry
    return;
  }

  Log::info('Merging %s into %s.', $def->lexicon, $form);
  $defEntries[0]->mergeInto($formEntries[0]->id);
}

function createMeanings($parsed, $def) {
  foreach ($parsed->findAll('reference') as $ref) {
    print "REFERENCE:{$def->lexicon} {$ref}\n";
  }
  foreach ($parsed->findAll('basic') as $rep) {
    $rep = (string)$rep;
    //    print "{$rep}\n";

    // separate qualifiers and try to convert them to labels
    $quals = getQualifiers($rep);

    // Log::info('%s: %s %s', $def->lexicon, implode(' ', $quals), $rep);
  }
}

// Parses qualifiers like "(#Înv.# și #pop.#)" before a meaning.
// Returns a list of tags.
// Modifies $rep when possible.
function getQualifiers(&$rep) {
  global $tagMap;

  $result = [];

  getAbbreviations($rep, $result);

  if (preg_match('/^\(([^)]+)\) (.*)$/', $rep, $m)) {
    $parts = preg_split('/[,;] /', $m[1]);

    foreach ($parts as $part) {
      if (preg_match('/^(#[^#]+#) și (#[^#]+#)$/', $part, $m2)) {
        $result[] = processQualifier($m2[1]);
        $result[] = processQualifier($m2[2]);
      } else {
        $result[] = processQualifier($part);
      }
    }

    if (count(array_filter($result)) == count($result)) {
      // no null elements, safe to remove the qualifier portion
      $rep = $m[2];
    }
  }

  // Consume abbreviations once more -- sometimes they precede the qualifiers, sometimes
  // they follow them.
  getAbbreviations($rep, $result);

  return $result;
}

// Remove abbreviations from the beginning, while we have tags for them.
function getAbbreviations(&$rep, &$result) {
  global $tagMap;

  do {
    $hash = false;
    if (preg_match('/^(și )?#([^#]+)#,? (.*)$/', $rep, $m)) {
      $abbr = mb_strtolower($m[2]);
      $exp = AdminStringUtil::getAbbreviation(SOURCE_ID, $abbr);
      if (isset($tagMap[$exp])) {
        $result[] = $tagMap[$exp];
        $rep = $m[3];
        $hash = true;
      }
    }
  } while ($hash);
}

// Returns a matching tag or null if there is no matching tag.
function processQualifier($qual) {
  global $tagMap;

  $qual = mb_strtolower($qual);
  $qual = expandAllAbbreviations($qual);

  if (isset($tagMap[$qual])) {
    return $tagMap[$qual];
  } else {
    return null;
  }
}

function expandAllAbbreviations($s) {
  $m = [];
  while (preg_match('/^([^#]*)#([^#]+)#(.*)$/', $s, $m)) {
    $s = sprintf('%s%s%s', $m[1], AdminStringUtil::getAbbreviation(SOURCE_ID, $m[2]), $m[3]);
  }
  return $s;
}

function createEtymologies($etymology, $parser, $def) {
  if (!$etymology) {
    return;
  }
  $etymology = (string)$etymology;
  $parsed = $parser->parse($etymology);
  if (!$parsed) {
    print "{$etymology}\n";
  }
}
