<?php

include_once "../phplib/util.php";
assert_options(ASSERT_BAIL, 1);

function assertEquals($expected, $actual) {
  if ($expected != $actual) {
    print "Assertion failed.\n";
    print "  expected [$expected]\n";
    print "  actual   [$actual]\n";
    debug_print_backtrace();
    exit;
  }
}

function assertEqualArrays($expected, $actual) {
  assertEquals(count($expected), count($actual));
  for ($i = 0; $i < count($expected); $i++) {
    $elemE = each($expected);
    $elemA = each($actual);
    assertEquals($elemE[0], $elemA[0]);
    assertEquals($elemE[1], $elemA[1]);
  }
}

/********************* Tests for intArray.php  ************************/
$s = int_create(1000000);
assertEquals(0, int_get($s, 1234));
int_put($s, 1234, 12345678);
assertEquals(12345678, int_get($s, 1234));
assertEquals(1000000, int_size($s));

/********************* Tests for textProcessing.php ************************/

// Check that we've got the shorthand->Unicode mappings right
assertEquals(text_shorthandToUnicode("~a"), 'ă');
assertEquals(text_shorthandToUnicode("~a^a^i,s,t"), 'ăâîșț');
assertEquals(text_shorthandToUnicode("'^a'^A^'a^'A"), 'ấẤấẤ');
assertEquals(text_shorthandToUnicode("'~a'~A~'a~'A"), 'ắẮắẮ');
assertEquals(text_shorthandToUnicode("~a~A^a^A'a'A"), 'ăĂâÂáÁ');
assertEquals(text_shorthandToUnicode("`a`A:a:A"), 'àÀäÄ');
assertEquals(text_shorthandToUnicode(",c,C'c'C~c~C"), 'çÇćĆčČ');
assertEquals(text_shorthandToUnicode("'e'E`e`E^e^E"), 'éÉèÈêÊ');
assertEquals(text_shorthandToUnicode(":e:E~e~E~g~G"), 'ëËĕĔğĞ');
assertEquals(text_shorthandToUnicode("'^i'^I^'i^'I"), 'î́Î́î́Î́');
assertEquals(text_shorthandToUnicode("'i'I`i`I^i^I"), 'íÍìÌîÎ');
assertEquals(text_shorthandToUnicode(":i:I~i~I~n~N"), 'ïÏĩĨñÑ');
assertEquals(text_shorthandToUnicode("'o'O`o`O^o^O"), 'óÓòÒôÔ');
assertEquals(text_shorthandToUnicode(":o:O~o~O~r~R"), 'öÖõÕřŘ');
assertEquals(text_shorthandToUnicode("~s~S,s,S,t,T"), 'šŠșȘțȚ');
assertEquals(text_shorthandToUnicode("'u'U`u`U^u^U"), 'úÚùÙûÛ');
assertEquals(text_shorthandToUnicode(":u:U~u~U"), 'üÜŭŬ');
assertEquals(text_shorthandToUnicode("'y'Y:y:Y~z~Z"), 'ýÝÿŸžŽ');

assertEquals('acegyzACEGYZ', text_unicodeToLatin("ắčèğýžẮČÈĞÝŽ"));

assertEquals('mama', text_unicodeToLower('mama'));
assertEquals('mama', text_unicodeToLower('maMa'));
assertEquals('mama', text_unicodeToLower('MAmA'));
assertEquals('mamă', text_unicodeToLower('MAmă'));
assertEquals('mamă', text_unicodeToLower('MAmĂ'));
assertEquals('abcúùû', text_unicodeToLower('ABCÚÙÛ'));
assertEquals('ÿ', text_unicodeToLower('Ÿ'));

assertEquals('MAMA', text_unicodeToUpper('MAMA'));
assertEquals('MAMA', text_unicodeToUpper('MAmA'));
assertEquals('MAMA', text_unicodeToUpper('MAmA'));
assertEquals('MAMĂ', text_unicodeToUpper('MamĂ'));
assertEquals('MAMĂ', text_unicodeToUpper('maMă'));
assertEquals('ABCÚÙÛ', text_unicodeToUpper('abcúùû'));
assertEquals('Ÿ', text_unicodeToUpper('ÿ'));

