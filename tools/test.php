<?php

require_once __DIR__ . '/../phplib/Core.php';
assert_options(ASSERT_BAIL, 1);

function fail($message) {
  print "$message\n";
  debug_print_backtrace();
  exit;
}

function assertEquals($expected, $actual) {
  if (is_array($expected) ^ is_array($actual)) {
    fail("Only one argument is of type array.");
  }

  if (is_array($expected)) {
    if (count($expected) != count($actual)) {
      fail("Different array lengths.");
    }

    foreach ($expected as $key => $value) {
      if (!isset($actual[$key])) {
        fail("Missing key [{$key}] from actual results.");
      }
      assertEquals($value, $actual[$key]);
    }
  } else {
    // scalar comparison
    if ($expected != $actual) {
      print "Assertion failed.\n";
      print "  expected [$expected]\n";
      print "  actual   [$actual]\n";
      debug_print_backtrace();
      exit;
    }
  }
}

// foreach $key => $value, asserts that f($key, $arg1, $arg2...) == $value
function assertTransform($f, $extraArgs, $data) {
  foreach ($data as $before => $after) {
    $args = array_merge([ $before ], $extraArgs);
    assertEquals($after, call_user_func_array($f, $args));
  }
}

/********************* Tests for Str.php ************************/

// Check that we've got the shorthand->Unicode mappings right
assertTransform('Str::cleanup', [], [
  '~a^a^i,s,t' => '~a^a^i,s,t',
  "'a'e'i'o'u'y" => "'a'e'i'o'u'y",
  '\\ş ş \\º º' => '\\ş ș \\º °',
]);

assertTransform('Str::unicodeToLatin', [], [
  'ắčèğýžẮČÈĞÝŽ' => 'acegyzACEGYZ',
]);

assertTransform('mb_strtolower', [], [
  'mama' => 'mama',
  'maMa' => 'mama',
  'MAmA' => 'mama',
  'MAmă' => 'mamă',
  'MAmĂ' => 'mamă',
  'ABCÚÙÛ' =>'abcúùû',
  'Ÿ' =>  'ÿ',
]);

assertTransform('mb_strtoupper', [], [
  'MAMA' => 'MAMA',
  'MAmA' => 'MAMA',
  'MamĂ' => 'MAMĂ',
  'maMă' => 'MAMĂ',
  'abcúùû' => 'ABCÚÙÛ',
  'ÿ' => 'Ÿ',
]);

// Check that we're using the right encoding
assertEquals(mb_strlen('íÍìÌîÎ'), 6);
assertEquals(mb_substr('íÍìÌîÎ', 3, 2), 'Ìî');

// Test string reversal
assertTransform('Str::reverse', [], [
  'abc' => 'cba',
  'ăâîșț' => 'țșîâă',
  'ĂÂÎȘȚ' => 'ȚȘÎÂĂ',
]);

// Test ord() and chr()
assertTransform('Str::ord', [], [
  'A' => 65,
  "\n" => 10,
  'ă' => 259,
]);
assertTransform('Str::chr', [], [
  65 => 'A',
  10 => "\n",
  259 => 'ă',
]);

