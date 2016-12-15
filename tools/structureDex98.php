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
define('START_AT', 'afurisenie');

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
    'rule+" "',
  ],
  'rule' => [
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
    '("#V.# " | "Din " | "De la ") formDot',
  ],
  'fromLang' => [
    '("Din " | "După ") (langList " " formComma " ")* langList " " formDot',
    '"Din " langList " " formNone " " translation "."',
  ],
  'import' => [
    '"#Cuv.# " lang',
    '"#Loc.# " lang',
    '"#Expr.# " lang',
  ],
  'withSuffix' => [
    'formNone elision? " + #suf.# " suffixDot',
    'formNone " (#n. pr.#) + #suf.# " suffixDot',
    'formNone " + #suf.# " suffixNone " (după " lang " " formItalicBracketDot',
  ],
  'glue' => [
    '(formNone elision? " + ")+ formDot',
    '(formNone elision? " + ")+ formNone elision "."',
    'formNone+" + " " (după " lang " " formItalicBracketDot',
  ],
  'confer' => [
    '"#Cf.# " conferSourceDot',
    '"#Cf.# " (lang " " conferSourceComma " ")* lang " " conferSourceDot',
  ],
  'regression' => [
    '"Din " formNone " (derivat regresiv)."',
  ],
  'latin' => [
    '"#Lat.# " formDot',
    '"#Lat.# " formNone " " translation "."',
    '"#Lat.# " formNone / \([=<] / formItalicBracketDot',
  ],
  'specials' => [
    '"Denumire comercială."',
    '"#Et. nec.#"',
    '"Formație onomatopeică."',
    '"Onomatopee."',
    '"Probabil formație onomatopeică."',
  ],
  'langList' => [
    'lang+", "',
  ],
  'lang' => [
    '/#[^#]+#/',
  ],
  'formComma' => [
    '/@[^@]+,@/',
  ],
  'formDot' => [
    '/@[^@]+\.@/',
  ],
  'formNone' => [
    '/@[^@]+@/',
  ],
  'formItalicBracketDot' => [
    '/\$[^$]+\)\.\$/',
  ],
  'suffixDot' => [
    '/\$-[^$]+\.\$/',
  ],
  'suffixNone' => [
    '/\$-[^$]+\$/',
  ],
  'conferSourceComma' => [
    '/%[^%]+,%/',
  ],
  'conferSourceDot' => [
    '/%[^%]+\.%/',
  ],
  'translation' => [
    '/"[^"]+"/',
  ],
  'elision' => [
    '/\[[^]]+\]/',
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
$tagMap['din'] = $tagMap['(provine) din'];
$tagMap['în expresie'] = $tagMap['expresie'];
$tagMap['în locuțiune adverbială'] = $tagMap['locuțiune adverbială'];
$tagMap['în sintagma'] = $tagMap['(în) sintagmă'];
$tagMap['în sintagmele'] = $tagMap['(în) sintagmă'];
$tagMap['la plural'] = $tagMap['(la) plural'];
$tagMap['la singular'] = $tagMap['(la) singular'];
$tagMap['(limba) sârbă, croată'] = $tagMap['limba sârbă, croată'];
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
          // $tuples1 = createMeanings($parsed, $d);
          $tuples1 = [];
          $tuples2 = createEtymologies($etymology, $etymologyParser, $d);
          $tuples = array_merge($tuples1, $tuples2);
          makeTree($tuples);
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
  return [];
}

// Parses qualifiers like "(#Înv.# și #pop.#)" before a meaning.
// Returns a list of tags.
// Modifies $rep when possible.
function getQualifiers(&$rep) {
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
  do {
    $hash = false;
    if (preg_match('/^(și )?#([^#]+)#,? (.*)$/', $rep, $m)) {
      $abbr = mb_strtolower($m[2]);
      $exp = AdminStringUtil::getAbbreviation(SOURCE_ID, $abbr);
      if (getTag($exp, false)) {
        $result[] = getTag($exp);
        $rep = $m[3];
        $hash = true;
      }
    }
  } while ($hash);
}

// Returns a matching tag or null if there is no matching tag.
function processQualifier($qual) {
  $qual = mb_strtolower($qual);
  $qual = expandAllAbbreviations($qual);

  return getTag($qual, false);
}

function expandAllAbbreviations($s) {
  $m = [];
  while (preg_match('/^([^#]*)#([^#]+)#(.*)$/', $s, $m)) {
    $s = sprintf('%s%s%s', $m[1], AdminStringUtil::getAbbreviation(SOURCE_ID, $m[2]), $m[3]);
  }
  return $s;
}