// Check that we're using the right encoding
assertEquals(mb_strlen('íÍìÌîÎ'), 6);
assertEquals(mb_substr('íÍìÌîÎ', 3, 2), 'Ìî');

// Test string reversal
assertEquals('cba', text_reverse('abc'));
assertEquals('țșîâă', text_reverse('ăâîșț'));
assertEquals('ȚȘÎÂĂ', text_reverse('ĂÂÎȘȚ'));

// Check suffix removals
assertEquals(_text_removeKnownSuffixes(''), '');
assertEquals(_text_removeKnownSuffixes('mama'), 'mama');
assertEquals(_text_removeKnownSuffixes('farmaciei'), 'farmacie');
assertEquals(_text_removeKnownSuffixes('dealului'), 'deal');
assertEquals(_text_removeKnownSuffixes('dealul'), 'deal');
assertEquals(_text_removeKnownSuffixes('dealuri'), 'deal');
assertEquals(_text_removeKnownSuffixes('dealurilor'), 'deal');
assertEquals(_text_removeKnownSuffixes('copacilor'), 'copac');
assertEquals(_text_removeKnownSuffixes('bogată'), 'bogat');
assertEquals(_text_removeKnownSuffixes('bogate'), 'bogat');

assertEquals(_text_getLastWord(''), '');
assertEquals(_text_getLastWord('foo'), 'foo');
assertEquals(_text_getLastWord('foo bar'), 'bar');
assertEquals(_text_getLastWord('foo bar (@1@)'), 'bar');
assertEquals(_text_getLastWord('foo bar õÕ (@1@)'), 'õÕ');

assertEquals(_text_internalizeAllReferences('|foo|bar|'), '|foo|bar|');
assertEquals(_text_internalizeAllReferences('|foo moo|bar|'), '|foo moo|bar|');
assertEquals(_text_internalizeAllReferences('|foo moo (@1@)|bar|'),
	     '|foo moo (@1@)|bar|');
assertEquals(_text_internalizeAllReferences('|foo||'), '|foo|foo|');
assertEquals(_text_internalizeAllReferences('|foo moo||'), '|foo moo|moo|');
assertEquals(_text_internalizeAllReferences('|foo moo (@1@)||'),
	     '|foo moo (@1@)|moo|');
assertEquals(_text_internalizeAllReferences('|dealului|-|'), '|dealului|deal|');
assertEquals(_text_internalizeAllReferences('|vax albina|-|'),
	     '|vax albina|vax albina|');
assertEquals(_text_internalizeAllReferences('text 1 |foo|| text 2 |dealul|-| text 3'),
	     'text 1 |foo|foo| text 2 |dealul|deal| text 3');

assertEquals('<a class="ref" href="/definitie/y">x</a>', _text_convertReferencesToHtml('|x|y|'));
assertEquals('<a class="ref" href="/definitie/î">ă</a>', _text_convertReferencesToHtml('|ă|î|'));
assertEquals('<a class="ref" href="/definitie/ab cd ef">ab cd ef</a>', _text_convertReferencesToHtml('|ab cd ef|ab cd ef|'));
assertEquals('<a class="ref" href="/definitie/ab cd ef (@1@)">ab cd ef (@1@)</a>', _text_convertReferencesToHtml('|ab cd ef (@1@)|ab cd ef (@1@)|'));
assertEquals('<a class="ref" href="/definitie/ab cd õÕ (@1@)">ab cd õÕ (@1@)</a>', _text_convertReferencesToHtml('|ab cd õÕ (@1@)|ab cd õÕ (@1@)|'));
assertEquals('<a class="ref" href="/definitie/y">x</a> foobar <a class="ref" href="/definitie/t">z</a>', _text_convertReferencesToHtml('|x|y| foobar |z|t|'));

assertEquals(_text_insertSuperscripts("copil^{+123}. copil_{-123}----"),
	     "copil<sup>+123</sup>. copil<sub>-123</sub>----");
assertEquals(_text_insertSuperscripts("copil^i^2"), "copil^i<sup>2</sup>");