assertTransform('Str::htmlize', [ 0 ], [
  // references
  'zzz |x|y|' =>
  ['zzz <a class="ref" href="/definitie/y">x</a>', []],

  'zzz |ă|î|' =>
  ['zzz <a class="ref" href="/definitie/î">ă</a>', []],

  'zzz |ab cd ef|ab cd ef|' =>
  ['zzz <a class="ref" href="/definitie/ab cd ef">ab cd ef</a>', []],

  'zzz |ab cd ef (1)|ab cd ef (1)|' =>
  ['zzz <a class="ref" href="/definitie/ab cd ef (1)">ab cd ef (1)</a>', []],

  'zzz |ab cd õÕ (@1@)|ab cd õÕ|' =>
  ['zzz <a class="ref" href="/definitie/ab cd õÕ">ab cd õÕ (<b>1</b>)</a>', []],

  'zzz |x|y| foobar |z|t|' =>
  [
    'zzz <a class="ref" href="/definitie/y">x</a> foobar <a class="ref" href="/definitie/t">z</a>',
    [],
  ],

  // mentions
  'măr pară[[1234567890]] prună' =>
  [
    'măr <span data-toggle="popover" data-html="true" data-placement="auto right" ' .
    'class="treeMention" title="1234567890">pară</span> prună',
    [],
  ],

  'măr pară[1234567890] prună' =>
  [
    'măr <span data-toggle="popover" data-html="true" data-placement="auto right" ' .
    'class="mention" title="1234567890">pară (<b>?</b>)</span> prună',
    [],
  ],

  'măr pară[1234567890*] prună' =>
  [
    'măr <span data-toggle="popover" data-html="true" data-placement="auto right" ' .
    'class="mention" title="1234567890">pară</span> prună',
    [],
  ],

  'măr pară[1234567890**] prună' =>
  [
    'măr <span data-toggle="popover" data-html="true" data-placement="auto right" ' .
    'class="mention" title="1234567890">(<b>?</b>)</span> prună',
    [],
  ],

  // other internal notations
  'mama\\tata' =>
  ['mamatata', []],

  'mama\\@tata' =>
  ['mama@tata', []],

  '@$bold and italic$ bold only@ regular.' =>
  ['<b><i>bold and italic</i> bold only</b> regular.', []],

  'foo < $bar$' =>
  ['foo &lt; <i>bar</i>', []],

  '%cățel%' =>
  ['<span class="spaced">cățel</span>', []],

  'foo %bar <% bib' =>
  ['foo <span class="spaced">bar &lt;</span> bib', []],

  '%unu, doi%' =>
  ['<span class="spaced">unu, doi</span>', []],

  'A %unu% B %doi% C' =>
  ['A <span class="spaced">unu</span> B <span class="spaced">doi</span> C', []],

  'A \\%unu% B %doi% C' =>
  ['A %unu<span class="spaced"> B </span>doi% C', []],

  '%ab @cd@%' =>
  ['<span class="spaced">ab <b>cd</b></span>', []],

  "okely\ndokely" =>
  ["okely\ndokely", []],

  "@ACC'ENT@" =>
  ['<b>ACC<span class="tonic-accent">E</span>NT</b>', []],

  "@ACC\\'ENT@" =>
  ['<b>ACC’ENT</b>', []],

  'copil^{+123}. copil_{-123}----' =>
  ['copil<sup>+123</sup>. copil<sub>-123</sub>----', []],

  'copil^i^2' =>
  ['copil^i<sup>2</sup>', []],

  'abc __de__ fgh' =>
  ['<span class="deemph">abc <span class="emph">de</span> fgh</span>', []],

  'abc __de__ fgh __ij__ klm' =>
  [
    '<span class="deemph">abc <span class="emph">de</span> fgh ' .
    '<span class="emph">ij</span> klm</span>',
    [],
  ],

  //escape characters
  'abc\\$def$ghi' =>
  ['abc$def$ghi', []],

  'abc\\^{def}ghi' =>
  ['abc^{def}ghi', []],

  // remove unwanted parts of definition
  "I\\'m the ▶invisible ◀man▶ - some lyrics from Queen◀." =>
  ["I’m the man.", []],

]);

assertTransform('Str::htmlize', [ 0, true ], [
  "okely\ndokely" => ["okely<br>\ndokely", []],
]);

// htmlize with footnotes
$internalRep = 'one two{{note/123}} three{{another note/456}} four';
list($html, $footnotes) = Str::htmlize($internalRep, 1);
assertEquals('one two<sup class="footnote">[1]</sup> three' .
             '<sup class="footnote">[2]</sup> four',
             $html);

assertEquals(2, count($footnotes));

assertEquals(123, $footnotes[0]->userId);
assertEquals(1, $footnotes[0]->rank);
assertEquals('note', $footnotes[0]->htmlRep);

assertEquals(456, $footnotes[1]->userId);
assertEquals(2, $footnotes[1]->rank);
assertEquals('another note', $footnotes[1]->htmlRep);

$data = [
  [
    '@FILLER@ adj. dem. (antepus), art.',
    '@FILLER@ #adj. dem.# (antepus), art.',
    1,
    [['abbrev' => 'art.', 'position' => 32, 'length' => 4]],
  ],
  [
    '@FILLER@ loc. adv. și adj. @MORE FILLER@',
    '@FILLER@ #loc. adv. și adj.# @MORE FILLER@',
    1,
    [],
  ],
  [
    '@FILLER@ arg. șarg. catarg. ăarg. țarg. @FILLER@',
    '@FILLER@ #arg.# șarg. catarg. ăarg. țarg. @FILLER@',
    1,
    [],
  ],
  [
    '@FILLER@ et. nec.',
    '@FILLER@ #et. nec.#',
    1,
    [],
  ],
  [
    '@FILLER@ Înv. @MORE FILLER@', // Unicode uppercase
    '@FILLER@ #Înv.# @MORE FILLER@',
    1,
    [],
  ],
  [
    '@FILLER@ art. hot. @FILLER@',
    '@FILLER@ #art. hot.# @FILLER@',
    1,
    [],
  ],
  [
    '@FILLER@ #art. hot.# @FILLER@',
    '@FILLER@ #art. hot.# @FILLER@',
    1,
    [],
  ],
  [
    'FOO ornit. BAR',
    'FOO ornit. BAR',
    99, // non-existent source
    [],
  ],
  [
    'FOO BAR', // no abbreviations
    'FOO BAR',
    1,
    [],
  ],
  [
    'FOO dat. BAR',  // ambiguous abbreviations
    'FOO dat. BAR',
    1,
    [['abbrev' => 'dat.', 'position' => 4, 'length' => 4]],
  ],
  [
    'FOO dat. arh. loc. adv. BAR',
    'FOO dat. #arh.# #loc. adv.# BAR',
    1,
    [['abbrev' => 'dat.', 'position' => 4, 'length' => 4]],
  ],
  [
    'FOO s-a dus BAR',
    'FOO s-a dus BAR',
    32,
    [['abbrev' => 's', 'position' => 4, 'length' => 1]],
  ],
  [
    "c'amătă",
    "c'amătă", // not #c#'amătă
    53,
    [],
  ],
  [
    'egal cu c^2',
    'egal cu c^2',
    53,
    [],
  ],
  [
    'ab @c@',
    'ab @#c#@',
    53,
    [],
  ],
  [
    "ab ltr\n",
    "ab #ltr#\n",
    53,
    [],
  ],
];
foreach ($data as list($before, $after, $sourceId, $ambiguous)) {
  list($actual, $matches) = Abbrev::markAbbreviations($before, $sourceId);
  assertEquals($after, $actual);
  assertEquals($ambiguous, $matches);
}