// returns an array of <meaning, tags> tuples
function createEtymologies($etymology, $parser, $def) {

  if (!$etymology) {
    return [];
  }
  $result = [];

  $etymology = (string)$etymology;
  $parsed = $parser->parse($etymology);

  if ($parsed) {
    // Create a tuple from every rule
    foreach ($parsed->findAll('rule') as $rule) {

      if ($r = $rule->findFirst('latin')) {
        // '"#Lat.# " etc.'
        $m = makeEtymology(substr($r, 7)); // skip "#Lat.# "
        $tags = [ getTag('limba latină') ];
        $result[] = [ 'meaning' => $m, 'tags' => $tags ];

      } else if ($rule->findFirst('fromLang') && $rule->findFirst('translation')) {
        // '"Din " langList " " formNone " " translation "."',
        $tags = [];

        foreach ($rule->findAll('lang') as $l) {
          $tags[] = expandAndGetTag($l);
        }
        $m = makeEtymology(sprintf('%s %s',
                                   $rule->findFirst('formNone'),
                                   $rule->findFirst('translation')));
        $result[] = [ 'meaning' => $m, 'tags' => $tags ];

      } else if ($rule->findFirst('fromLang') && $rule->findFirst('formDot')) {
        // '("Din " | "După ") (langList " " formComma " ")* langList " " formDot',
        $fromLang = $rule->findFirst('fromLang');
        $after = (string)$fromLang->getSubnode(0) == 'După ';

        $list = $fromLang->findFirst('list');
        if ($list) {
          foreach ($list->getSubnodes() as $item) {
            $tags = [];
            if ($after) {
              $tags[] = getTag('după');
            }
            foreach ($item->findAll('lang') as $l) {
              $tags[] = expandAndGetTag($l);
            }
            $m = makeEtymology(preg_replace('/,@$/', '@', $item->findFirst('formComma')));
            $result[] = [ 'meaning' => $m, 'tags' => $tags ];
          }
        }

        $tags = [];
        if ($after) {
          $tags[] = getTag('după');
        }
        foreach ($fromLang->getSubnode(2)->findAll('lang') as $l) {
          $tags[] = expandAndGetTag($l);
        }
        $m = makeEtymology(preg_replace('/\.@$/', '@', $fromLang->getSubnode(4)));
          
        $result[] = [ 'meaning' => $m, 'tags' => $tags ];

      } else if ($rule->findFirst('reference')) {
        // '("#V.# " | "Din " | "De la ") formDot',
        $m = makeEtymology(preg_replace('/\.@$/', '@', $rule->findFirst('formDot')));
        switch ($rule->findFirst('reference')->getSubnode(0)) {
          case '#V.# ': $tags = [ getTag('vezi') ]; break;
          case 'Din ': $tags = [ getTag('din') ]; break;
          case 'De la ': $tags = [ getTag('de la') ]; break;
        }
        $result[] = [ 'meaning' => $m, 'tags' => $tags ];

      } else if (($r = $rule->findFirst('withSuffix')) && $rule->findFirst('formItalicBracketDot')) {
        // 'formNone " + #suf.# " suffixNone " (după " lang " " formItalicBracketDot',
        $rep = sprintf('%s + sufix %s', $r->findFirst('formNone'), $r->findFirst('suffixNone'));
        $m = makeEtymology(expandAllAbbreviations($rep));
        $tags = [];
        $result[] = [ 'meaning' => $m, 'tags' => $tags ];

        $form = preg_replace('/\)\.\$$/', '$', $rule->findFirst('formItalicBracketDot'));
        $rep = sprintf('%s %s', $r->findFirst('lang'), $form);
        $m = makeEtymology(expandAllAbbreviations($rep));
        $tags = [ getTag('după') ];
        $result[] = [ 'meaning' => $m, 'tags' => $tags ];

      } else if ($r = $rule->findFirst('withSuffix')) {
        // other withSuffix rules
        $m = makeEtymology(expandAllAbbreviations($r));
        $tags = [];
        $result[] = [ 'meaning' => $m, 'tags' => $tags ];
        // printf("[%s] %s %s\n", $def->lexicon, implode(',', $tags), $m->internalRep);

      } else {
        print "*** [{$def->lexicon}] {$etymology}\n";
        print $rule->dump() . "\n";
        exit;
      }
    }
  } else {
    // Create a meaning using the whole text
    $m = Model::factory('Meaning')->create();
    $m->type = Meaning::TYPE_ETYMOLOGY;
    $m->internalRep = $etymology;
    $result[] = [
      'meaning' => $m,
      'tags' => [],
    ];
  }

  return $result;
}

function makeEtymology($internalRep) {
  $m = Model::factory('Meaning')->create();
  $m->type = Meaning::TYPE_ETYMOLOGY;
  $m->internalRep = $internalRep;
  return $m;
}

function makeTree($tuples) {
  // TODO: figure out which tree to use
  $t = null;

  foreach ($tuples as $tuple) {
    $m = $tuple['meaning'];
    $m->parentId = 0;
    $m->userId = MY_USER_ID;
    // $m->treeId = $t->id; TODO
    $m->htmlRep = AdminStringUtil::htmlize($m->internalRep, 0);    
  }
}

// If no tag with the given value exists, then
// * exists if $assert = true
// * returns null if $assert = false
function getTag($value, $assert = true) {
  global $tagMap;

  if (isset($tagMap[$value])) {
    return $tagMap[$value];
  } else if ($assert) {
    Log::error("No tag: {$value}");
    exit;
  } else {
    return null;
  }
}

function expandAndGetTag($value) {
  $value = expandAllAbbreviations($value);
  return getTag($value);
}