assertEquals('xxx &#x25ca; &#x2666; < &#x2013; > yyy',
             _text_minimalInternalToHtml('xxx * ** < - > yyy'));

assertEquals('„abc”„”',
	     _text_internalToHtml('"abc"""', FALSE));
assertEquals('<b><i>bold and italic</i> bold only</b> regular.',
	     _text_internalToHtml('@$bold and italic$ bold only@ regular.',
				  FALSE));
assertEquals('<@bold, but inside tag@>',
	     _text_internalToHtml('<@bold, but inside tag@>', FALSE));
assertEquals('foo &lt; <i>bar</i>',
	     _text_internalToHtml('foo &lt; $bar$', FALSE));
assertEquals('<span class="spaced">cățel</span>', _text_internalToHtml('%cățel%', FALSE));
assertEquals('foo <span class="spaced">bar &amp;</span> bib', _text_internalToHtml('foo %bar &amp;% bib', FALSE));
assertEquals('<span class="spaced">unu, doi</span>', _text_internalToHtml('%unu, doi%', FALSE));
assertEquals('<span class="spaced">ab <b>cd</b></span>', _text_internalToHtml('%ab @cd@%', FALSE));
assertEquals("okely\ndokely",
	     _text_internalToHtml("okely\ndokely", FALSE));
assertEquals("okely<br/>\ndokely",
	     _text_internalToHtml("okely\ndokely", TRUE));

assertEquals("@FILLER@ #adj. dem.# (antepus), art.", _text_markAbbreviations("@FILLER@ adj. dem. (antepus), art.", 1));
assertEquals("@FILLER@ #adj. dem.# (antepus), art.", _text_markAbbreviations("@FILLER@ adj. dem. (antepus), art.", 1));
assertEquals("@FILLER@ #loc. adv. și adj.# @MORE FILLER@", _text_markAbbreviations("@FILLER@ loc. adv. și adj. @MORE FILLER@", 1));
assertEquals("@FILLER@ #arg.# șarg. catarg. ăarg. țarg. @FILLER@", _text_markAbbreviations("@FILLER@ arg. șarg. catarg. ăarg. țarg. @FILLER@", 1));
assertEquals("@FILLER@ #et. nec.#", _text_markAbbreviations("@FILLER@ et. nec.", 1));
assertEquals("@FILLER@ #art. hot.# @FILLER@", _text_markAbbreviations("@FILLER@ art.hot. @FILLER@", 1));
assertEquals("@FILLER@ #art. hot.# @FILLER@", _text_markAbbreviations("@FILLER@ #art. hot.# @FILLER@", 1));
assertEquals("FOO ornit. BAR", _text_markAbbreviations("FOO ornit. BAR", 99)); // Inexistent source
assertEquals("FOO BAR", _text_markAbbreviations("FOO BAR", 1)); // No abbreviations
assertEquals("FOO dat. BAR", _text_markAbbreviations("FOO dat. BAR", 1)); // Ambiguous abbreviations
// A more complex example which also reports ambiguous matches
$ambiguousMatches = array();
assertEquals("FOO dat. #arh.# #loc. adv.# BAR", _text_markAbbreviations("FOO dat. arh. loc. adv. BAR", 1, $ambiguousMatches));
assertEquals(1, count($ambiguousMatches));
assertEqualArrays(array('abbrev' => 'dat.', 'position' => 4, 'length' => 4), $ambiguousMatches[0]);

assertEquals("FOO <abbr class=\"abbrev\" title=\"farmacie; farmacologie\">farm.</abbr> BAR", text_htmlize("FOO #farm.# BAR", 1)); /** Semicolon in abbreviation **/
assertEquals("FOO <abbr class=\"abbrev\" title=\"substantiv masculin\">s. m.</abbr> BAR", text_htmlize("FOO #s. m.# BAR", 1));
$errors = array();
assertEquals("FOO <abbr class=\"abbrev\" title=\"abreviere necunoscută\">brrb. ghhg.</abbr> BAR", text_htmlize("FOO #brrb. ghhg.# BAR", 1, $errors));
assertEqualArrays(array(0 => 'Abreviere necunoscută: «brrb. ghhg.». Verificați că după fiecare punct există un spațiu.'), $errors);