assertEquals(
  ["FOO <abbr class=\"abbrev\" data-html=\"true\" title=\"farmacie; farmacologie\">farm.</abbr> BAR", []],
  Str::htmlize("FOO #farm.# BAR", 1)); /** Semicolon in abbreviation **/
assertEquals(
  ["FOO <abbr class=\"abbrev\" data-html=\"true\" title=\"substantiv masculin\">s. m.</abbr> BAR", []],
  Str::htmlize("FOO #s. m.# BAR", 1));
$errors = [];
assertEquals(
  ["FOO <abbr class=\"abbrev\" data-html=\"true\" title=\"abreviere necunoscută\">brrb. ghhg.</abbr> BAR", []],
  Str::htmlize("FOO #brrb. ghhg.# BAR", 1, false, $errors));
assertEquals(
  ['Abreviere necunoscută: «brrb. ghhg.».'],
  $errors);

$internalRep = '@M\'ARE^2@, $mări$, #s. f.# Nume generic dat vastelor întinderi de apă stătătoare, adânci și sărate, de pe suprafața |Pământului|Pământ|, care de obicei sunt unite cu oceanul printr-o strâmtoare; parte a oceanului de lângă țărm; $#p. ext.#$ ocean. * #Expr.# $Marea cu sarea$ = mult, totul; imposibilul. $A vântura mări și țări$ = a călători mult. $A încerca marea cu degetul$ = a face o încercare, chiar dacă șansele de reușită sunt minime. $Peste (nouă) mări și (nouă) țări$ = foarte departe. ** #Fig.# Suprafață vastă; întindere mare; imensitate. ** #Fig.# Mulțime (nesfârșită), cantitate foarte mare. - Lat. @mare, -is@.';
list ($actualRep, $ambiguous) =
  Str::sanitize('@M\'ARE^2@, $mări$, s. f. Nume generic dat vastelor întinderi de apă stătătoare, adânci și sărate, de pe suprafața |Pământului|Pământ|, care de obicei sunt unite cu oceanul printr-o strâmtoare; parte a oceanului de lângă țărm; $p. ext.$ ocean. * Expr. $Marea cu sarea$ = mult, totul; imposibilul. $A vântura mări și țări$ = a călători mult. $A încerca marea cu degetul$ = a face o încercare, chiar dacă șansele de reușită sunt minime. $Peste (nouă) mări și (nouă) țări$ = foarte departe. ** Fig. Suprafață vastă; întindere mare; imensitate. ** Fig. Mulțime (nesfârșită), cantitate foarte mare. - Lat. @mare, -is@.', 1);
assertEquals($internalRep, $actualRep);
assertEquals([
  0 => [
    'abbrev' => 'lat.',
    'position' => 650,
    'length' => 4,
  ]
], $ambiguous);

list($actualRep, $ignored)
  = Str::htmlize($internalRep, 1);
