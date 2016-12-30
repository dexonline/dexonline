<?php

/**
 * Structure definitions from DEX '98.
 **/

require_once __DIR__ . '/../phplib/util.php';
require_once __DIR__ . '/../phplib/third-party/PHP-parsing-tool/Parser.php';
ini_set('memory_limit', '1024M');

define('SOURCE_ID', 27);
define('MY_USER_ID', 1);
define('BATCH_SIZE', 10000);
define('START_AT', '');
define('EDIT_URL', 'https://dexonline.ro/admin/definitionEdit.php?definitionId=');

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
    '/[-a-zA-ZáàäåăắâấÁÀÄÅĂẮÂẤçÇéèêÉÈÊíîî́ÍÎÎ́óöÓÖ®șȘțȚúüÚÜýÝ\\\\\'(). ]+/',
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
    '"#adj. pron. dem.#"',
    '"#adj. pos.#"',
    '"#adj.#"',
    '"#adv.#"',
    '"#adv. interog.#"',
    '"#art.#"',
    '"#art. nehot.#"',
    '"#conj.#"',
    '"#interj.#"',
    '"#loc.#"',
    '"#loc. adj.#"',
    '"#loc. adj. și adv.#"',
    '"#loc. adv.#"',
    '"#loc. vb.# #impers.#"',
    '"#n. pr.#"',
    '"#num.#"',
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
    'expressions',
    'synonyms',
    'phrase " $#p. ext.#$ " phrase',
    'phrase',
  ],
  'phrase' => [
    '/.*?(?=( @[A-E]+\.@ | @[IVX]+\.@ | @\d\.@ | \*\* | \* | \$#p. ext.#\$ |$))/',
  ],
  'expressions' => [ // A = B. C = D. ...
    '"#Expr.# " expression+" "',
  ],
  'expression' => [
    'key " = " value "."',
  ],
  'key' => [
    '/.*?(?=( = | @[A-E]+\.@ | @[IVX]+\.@ | @\d\.@ | \*\* | \* | \$#p. ext.#\$ |$))/',
  ],
  'value' => [
    '/.*?(?=\.)/',
  ],

  'synonyms' => [
    'synonym+" "',
  ],

  'synonym' => [
    'word /[;,.]/',
  ],
  'word' => [
    '/[^ ;,.]+/',
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
    '("#Cuv.# " | "#Loc.# " | "#Expr.# ") lang',
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

// store meaning synonyms - we can only associate them at the end, once
// we have all the trees
$synMap = [];

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
      Log::error('Cannot parse: %s %s%d', $rep, EDIT_URL, $d->id);
    } else {
      // $meaning = $parsed->findFirst('meaning');
      // $etymology = $parsed->findFirst('etymology');
      // $reference = $parsed->findFirst('reference');

      // if ($meaning) {
      //   $parsed = $meaningParser->parse($meaning);
      //   if ($parsed) {
      //     $tuples1 = createMeanings($parsed, $d);
      //     $tuples2 = createEtymologies($etymology, $etymologyParser, $d);
      //     $tuples = array_merge($tuples1, $tuples2);
      //     makeTree($tuples, $d);
      //   } else {
      //     Log::error('Cannot parse meaning for [%s]: [%s]', $d->lexicon, $meaning);
      //   }
      // } else if ($reference) {
      //   mergeVariant($d, $reference);
      // } else {
      //   Log::error('No meaning nor reference: %s', $rep);
      // }
    }
    // exit;
  }

  $offset += BATCH_SIZE;
  Log::info("Processed $offset definitions.");
} while (count($defs));

Log::info('associating synonyms');