$internalRep = '@MÁRE^2,@ $mări,$ #s. f.# Nume generic dat vastelor întinderi de apă stătătoare, adânci și sărate, de pe suprafața |Pământului|Pământ|, care de obicei sunt unite cu |oceanul|ocean| printr-o |strâmtoare|strâmtoare|; parte a oceanului de lângă |țărm|țărm|; $#p. ext.#$ ocean. * #Expr.# $Marea cu sarea$ = mult, totul; imposibilul. $A vântura mări și țări$ = a călători mult. $A încerca marea cu degetul$ = a face o încercare, chiar dacă șansele de reușită sunt minime. $Peste (nouă) mări și (nouă) țări$ = foarte departe. ** #Fig.# Suprafață vastă; întindere mare; imensitate. ** #Fig.# Mulțime (nesfârșită), cantitate foarte mare. - Lat. @mare, -is.@';
assertEquals($internalRep,
             text_internalizeDefinition('@M\'ARE^2@, $m~ari$, s. f. Nume generic dat vastelor ^intinderi de ap~a st~at~atoare, ad^anci ,si s~arate, de pe suprafa,ta |P~am^antului|-|, care de obicei sunt unite cu |oceanul|-| printr-o |str^amtoare||; parte a oceanului de l^ang~a |,t~arm||; $p.ext.$ ocean. * Expr. $Marea cu sarea$ = mult, totul; imposibilul. $A v^antura m~ari ,si ,t~ari$ = a c~al~atori mult. $A ^incerca marea cu degetul$ = a face o ^incercare, chiar dac~a ,sansele de reu,sit~a sunt minime. $Peste (nou~a) m~ari ,si (nou~a) ,t~ari$ = foarte departe. ** Fig. Suprafa,t~a vast~a; ^intindere mare; imensitate. ** Fig. Mul,time (nesf^ar,sit~a), cantitate foarte mare. - Lat. @mare, -is@.', 1));
assertEquals('<b>MÁRE<sup>2</sup>,</b> <i>mări,</i> <abbr class="abbrev" title="substantiv feminin">s. f.</abbr> Nume generic dat vastelor întinderi de apă stătătoare, adânci și sărate, de pe suprafața <a class="ref" href="/definitie/Pământ">Pământului</a>, care de obicei sunt unite cu <a class="ref" href="/definitie/ocean">oceanul</a> printr-o <a class="ref" href="/definitie/strâmtoare">strâmtoare</a>; parte a oceanului de lângă <a class="ref" href="/definitie/țărm">țărm</a>; <i><abbr class="abbrev" title="prin extensiune">p. ext.</abbr></i> ocean. &#x25ca; <abbr class="abbrev" title="expresie">Expr.</abbr> <i>Marea cu sarea</i> = mult, totul; imposibilul. <i>A vântura mări și țări</i> = a călători mult. <i>A încerca marea cu degetul</i> = a face o încercare, chiar dacă șansele de reușită sunt minime. <i>Peste (nouă) mări și (nouă) țări</i> = foarte departe. &#x2666; <abbr class="abbrev" title="figurat">Fig.</abbr> Suprafață vastă; întindere mare; imensitate. &#x2666; <abbr class="abbrev" title="figurat">Fig.</abbr> Mulțime (nesfârșită), cantitate foarte mare. &#x2013; Lat. <b>mare, -is.</b>',
             text_htmlize($internalRep, 1));
assertEquals($internalRep, text_internalizeDefinition($internalRep, 1));

assertEquals('@MÁRE^2,@ $mări,$ s.f.', _text_migrateFormatChars('@MÁRE^2@, $mări$, s.f.'));
assertEquals('@$%spaced% text$@', _text_migrateFormatChars('@$ % spaced % text $@'));
assertEquals('40\% dolomite', _text_migrateFormatChars('40\% dolomite'));
assertEquals('40 %dolomite%', _text_migrateFormatChars('40% dolomite%'));