assertEquals('<b>M<span class="tonic-accent">A</span>RE<sup>2</sup></b>, <i>mări</i>, <abbr class="abbrev" data-html="true" title="substantiv feminin">s. f.</abbr> Nume generic dat vastelor întinderi de apă stătătoare, adânci și sărate, de pe suprafața <a class="ref" href="/definitie/Pământ">Pământului</a>, care de obicei sunt unite cu oceanul printr-o strâmtoare; parte a oceanului de lângă țărm; <i><abbr class="abbrev" data-html="true" title="prin extensiune">p. ext.</abbr></i> ocean. ◊ <abbr class="abbrev" data-html="true" title="expresie">Expr.</abbr> <i>Marea cu sarea</i> = mult, totul; imposibilul. <i>A vântura mări și țări</i> = a călători mult. <i>A încerca marea cu degetul</i> = a face o încercare, chiar dacă șansele de reușită sunt minime. <i>Peste (nouă) mări și (nouă) țări</i> = foarte departe. ♦ <abbr class="abbrev" data-html="true" title="figurat">Fig.</abbr> Suprafață vastă; întindere mare; imensitate. ♦ <abbr class="abbrev" data-html="true" title="figurat">Fig.</abbr> Mulțime (nesfârșită), cantitate foarte mare. – Lat. <b>mare, -is</b>.',
             $actualRep);

$msg1 = 'Unele dintre caracterele @$%#{} nu sunt împerecheate corect.';
$msg2 = 'Unele dintre caracterele [] nu sunt împerecheate corect.';
$msg3 = 'Unele dintre caracterele "«» nu sunt împerecheate corect.';
$data = [
  '@a^{bc}d@'      => [],
  '@a^\\{bc}d@'    => [ $msg1 ],
  '@a#bc@d#'    => [ $msg1 ],
  'ab@cd$ef@gh$ij' => [ $msg1 ],
  'ab@cd'          => [ $msg1 ],
  'ab@cd@ef@gh'    => [ $msg1 ],
  '[[[]]'          => [ $msg2 ],
  '[[]]]'          => [ $msg2 ],
  'a[b@c[[]d'      => [ $msg1, $msg2 ],
  '"out «in» out"' => [],
  '«out "in" out»' => [],
  'ab"cd'          => [ $msg3 ],
];
foreach ($data as $s => $errors) {
  $e = [];
  Str::reportSanitizationErrors($s, $e);
  assertEquals($errors, $e);
}

// Test various capitalization combos with abbreviations
// - When internalizing the definition, preserve the capitalization if
//   the defined abbreviation is capitalized; otherwise, capitalize
//   the first letter (if necessary) and convert the rest to lowercase
// - If the defined abbreviation contains capital letters, then only
//   match text with identical capitalization
// - If the defined abbreviation does not contain capital letters,
//   then match text regardless of capitalization
// - When htmlizing the definition, use the expansion from the
//   abbreviation that best matches the case.
$data = [
  [
    'FILLER adv. FILLER',
    'FILLER #adv.# FILLER',
    'FILLER <abbr class="abbrev" data-html="true" title="adverb">adv.</abbr> FILLER',
    1,
  ],
  [
    'FILLER Adv. FILLER',
    'FILLER #Adv.# FILLER',
    'FILLER <abbr class="abbrev" data-html="true" title="adverb">Adv.</abbr> FILLER',
    1,
  ],
  [
    'FILLER BWV FILLER',
    'FILLER #BWV# FILLER',
    'FILLER <abbr class="abbrev" data-html="true" title="Bach-Werke-Verzeichnis">BWV</abbr> FILLER',
    32,
  ],
  [
    'FILLER bwv FILLER',
    'FILLER bwv FILLER',
    'FILLER bwv FILLER',
    32,
  ],
  [
    'FILLER bWv FILLER',
    'FILLER bWv FILLER',
    'FILLER bWv FILLER',
    32,
  ],
  [
    'FILLER ed. FILLER',
    'FILLER #ed.# FILLER',
    'FILLER <abbr class="abbrev" data-html="true" title="ediție, editat">ed.</abbr> FILLER',
    32,
  ],
  [
    'FILLER Ed. FILLER',
    'FILLER #Ed.# FILLER',
    'FILLER <abbr class="abbrev" data-html="true" title="Editura">Ed.</abbr> FILLER',
    32,
  ],
  [
    'FILLER ED. FILLER', // ED. does not match either ed. or Ed.
    'FILLER ED. FILLER',
    'FILLER ED. FILLER',
    32,
  ],
  [
    'FILLER RRHA, TMC FILLER', // abbreviation includes special characters
    'FILLER #RRHA, TMC# FILLER',
    "FILLER <abbr class=\"abbrev\" data-html=\"true\" title=\"Revue Roumaine d’Histoire de l’Art, " .
    "série Théâtre, Musique, Cinématographie\">RRHA, TMC</abbr> FILLER",
    32,
  ],
  [
    'FILLER adj. interog.-rel. FILLER',
    'FILLER #adj. interog.-rel.# FILLER',
    'FILLER <abbr class="abbrev" data-html="true" title="adjectiv interogativ-relativ">' .
    'adj. interog.-rel.</abbr> FILLER',
    1,
  ],
  [
    'AGNUS DEI', // abbreviation is not delimited by spaces
    'AGNUS DEI',
    'AGNUS DEI',
    32,
  ],
];
foreach ($data as list($raw, $internal, $html, $sourceId)) {
  list($s, $ambiguous) = Abbrev::markAbbreviations($raw, $sourceId);
  assertEquals($internal, $s);
  assertEquals([], $ambiguous);

  list ($s, $ignored) = Str::htmlize($internal, $sourceId, $errors);
  assertEquals($html, $s);
}