foreach ($synMap as $meaningId => $synList) {
  foreach ($synList as $form) {
    // lookup by main form first
    $trees = Model::factory('Tree')
           ->table_alias('t')
           ->select('t.*')
           ->distinct()
           ->join('TreeEntry', ['t.id', '=', 'te.treeId'], 'te')
           ->join('EntryLexem', ['te.entryId', '=', 'el.entryId'], 'el')
           ->join('Lexem', ['el.lexemId', '=', 'l.id'], 'l')
           ->where('l.formNoAccent', $form)
           ->find_many();

    // lookup by inflected form next
    $trees = Model::factory('Tree')
           ->table_alias('t')
           ->select('t.*')
           ->distinct()
           ->join('TreeEntry', ['t.id', '=', 'te.treeId'], 'te')
           ->join('EntryLexem', ['te.entryId', '=', 'el.entryId'], 'el')
           ->join('InflectedForm', ['el.lexemId', '=', 'i.lexemId'], 'i')
           ->where('i.formNoAccent', $form)
           ->find_many();

    if (count($trees)) {
      foreach ($trees as $t) {
        $r = Model::factory('Relation')->create();
        $r->meaningId = $meaningId;
        $r->treeId = $t->id;
        $r->type = Relation::TYPE_SYNONYM;
        $r->save();
        Log::info('tree [%s], synonym [%s] for meaning %s', $t->description, $form, $meaningId);
      }
    } else {
      Log::info('no trees for synonym [%s] for meaning %s', $form, $meaningId);
    }
  }
}

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
  $result = [];

  foreach ($parsed->findAll('basic') as $rep) {
    $expressions = $rep->findAll('expression');
    $synonyms = $rep->findAll('synonym');
    $phrases = $rep->findAll('phrase');

    if (count($expressions)) {
      $tags = [ getTag('expresie') ];
      foreach ($expressions as $e) {
        $result[] = makeMeaning($e, $tags);
      }

    } else if (count($synonyms)) {
      $synList = [];
      foreach ($synonyms as $s) {
        $word = (string)$s->findFirst('word');
        $word = preg_replace('/\^\d$/', '', $word);
        $word = preg_replace('/\(ă\)$/', '', $word);
        $synList[] = $word;
      }
      $result[] = makeMeaning('', [], $synList);

    } else if (count($phrases) == 2) { // A; p. ext. B
      $p0 = (string)$phrases[0];
      $p0 = preg_replace('/;$/', '.', $p0); // replace a final semicolon with a dot
      $quals = getQualifiers($p0);
      $result[] = makeMeaning($p0, $quals);

      $p1 = AdminStringUtil::capitalize((string)$phrases[1]);
      $tags = [ getTag('prin extensiune') ];
      $result[] = makeMeaning($p1, $tags);

    } else {
      $rep = (string)$rep;
      $quals = getQualifiers($rep);
      $result[] = makeMeaning($rep, $quals);
    }
  }

  return $result;
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
    $origCount = count($result);
    $result = array_filter($result);

    if (count($result) == $origCount) {
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
    $abbr = mb_strtolower($m[2]);
    $exp = AdminStringUtil::getAbbreviation(SOURCE_ID, $abbr);
    if ($abbr != $m[2]) { // is capitalized
      $exp = AdminStringUtil::capitalize($exp);
    }
    $s = sprintf('%s%s%s', $m[1], $exp, $m[3]);
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
        $tags = [ getTag('limba latină') ];
        $result[] = makeEtymology(substr($r, 7), $tags); // skip "#Lat.# "

      } else if ($rule->findFirst('fromLang') && $rule->findFirst('translation')) {
        // '"Din " langList " " formNone " " translation "."',
        $tags = [];

        foreach ($rule->findAll('lang') as $l) {
          $tags[] = expandAndGetTag($l);
        }
        $rep = sprintf('%s %s', $rule->findFirst('formNone'), $rule->findFirst('translation'));
        $result[] = makeEtymology($rep, $tags);

      } else if (($r = $rule->findFirst('fromLang')) && $rule->findFirst('formDot')) {
        // '("Din " | "După ") (langList " " formComma " ")* langList " " formDot',
        $after = (string)$r->getSubnode(0) == 'După ';

        $list = $r->findFirst('list');
        if ($list) {
          foreach ($list->getSubnodes() as $item) {
            $tags = [];
            if ($after) {
              $tags[] = getTag('după');
            }
            foreach ($item->findAll('lang') as $l) {
              $tags[] = expandAndGetTag($l);
            }
            $rep = preg_replace('/,@$/', '@', $item->findFirst('formComma'));
            $result[] = makeEtymology($rep, $tags);
          }
        }

        $tags = [];
        if ($after) {
          $tags[] = getTag('după');
        }
        foreach ($r->getSubnode(2)->findAll('lang') as $l) {
          $tags[] = expandAndGetTag($l);
        }
        $rep = preg_replace('/\.@$/', '@', $r->getSubnode(4));
        $result[] = makeEtymology($rep, $tags);

      } else if ($rule->findFirst('reference')) {
        // '("#V.# " | "Din " | "De la ") formDot',
        $rep = preg_replace('/\.@$/', '@', $rule->findFirst('formDot'));
        switch ($rule->findFirst('reference')->getSubnode(0)) {
          case '#V.# ': $tags = [ getTag('vezi') ]; break;
          case 'Din ': $tags = [ getTag('din') ]; break;
          case 'De la ': $tags = [ getTag('de la') ]; break;
        }
        $result[] = makeEtymology($rep, $tags);

      } else if (($r = $rule->findFirst('withSuffix')) && $rule->findFirst('formItalicBracketDot')) {
        // 'formNone " + #suf.# " suffixNone " (după " lang " " formItalicBracketDot',
        $rep = sprintf('%s + sufix %s', $r->findFirst('formNone'), $r->findFirst('suffixNone'));
        $result[] = makeEtymology(expandAllAbbreviations($rep), []);

        $rep = preg_replace('/\)\.\$$/', '$', $rule->findFirst('formItalicBracketDot'));
        $tags = [
          getTag('după'),
          expandAndGetTag($r->findFirst('lang')),
        ];
        $result[] = makeEtymology($rep, $tags);

      } else if ($r = $rule->findFirst('withSuffix')) {
        // other withSuffix rules
        $result[] = makeEtymology(expandAllAbbreviations($r), []);

      } else if (($r = $rule->findFirst('confer')) && $rule->findFirst('lang')) {
        // '"#Cf.# " (lang " " conferSourceComma " ")* lang " " conferSourceDot',
        $list = $r->findFirst('list');
        if ($list) {
          foreach ($list->getSubnodes() as $item) {
            $tags = [
              getTag('Cf.'),
              expandAndGetTag($item->findFirst('lang')),
            ];
            $rep = preg_replace('/,%$/', '%', $item->findFirst('conferSourceComma'));
            $result[] = makeEtymology($rep, $tags);
          }
        }

        $tags = [
          getTag('Cf.'),
          expandAndGetTag($r->getSubnode(2)),
        ];
        $rep = preg_replace('/\.%$/', '%', $r->findFirst('conferSourceDot'));
        $result[] = makeEtymology($rep, $tags);

      } else if ($r = $rule->findFirst('confer')) {
        // '"#Cf.# " conferSourceDot',
        $rep = preg_replace('/\.%$/', '%', $r->findFirst('conferSourceDot'));
        $tags = [ getTag('Cf.') ];
        $result[] = makeEtymology($rep, $tags);

      } else if ($r = $rule->findFirst('import')) {
        // '("#Cuv.# " | "#Loc.# " | "#Expr.# ") lang',
        $type = mb_strtolower(preg_replace('/ $/', '', $r->getSubnode(0)));
        $tags = [
          expandAndGetTag($type),
          expandAndGetTag($r->findFirst('lang')),
        ];
        $result[] = makeEtymology('', $tags);

      } else if (($r = $rule->findFirst('glue')) && $rule->findFirst('formItalicBracketDot')) {
        // 'formNone+" + " " (după " lang " " formItalicBracketDot',
        $result[] = makeEtymology($r->getSubnode(0), []);

        $rep = preg_replace('/\)\.\$$/', '$', $rule->findFirst('formItalicBracketDot'));
        $tags = [
          getTag('după'),
          expandAndGetTag($r->findFirst('lang')),
        ];
        $result[] = makeEtymology($rep, $tags);

      } else if ($r = $rule->findFirst('glue')) {
        // other glue rules
        // remove the final . (or the dot from .@)
        $rep = preg_replace('/\.$/', '', $r);
        $rep = preg_replace('/\.@$/', '@', $rep);

        $result[] = makeEtymology(expandAllAbbreviations($rep), []);

      } else if ($r = $rule->findFirst('regression')) {
        // '"Din " formNone " (derivat regresiv)."',
        $rep = $r->findFirst('formNone');
        $tags = [
          getTag('derivat regresiv'),
          getTag('din'),
        ];
        $result[] = makeEtymology($rep, $tags);

      } else if ($r = $rule->findFirst('specials')) {
        switch ((string)$r) {
          case 'Denumire comercială.': $tags = [ getTag('denumire comercială') ]; break;
          case '#Et. nec.#': $tags = [ getTag('necunoscută') ]; break;
          case 'Formație onomatopeică.': $tags = [ getTag('formație onomatopeică') ]; break;
          case 'Onomatopee.': $tags = [ getTag('onomatopee') ]; break;
          case 'Probabil formație onomatopeică.':
            $tags = [
              getTag('probabil'),
              getTag('formație onomatopeică'),
            ];
            break;
        }
        $result[] = makeEtymology('', $tags);

      } else {
        Log::error('Cannot parse etymology for [%s] %s', $def->lexicon, $etymology);
      }
    }
  } else {
    // Create a meaning using the whole text
    $result[] = makeEtymology($etymology, []);
  }

  return $result;
}