assertEquals('cățel', text_internalizeWordName("C~A,t'EL"));
assertEquals('ă', text_internalizeWordName("~~A~~!@#$%^&*()123456790"));

assertEquals('casă', text_removeAccents('cásă'));

assertEquals('mama', text_cleanupQuery("'mama'"));
assertEquals('mama', text_cleanupQuery('"mama"'));
assertEquals('aăbcdef', text_cleanupQuery("aăbc<mamă foo bar>def"));
assertEquals('AĂBCDEF', text_cleanupQuery("AĂBC<MAMĂ FOO BAR>DEF"));
assertEquals('aăbcdef', text_cleanupQuery("a~abc<mam~a foo bar>def"));
assertEquals('aĂBcdef', text_cleanupQuery("a~ABc<mam~a foo bar>def"));
assertEquals('1234', text_cleanupQuery('12&qweasd;34'));

assert(text_hasDiacritics('mamă'));
assert(!text_hasDiacritics('mama'));

$def = new Definition();
$def->sourceId = 1;
$def->internalRep = 'abcd';
assertEquals('abcd', text_extractLexicon($def));
$def->internalRep = 'wxyz';
assertEquals('wxyz', text_extractLexicon($def));
$def->internalRep = 'mamă';
assertEquals('mamă', text_extractLexicon($def));

assert(text_hasRegexp('asd[0-9]'));
assert(!text_hasRegexp('ăâîșț'));
assert(text_hasRegexp('cop?l'));

assertEquals("like 'cop%l'", text_dexRegexpToMysqlRegexp('cop*l'));
assertEquals("like 'cop_l'", text_dexRegexpToMysqlRegexp('cop?l'));
assertEquals("rlike '^(cop[a-z]l)$'",
	     text_dexRegexpToMysqlRegexp('cop[a-z]l'));
assertEquals("rlike '^(cop[^a-z]l)$'",
	     text_dexRegexpToMysqlRegexp('cop[^a-z]l'));
assertEquals("rlike '^(cop[â-z]l)$'",
	     text_dexRegexpToMysqlRegexp('cop[â-z]l'));
assertEquals("rlike '^(cop[â-z]l.*)$'",
	     text_dexRegexpToMysqlRegexp('cop[â-z]l*'));

assertEqualArrays(array(0, 0, 0), text_analyzeQuery('mama'));
assertEqualArrays(array(1, 0, 0), text_analyzeQuery('mamă'));
assertEqualArrays(array(0, 1, 0), text_analyzeQuery('cop?l'));
assertEqualArrays(array(0, 1, 0), text_analyzeQuery('cop[c-g]l'));
assertEqualArrays(array(1, 1, 0), text_analyzeQuery('căț[c-g]l'));
assertEqualArrays(array(0, 0, 1), text_analyzeQuery('1234567'));

assertEquals('&#x25;&#x7e;&#x24;&#x40;&#x27;',
             text_xmlizeRequired('\\%\\~\\$\\@\\\''));
assertEquals('&lt;&gt;&amp;',
             text_xmlizeRequired('<>&'));

$t = text_extractTransforms('arde', 'arzând', 0);
assertEquals(4, count($t));
assertEquals('d', $t[0]->transfFrom);
assertEquals('z', $t[0]->transfTo);
assertEquals('e', $t[1]->transfFrom);
assertEquals('', $t[1]->transfTo);
assertEquals('', $t[2]->transfFrom);
assertEquals('ând', $t[2]->transfTo);
assertEquals(UNKNOWN_ACCENT_SHIFT, $t[3]);

$t = text_extractTransforms('frumos', 'frumoasă', 0);
assertEquals(3, count($t));
assertEquals('o', $t[0]->transfFrom);
assertEquals('oa', $t[0]->transfTo);
assertEquals('', $t[1]->transfFrom);
assertEquals('ă', $t[1]->transfTo);
assertEquals(UNKNOWN_ACCENT_SHIFT, $t[2]);

$t = text_extractTransforms('fi', 'sunt', 0);
assertEquals(2, count($t));
assertEquals('fi', $t[0]->transfFrom);
assertEquals('sunt', $t[0]->transfTo);
assertEquals(UNKNOWN_ACCENT_SHIFT, $t[1]);