assertTransform('Str::removeAccents', [], [
  'cásă' => 'casă',
]);

assertTransform('Str::changeAccents', [], [
  'cásă' => "c'asă",
]);

assertTransform('Str::cleanupQuery', [], [
  "'mama'" => 'mama',
  '"mama"' => 'mama',
  "aăbc<mamă foo bar>def" => 'aăbcdef',
  "AĂBC<MAMĂ FOO BAR>DEF" => 'AĂBCDEF',
  "aăbc<mamă foo bar>def" => 'aăbcdef',
  "aĂBc<mamă foo bar>def" => 'aĂBcdef',
  '12&qweasd;34' => '1234',
]);

assert(Str::hasDiacritics('mamă'));
assert(!Str::hasDiacritics('mama'));

$def = Model::factory('Definition')->create();
$def->sourceId = 1;
$def->internalRep = '@abcd@';
assertEquals('abcd', $def->extractLexicon());
$def->internalRep = '@$wxyz$@';
assertEquals('wxyz', $def->extractLexicon());
$def->sourceId = 7;
$def->internalRep = '@A SE JUCÁ@ lalala';
assertEquals('juca', $def->extractLexicon());
$def->internalRep = '@ȚARĂ^1@ lalala';
assertEquals('țară', $def->extractLexicon());

assert(Str::hasRegexp('asd[0-9]'));
assert(!Str::hasRegexp('ăâîșț'));
assert(Str::hasRegexp('cop?l'));

assertTransform('Str::dexRegexpToMysqlRegexp', [], [
  'cop*l' => "like 'cop%l'",
  'cop?l' => "like 'cop_l'",
  'cop[a-z]l' => "rlike '^(cop[a-z]l)$'",
  'cop[^a-z]l' => "rlike '^(cop[^a-z]l)$'",
  'cop[â-z]l' => "rlike '^(cop[â-z]l)$'",
  'cop[â-z]l*' => "rlike '^(cop[â-z]l.*)$'",
]);

$data = [
  [ 'mama', false, false, false ],
  [ 'mamă', true, false, false ],
  [ 'cop?l', false, true, false ],
  [ 'cop[cg]l', false, true, false ],
  [ 'căț[cg]l', true, true, false ],
  [ '1234567', false, false, true ],
];
foreach ($data as list($query, $hasDiacritics, $hasRegexp, $isAllDigits)) {
  assertEquals($hasDiacritics, Str::hasDiacritics($query));
  assertEquals($hasRegexp, Str::hasRegexp($query));
  assertEquals($isAllDigits, Str::isAllDigits($query));
}

assertTransform('Str::xmlize', [], [
  '\\%\\~\\$' => '&#x5c;&#x25;&#x5c;&#x7e;&#x5c;&#x24;',
  'A<B>C&D' => 'A&lt;B&gt;C&amp;D',
]);

$t = FlexStr::extractTransforms('arde', 'arzând', 0);
assertEquals(4, count($t));
assertEquals('d', $t[0]->transfFrom);
assertEquals('z', $t[0]->transfTo);
assertEquals('e', $t[1]->transfFrom);
assertEquals('', $t[1]->transfTo);
assertEquals('', $t[2]->transfFrom);
assertEquals('ând', $t[2]->transfTo);
assertEquals(ModelDescription::UNKNOWN_ACCENT_SHIFT, $t[3]);

$t = FlexStr::extractTransforms('frumos', 'frumoasă', 0);
assertEquals(3, count($t));
assertEquals('o', $t[0]->transfFrom);
assertEquals('oa', $t[0]->transfTo);
assertEquals('', $t[1]->transfFrom);
assertEquals('ă', $t[1]->transfTo);
assertEquals(ModelDescription::UNKNOWN_ACCENT_SHIFT, $t[2]);

$t = FlexStr::extractTransforms('fi', 'sunt', 0);
assertEquals(2, count($t));
assertEquals('fi', $t[0]->transfFrom);
assertEquals('sunt', $t[0]->transfTo);
assertEquals(ModelDescription::UNKNOWN_ACCENT_SHIFT, $t[1]);

$t = FlexStr::extractTransforms('abil', 'abilul', 0);
assertEquals(2, count($t));
assertEquals('', $t[0]->transfFrom);
assertEquals('ul', $t[0]->transfTo);
assertEquals(ModelDescription::UNKNOWN_ACCENT_SHIFT, $t[1]);