function makeMeaningWithType($type, $internalRep, $tags, $synonyms) {
  $m = Model::factory('Meaning')->create();
  $m->type = $type;
  $m->internalRep = $internalRep;

  return [
    'meaning' => $m,
    'tags' => $tags,
    'synonyms' => $synonyms,
  ];
}

function makeMeaning($internalRep, $tags, $synonyms = []) {
  return makeMeaningWithType(Meaning::TYPE_MEANING, $internalRep, $tags, $synonyms);
}

// etymologies don't have synonyms
function makeEtymology($internalRep, $tags) {
  // replace % signs with @ signs
  $internalRep = preg_replace('/%/', '@', $internalRep);

  return makeMeaningWithType(Meaning::TYPE_ETYMOLOGY, $internalRep, $tags, []);
}

function makeTree($tuples, $def) {
  global $synMap;

  $entries = Model::factory('Entry')
           ->table_alias('e')
           ->select('e.*')
           ->join('EntryDefinition', ['ed.entryId', '=', 'e.id'], 'ed')
           ->where('ed.definitionId', $def->id)
           ->find_many();
  if (!count($entries)) {
    Log::error('No associated entries for [%s]', $def->lexicon);
    return;
  }

  $minStructStatus = Entry::STRUCT_STATUS_DONE;
  foreach ($entries as $e) {
    $minStructStatus = min($minStructStatus, $e->structStatus);
  }
  if ($minStructStatus > Entry::STRUCT_STATUS_NEW) {
    Log::error('All entries have been structured for [%s]', $def->lexicon);
    return;
  }

  // Look for an empty tree...
  $t = null;
  foreach ($entries as $e) {
    foreach ($e->getTrees() as $tree) {
      if (!Meaning::get_by_treeId($tree->id)) {
        $t = $tree;
      }
    }
  }

  // ...or create one...
  if (!$t) {
    $t = Model::factory('Tree')->create();
    $t->description = $entries[0]->description;
    $t->save();
  }
  if ($t->status == Tree::ST_HIDDEN) {
    $t->status = Tree::ST_VISIBLE;
    $t->save();
  }

  // ... and associate it with all the entries
  foreach ($entries as $e) {
    TreeEntry::associate($t->id, $e->id);
  }

  // printf("*** [%s] [%s]\n", $def->lexicon, $def->internalRep);
  $order = 0;
  foreach ($tuples as $tuple) {
    $m = $tuple['meaning'];
    $m->parentId = 0;
    $m->userId = MY_USER_ID;
    $m->treeId = $t->id;
    $m->internalRep = expandAllAbbreviations($m->internalRep);
    $m->htmlRep = AdminStringUtil::htmlize($m->internalRep, 0);
    $m->displayOrder = ++$order;
    if ($m->type == Meaning::TYPE_MEANING) {
      $m->breadcrumb = "{$m->displayOrder}.";
    } else {
      $m->breadcrumb = '';
    }
    // printf("[%s] %s %s [%s]\n",
    //        $def->lexicon,
    //        $m->getDisplayTypeName(),
    //        implode(' ', $tuple['tags']),
    //        $m->internalRep);
    $m->save();

    // We cannot associate synonyms here because the target trees may not yet exist.
    // Store them for the end.
    if (count($tuple['synonyms'])) {
      $synMap[$m->id] = $tuple['synonyms'];
    }

    foreach($tuple['tags'] as $tag) {
      ObjectTag::associate(ObjectTag::TYPE_MEANING, $m->id, $tag->id);
    }
    MeaningSource::associate($m->id, SOURCE_ID);
  }

  foreach ($entries as $e) {
    $e->deleteEmptyTrees();
    $e->structStatus = Entry::STRUCT_STATUS_IN_PROGRESS;
    $e->save();
  }

  Log::info('saved tree for [%s]', $def->lexicon);
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