$t = text_extractTransforms('abil', 'abilul', 0);
assertEquals(2, count($t));
assertEquals('', $t[0]->transfFrom);
assertEquals('ul', $t[0]->transfTo);
assertEquals(UNKNOWN_ACCENT_SHIFT, $t[1]);

$t = text_extractTransforms('alamă', 'alămuri', 0);
assertEquals(4, count($t));
assertEquals('a', $t[0]->transfFrom);
assertEquals('ă', $t[0]->transfTo);
assertEquals('ă', $t[1]->transfFrom);
assertEquals('', $t[1]->transfTo);
assertEquals('', $t[2]->transfFrom);
assertEquals('uri', $t[2]->transfTo);
assertEquals(UNKNOWN_ACCENT_SHIFT, $t[3]);

$t = text_extractTransforms('sămânță', 'semințe', 0);
assertEquals(4, count($t));
assertEquals('ă', $t[0]->transfFrom);
assertEquals('e', $t[0]->transfTo);
assertEquals('â', $t[1]->transfFrom);
assertEquals('i', $t[1]->transfTo);
assertEquals('ă', $t[2]->transfFrom);
assertEquals('e', $t[2]->transfTo);
assertEquals(UNKNOWN_ACCENT_SHIFT, $t[3]);

$t = text_extractTransforms('deșert', 'deșartelor', 0);
assertEquals(3, count($t));
assertEquals('e', $t[0]->transfFrom);
assertEquals('a', $t[0]->transfTo);
assertEquals('', $t[1]->transfFrom);
assertEquals('elor', $t[1]->transfTo);
assertEquals(UNKNOWN_ACCENT_SHIFT, $t[2]);

$t = text_extractTransforms('cumătră', 'cumetrelor', 0);
assertEquals(4, count($t));
assertEquals('ă', $t[0]->transfFrom);
assertEquals('e', $t[0]->transfTo);
assertEquals('ă', $t[1]->transfFrom);
assertEquals('e', $t[1]->transfTo);
assertEquals('', $t[2]->transfFrom);
assertEquals('lor', $t[2]->transfTo);
assertEquals(UNKNOWN_ACCENT_SHIFT, $t[3]);

$t = text_extractTransforms('crăpa', 'crapă', 0);
assertEquals(3, count($t));
assertEquals('ă', $t[0]->transfFrom);
assertEquals('a', $t[0]->transfTo);
assertEquals('a', $t[1]->transfFrom);
assertEquals('ă', $t[1]->transfTo);
assertEquals(UNKNOWN_ACCENT_SHIFT, $t[2]);

$t = text_extractTransforms('stradă', 'străzi', 0);
assertEquals(4, count($t));
assertEquals('a', $t[0]->transfFrom);
assertEquals('ă', $t[0]->transfTo);
assertEquals('d', $t[1]->transfFrom);
assertEquals('z', $t[1]->transfTo);
assertEquals('ă', $t[2]->transfFrom);
assertEquals('i', $t[2]->transfTo);
assertEquals(UNKNOWN_ACCENT_SHIFT, $t[3]);

$t = text_extractTransforms('frumos', 'frumoasă', 0);
assertEquals(3, count($t));
assertEquals('o', $t[0]->transfFrom);
assertEquals('oa', $t[0]->transfTo);
assertEquals('', $t[1]->transfFrom);
assertEquals('ă', $t[1]->transfTo);
assertEquals(UNKNOWN_ACCENT_SHIFT, $t[2]);

$t = text_extractTransforms('groapă', 'gropilor', 0);
assertEquals(4, count($t));
assertEquals('a', $t[0]->transfFrom);
assertEquals('', $t[0]->transfTo);
assertEquals('ă', $t[1]->transfFrom);
assertEquals('i', $t[1]->transfTo);
assertEquals('', $t[2]->transfFrom);
assertEquals('lor', $t[2]->transfTo);
assertEquals(UNKNOWN_ACCENT_SHIFT, $t[3]);