$t = FlexStr::extractTransforms('alamă', 'alămuri', 0);
assertEquals(4, count($t));
assertEquals('a', $t[0]->transfFrom);
assertEquals('ă', $t[0]->transfTo);
assertEquals('ă', $t[1]->transfFrom);
assertEquals('', $t[1]->transfTo);
assertEquals('', $t[2]->transfFrom);
assertEquals('uri', $t[2]->transfTo);
assertEquals(ModelDescription::UNKNOWN_ACCENT_SHIFT, $t[3]);

$t = FlexStr::extractTransforms('sămânță', 'semințe', 0);
assertEquals(4, count($t));
assertEquals('ă', $t[0]->transfFrom);
assertEquals('e', $t[0]->transfTo);
assertEquals('â', $t[1]->transfFrom);
assertEquals('i', $t[1]->transfTo);
assertEquals('ă', $t[2]->transfFrom);
assertEquals('e', $t[2]->transfTo);
assertEquals(ModelDescription::UNKNOWN_ACCENT_SHIFT, $t[3]);

$t = FlexStr::extractTransforms('deșert', 'deșartelor', 0);
assertEquals(3, count($t));
assertEquals('e', $t[0]->transfFrom);
assertEquals('a', $t[0]->transfTo);
assertEquals('', $t[1]->transfFrom);
assertEquals('elor', $t[1]->transfTo);
assertEquals(ModelDescription::UNKNOWN_ACCENT_SHIFT, $t[2]);

$t = FlexStr::extractTransforms('cumătră', 'cumetrelor', 0);
assertEquals(4, count($t));
assertEquals('ă', $t[0]->transfFrom);
assertEquals('e', $t[0]->transfTo);
assertEquals('ă', $t[1]->transfFrom);
assertEquals('e', $t[1]->transfTo);
assertEquals('', $t[2]->transfFrom);
assertEquals('lor', $t[2]->transfTo);
assertEquals(ModelDescription::UNKNOWN_ACCENT_SHIFT, $t[3]);

$t = FlexStr::extractTransforms('crăpa', 'crapă', 0);
assertEquals(3, count($t));
assertEquals('ă', $t[0]->transfFrom);
assertEquals('a', $t[0]->transfTo);
assertEquals('a', $t[1]->transfFrom);
assertEquals('ă', $t[1]->transfTo);
assertEquals(ModelDescription::UNKNOWN_ACCENT_SHIFT, $t[2]);

$t = FlexStr::extractTransforms('stradă', 'străzi', 0);
assertEquals(4, count($t));
assertEquals('a', $t[0]->transfFrom);
assertEquals('ă', $t[0]->transfTo);
assertEquals('d', $t[1]->transfFrom);
assertEquals('z', $t[1]->transfTo);
assertEquals('ă', $t[2]->transfFrom);
assertEquals('i', $t[2]->transfTo);
assertEquals(ModelDescription::UNKNOWN_ACCENT_SHIFT, $t[3]);

$t = FlexStr::extractTransforms('frumos', 'frumoasă', 0);
assertEquals(3, count($t));
assertEquals('o', $t[0]->transfFrom);
assertEquals('oa', $t[0]->transfTo);
assertEquals('', $t[1]->transfFrom);
assertEquals('ă', $t[1]->transfTo);
assertEquals(ModelDescription::UNKNOWN_ACCENT_SHIFT, $t[2]);

$t = FlexStr::extractTransforms('groapă', 'gropilor', 0);
assertEquals(4, count($t));
assertEquals('a', $t[0]->transfFrom);
assertEquals('', $t[0]->transfTo);
assertEquals('ă', $t[1]->transfFrom);
assertEquals('i', $t[1]->transfTo);
assertEquals('', $t[2]->transfFrom);
assertEquals('lor', $t[2]->transfTo);
assertEquals(ModelDescription::UNKNOWN_ACCENT_SHIFT, $t[3]);

$t = FlexStr::extractTransforms('căpăta', 'capăt', 0);
assertEquals(4, count($t));
assertEquals('ă', $t[0]->transfFrom);
assertEquals('a', $t[0]->transfTo);
assertEquals('ă', $t[1]->transfFrom);
assertEquals('ă', $t[1]->transfTo);
assertEquals('a', $t[2]->transfFrom);
assertEquals('', $t[2]->transfTo);
assertEquals(ModelDescription::UNKNOWN_ACCENT_SHIFT, $t[3]);

$t = FlexStr::extractTransforms('răscrăcăra', 'răscracăr', 0);
assertEquals(4, count($t));
assertEquals('ă', $t[0]->transfFrom);
assertEquals('a', $t[0]->transfTo);
assertEquals('ă', $t[1]->transfFrom);
assertEquals('ă', $t[1]->transfTo);
assertEquals('a', $t[2]->transfFrom);
assertEquals('', $t[2]->transfTo);
assertEquals(ModelDescription::UNKNOWN_ACCENT_SHIFT, $t[3]);

$t = FlexStr::extractTransforms('răscrăcăra', 'rascrăcăr', 0);
assertEquals(5, count($t));
assertEquals('ă', $t[0]->transfFrom);
assertEquals('a', $t[0]->transfTo);
assertEquals('ă', $t[1]->transfFrom);
assertEquals('ă', $t[1]->transfTo);
assertEquals('ă', $t[2]->transfFrom);
assertEquals('ă', $t[2]->transfTo);
assertEquals('a', $t[3]->transfFrom);
assertEquals('', $t[3]->transfTo);
assertEquals(ModelDescription::UNKNOWN_ACCENT_SHIFT, $t[4]);

$t = FlexStr::extractTransforms('foo', 'foo', 0);
assertEquals(2, count($t));
assertEquals('', $t[0]->transfFrom);
assertEquals('', $t[0]->transfTo);
assertEquals(ModelDescription::UNKNOWN_ACCENT_SHIFT, $t[1]);

// Try some accents
$t = FlexStr::extractTransforms("căpăt'a", "c'apăt", 0);
assertEquals(5, count($t));
assertEquals('ă', $t[0]->transfFrom);
assertEquals('a', $t[0]->transfTo);
assertEquals('ă', $t[1]->transfFrom);
assertEquals('ă', $t[1]->transfTo);
assertEquals('a', $t[2]->transfFrom);
assertEquals('', $t[2]->transfTo);
assertEquals('a', $t[3]);
assertEquals(2, $t[4]);

$t = FlexStr::extractTransforms("c'ăpăta", "cap'ăt", 0);
assertEquals(5, count($t));
assertEquals('ă', $t[0]->transfFrom);
assertEquals('a', $t[0]->transfTo);
assertEquals('ă', $t[1]->transfFrom);
assertEquals('ă', $t[1]->transfTo);
assertEquals('a', $t[2]->transfFrom);
assertEquals('', $t[2]->transfTo);
assertEquals('ă', $t[3]);
assertEquals(1, $t[4]);

$t = FlexStr::extractTransforms("n'ailon", "nailo'ane", 0);
assertEquals(4, count($t));
assertEquals('o', $t[0]->transfFrom);
assertEquals('oa', $t[0]->transfTo);
assertEquals('', $t[1]->transfFrom);
assertEquals('e', $t[1]->transfTo);
assertEquals('a', $t[2]);
assertEquals(2, $t[3]);

$t = FlexStr::extractTransforms("n'ailon", "n'ailonului", 0);
assertEquals(2, count($t));
assertEquals('', $t[0]->transfFrom);
assertEquals('ului', $t[0]->transfTo);
assertEquals(ModelDescription::NO_ACCENT_SHIFT, $t[1]);

assertTransform('FlexStr::countVowels', [], [
  'abc' => 1,
  'abcde' => 2,
  'aeiouăâî' => 8,
]);

assertEquals("cas'ă", FlexStr::placeAccent("casă", 1, ''));
assertEquals("c'asă", FlexStr::placeAccent("casă", 2, ''));
assertEquals("casă", FlexStr::placeAccent("casă", 3, ''));
assertEquals("ap'ă", FlexStr::placeAccent("apă", 1, ''));
assertEquals("'apă", FlexStr::placeAccent("apă", 2, ''));
assertEquals("apă", FlexStr::placeAccent("apă", 3, ''));
assertEquals("'a", FlexStr::placeAccent("a", 1, ''));
assertEquals("a", FlexStr::placeAccent("a", 2, ''));

assertEquals("șa'ibă", FlexStr::placeAccent("șaibă", 2, ''));
assertEquals("ș'aibă", FlexStr::placeAccent("șaibă", 3, ''));
assertEquals("ș'aibă", FlexStr::placeAccent("șaibă", 2, 'a'));
assertEquals("ș'aibă", FlexStr::placeAccent("șaibă", 3, 'a'));
assertEquals("șa'ibă", FlexStr::placeAccent("șaibă", 2, 'i'));
assertEquals("șa'ibă", FlexStr::placeAccent("șaibă", 3, 'i'));

assertEquals("unfuckingbelievable", FlexStr::insert("unbelievable", "fucking", 2));
assertEquals("abcdef", FlexStr::insert("cdef", "ab", 0));
assertEquals("abcdef", FlexStr::insert("abcd", "ef", 4));