$t = text_extractTransforms('căpăta', 'capăt', 0);
assertEquals(4, count($t));
assertEquals('ă', $t[0]->transfFrom);
assertEquals('a', $t[0]->transfTo);
assertEquals('ă', $t[1]->transfFrom);
assertEquals('ă', $t[1]->transfTo);
assertEquals('a', $t[2]->transfFrom);
assertEquals('', $t[2]->transfTo);
assertEquals(UNKNOWN_ACCENT_SHIFT, $t[3]);

$t = text_extractTransforms('răscrăcăra', 'răscracăr', 0);
assertEquals(4, count($t));
assertEquals('ă', $t[0]->transfFrom);
assertEquals('a', $t[0]->transfTo);
assertEquals('ă', $t[1]->transfFrom);
assertEquals('ă', $t[1]->transfTo);
assertEquals('a', $t[2]->transfFrom);
assertEquals('', $t[2]->transfTo);
assertEquals(UNKNOWN_ACCENT_SHIFT, $t[3]);

$t = text_extractTransforms('răscrăcăra', 'rascrăcăr', 0);
assertEquals(5, count($t));
assertEquals('ă', $t[0]->transfFrom);
assertEquals('a', $t[0]->transfTo);
assertEquals('ă', $t[1]->transfFrom);
assertEquals('ă', $t[1]->transfTo);
assertEquals('ă', $t[2]->transfFrom);
assertEquals('ă', $t[2]->transfTo);
assertEquals('a', $t[3]->transfFrom);
assertEquals('', $t[3]->transfTo);
assertEquals(UNKNOWN_ACCENT_SHIFT, $t[4]);

$t = text_extractTransforms('foo', 'foo', 0);
assertEquals(2, count($t));
assertEquals('', $t[0]->transfFrom);
assertEquals('', $t[0]->transfTo);
assertEquals(UNKNOWN_ACCENT_SHIFT, $t[1]);

// Try some accents
$t = text_extractTransforms("căpăt'a", "c'apăt", 0);
assertEquals(5, count($t));
assertEquals('ă', $t[0]->transfFrom);
assertEquals('a', $t[0]->transfTo);
assertEquals('ă', $t[1]->transfFrom);
assertEquals('ă', $t[1]->transfTo);
assertEquals('a', $t[2]->transfFrom);
assertEquals('', $t[2]->transfTo);
assertEquals('a', $t[3]);
assertEquals(2, $t[4]);

$t = text_extractTransforms("c'ăpăta", "cap'ăt", 0);
assertEquals(5, count($t));
assertEquals('ă', $t[0]->transfFrom);
assertEquals('a', $t[0]->transfTo);
assertEquals('ă', $t[1]->transfFrom);
assertEquals('ă', $t[1]->transfTo);
assertEquals('a', $t[2]->transfFrom);
assertEquals('', $t[2]->transfTo);
assertEquals('ă', $t[3]);
assertEquals(1, $t[4]);

$t = text_extractTransforms("n'ailon", "nailo'ane", 0);
assertEquals(4, count($t));
assertEquals('o', $t[0]->transfFrom);
assertEquals('oa', $t[0]->transfTo);
assertEquals('', $t[1]->transfFrom);
assertEquals('e', $t[1]->transfTo);
assertEquals('a', $t[2]);
assertEquals(2, $t[3]);

$t = text_extractTransforms("n'ailon", "n'ailonului", 0);
assertEquals(2, count($t));
assertEquals('', $t[0]->transfFrom);
assertEquals('ului', $t[0]->transfTo);
assertEquals(NO_ACCENT_SHIFT, $t[1]);