assertEquals('mamă      ', Str::padRight('mamă', 10));
assertEquals('mama      ', Str::padRight('mama', 10));
assertEquals('ăâîșț   ', Str::padRight('ăâîșț', 8));
assertEquals('ăâîșț', Str::padRight('ăâîșț', 5));
assertEquals('ăâîșț', Str::padRight('ăâîșț', 3));

assertEquals(['c', 'a', 'r'], Str::unicodeExplode('car'));
assertEquals(['ă', 'a', 'â', 'ș', 'ț'], Str::unicodeExplode('ăaâșț'));

$orth = [
  'pîine' => 'pâine',
  'pîine mîine' => 'pâine mâine',
  'reînnoi pîine' => 'reînnoi pâine',
  'pîine reînnoi' => 'pâine reînnoi',
  'anexînd' => 'anexând', // ex is not a prefix here
  'înger' => 'înger',
  'rîu înger' => 'râu înger',
  'împlîntînd în' => 'împlântând în',
  'sîntem astăzi sînt mîine' => 'suntem astăzi sunt mâine',
  'sîntul așteaptă' => 'sântul așteaptă',
  'țîfnos țîrîi' => 'țâfnos țârâi', // UTF8 context
  'sînteți' => 'sunteți',
];
foreach ($orth as $old => $new) {
  assertEquals($new, Str::convertOrthography($old));
  assertEquals(mb_strtoupper($new), Str::convertOrthography(mb_strtoupper($old)));
}

assertEquals([1, 5, 10],
             Util::intersectArrays([1, 3, 5, 7, 9, 10],
                                   [1, 2, 4, 5, 6, 8, 10]));
assertEquals([],
             Util::intersectArrays([2, 4, 6, 8],
                                   [1, 3, 5, 7]));

assert(!Lock::release('test'));
assert(!Lock::exists('test'));
assert(Lock::acquire('test'));
assert(Lock::exists('test'));
assert(!Lock::acquire('test'));
assert(Lock::release('test'));
assert(!Lock::exists('test'));
assert(!Lock::release('test'));

assertEquals(0, Util::findSnippet([[1, 2, 10]]));
assertEquals(1, Util::findSnippet([[1, 2, 10],
                                   [5, 6, 9]]));
assertEquals(2, Util::findSnippet([[1, 2, 10],
                                   [5, 6, 8]]));
assertEquals(4, Util::findSnippet([[1, 2, 10],
                                   [6, 20],
                                   [8, 15]]));

/************************* Test for DiffUtil.php *************************/

// diff actions are used in definitionEdit.php. The user can click on the highlight
// differences (red = deleted, green = inserted) to remove them from the definition
// or to insert them in the similar definition from the previous edition
function assertDiffAction($from, $to, $rank, $action, $expectedFrom, $expectedTo) {
  $d1 = Model::factory('Definition')->create();
  $d2 = Model::factory('Definition')->create();
  $d1->internalRep = $from;
  $d2->internalRep = $to;

  DiffUtil::diffAction($d1, $d2, $rank, $action);

  assertEquals($expectedFrom, $d1->internalRep);
  assertEquals($expectedTo, $d2->internalRep);
}

$a = 'abc def ghi jkl modified text mno pqr stu vwx deleted text yz';
$b = 'abc def inserted text ghi jkl other words mno pqr stu vwx yz';
$granularity = DiffUtil::FINE_DIFF_GRANULARITY[DiffUtil::GRANULARITY_WORD];
$opcodes = FineDiff::getDiffOpcodes($a, $b, $granularity);
assertEquals($opcodes, 'c8i14:inserted text c8d14i12:other words c16d13c2');

assertDiffAction($a, $b, 0, 0,
                 $a,
                 'abc def ghi jkl other words mno pqr stu vwx yz');
assertDiffAction($a, $b, 0, 1,
                 'abc def inserted text ghi jkl modified text mno pqr stu vwx deleted text yz',
                 $b);
assertDiffAction($a, $b, 1, 0,
                 'abc def ghi jkl mno pqr stu vwx deleted text yz',
                 $b);
assertDiffAction($a, $b, 1, 1,
                 $a,
                 'abc def inserted text ghi jkl modified text other words mno pqr stu vwx yz');
assertDiffAction($a, $b, 2, 0,
                 $a,
                 'abc def inserted text ghi jkl mno pqr stu vwx yz');
assertDiffAction($a, $b, 2, 1,
                 'abc def ghi jkl modified text other words mno pqr stu vwx deleted text yz',
                 $b);
assertDiffAction($a, $b, 3, 0,
                 'abc def ghi jkl modified text mno pqr stu vwx yz',
                 $b);
assertDiffAction($a, $b, 3, 1,
                 $a,
                 'abc def inserted text ghi jkl other words mno pqr stu vwx deleted text yz');