$i = Inflection::get("id = " . INFL_M_OFFSET);
assertEquals('Substantiv masculin, Nominativ-Acuzativ, singular, nearticulat', $i->description);
$i = Inflection::get("id = " . INFL_F_OFFSET);
assertEquals('Substantiv feminin, Nominativ-Acuzativ, singular, nearticulat', $i->description);
$i = Inflection::get("id = " . INFL_N_OFFSET);
assertEquals('Substantiv neutru, Nominativ-Acuzativ, singular, nearticulat', $i->description);
$i = Inflection::get("id = " . INFL_A_OFFSET);
assertEquals('Adjectiv, masculin, Nominativ-Acuzativ, singular, nearticulat', $i->description);
$i = Inflection::get("id = " . INFL_P_OFFSET);
assertEquals('Pronume, Nominativ-Acuzativ, singular, masculin', $i->description);
$i = Inflection::get("id = " . INFL_V_OFFSET);
assertEquals('Verb, Infinitiv prezent', $i->description);
$i = Inflection::get("id = " . INFL_V_PREZ_OFFSET);
assertEquals('Verb, Indicativ, prezent, persoana I, singular', $i->description);

assertEquals(1, text_countVowels('abc'));
assertEquals(2, text_countVowels('abcde'));
assertEquals(8, text_countVowels('aeiouăâî'));

assertEquals('cásă', text_internalize("c'as~a", false));
assertEquals("c'asă", text_internalize("c'as~a", true));

assertEquals("cas'ă", text_placeAccent("casă", 1, ''));
assertEquals("c'asă", text_placeAccent("casă", 2, ''));
assertEquals("casă", text_placeAccent("casă", 3, ''));
assertEquals("ap'ă", text_placeAccent("apă", 1, ''));
assertEquals("'apă", text_placeAccent("apă", 2, ''));
assertEquals("apă", text_placeAccent("apă", 3, ''));
assertEquals("'a", text_placeAccent("a", 1, ''));
assertEquals("a", text_placeAccent("a", 2, ''));

assertEquals("șa'ibă", text_placeAccent("șaibă", 2, ''));
assertEquals("ș'aibă", text_placeAccent("șaibă", 3, ''));
assertEquals("ș'aibă", text_placeAccent("șaibă", 2, 'a'));
assertEquals("ș'aibă", text_placeAccent("șaibă", 3, 'a'));
assertEquals("șa'ibă", text_placeAccent("șaibă", 2, 'i'));
assertEquals("șa'ibă", text_placeAccent("șaibă", 3, 'i'));

assertEquals("unfuckingbelievable", text_insert("unbelievable", "fucking", 2));
assertEquals("abcdef", text_insert("cdef", "ab", 0));
assertEquals("abcdef", text_insert("abcd", "ef", 4));

assertEquals('mamă      ', text_padRight('mamă', 10));
assertEquals('mama      ', text_padRight('mama', 10));
assertEquals('ăâîșț   ', text_padRight('ăâîșț', 8));
assertEquals('ăâîșț', text_padRight('ăâîșț', 5));
assertEquals('ăâîșț', text_padRight('ăâîșț', 3));

assertEqualArrays(array('c', 'a', 'r'), text_unicodeExplode('car'));
assertEqualArrays(array('ă', 'a', 'â', 'ș', 'ț'),
                  text_unicodeExplode('ăaâșț'));

assertEqualArrays(array(1, 5, 10),
                  util_intersectArrays(array(1, 3, 5, 7, 9, 10),
                                       array(1, 2, 4, 5, 6, 8, 10)));
assertEqualArrays(array(),
                  util_intersectArrays(array(2, 4, 6, 8),
                                       array(1, 3, 5, 7)));

assert(!lock_release('test'));
assert(!lock_exists('test'));
assert(lock_acquire('test'));
assert(lock_exists('test'));
assert(!lock_acquire('test'));
assert(lock_release('test'));
assert(!lock_exists('test'));
assert(!lock_release('test'));

assertEquals(0, util_findSnippet(array(array(1, 2, 10))));
assertEquals(1, util_findSnippet(array(array(1, 2, 10),
                                       array(5, 6, 9))));
assertEquals(2, util_findSnippet(array(array(1, 2, 10),
                                       array(5, 6, 8))));
assertEquals(4, util_findSnippet(array(array(1, 2, 10),
                                       array(6, 20),
                                       array(8, 15))));

assertEquals('$abc$ @def@', text_formatLexem('$abc$ @def@')); // This is intentional -- lexem formatting is very lenient.
assertEquals("m'amă m'are", text_formatLexem("m'am~a máre  "));

?>
