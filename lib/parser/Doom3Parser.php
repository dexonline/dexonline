<?php

class Doom3Parser extends Parser {

  const SOURCE_ID = 88;

  const INFO_KEYWORDS = [
    'cuv. amerind.', '#cuv.# #amerind.#',
    'cuv. ar.', '#cuv.# #ar.#',
    'cuv. jap.', '#cuv.# #jap.#',
    'în tempo rapid',
    'rar',
    'sport',
    'uzual',
    // getGrammar() will add all abreviations
  ];

  const PRONUNCIATION_KEYWORDS = [
    'cit.', '#cit.#',
    'pron. engl.', '#pron.# #engl.#',
    'pron. fr.', '#pron.# #fr.#',
    'pron. germ.', '#pron.# #germ.#',
    'pron. gr.', '#pron.# #gr.#',
    'pron. it.', '#pron.# #it.#',
    'pron. port.', '#pron.# #port.#',
    'pron. rom.', '#pron. rom.#',
    'pron. sp.', '#pron.# #sp.#',
    'pron. sued.', '#pron.# #sued.#',
    'pron.', '#pron.#',
  ];

  const GRAMMAR = [
    'start' => [
      'definition',
    ],
    'definition' => [
      'headerList ws body',
    ],

    // one or more entries and various data about them: hyphenation, pronunciation etc.
    'headerList' => [
      'header (ws? "/" ws? header)*',
    ],
    'header' => [
      '(infoBlock ws)? title (ws microdef)? (ws infoBlock)* (ws pronBlock)? (ws hyphBlock)?',
    ],

    // title (entry), e.g. @!aaleni'an^1@
    'title' => [
      '"@" ("+"|"!")? titleUnformatted "@"',
    ],
    'titleUnformatted' => [
      'titleChunk (ws? "/" ws? titleChunk)*',
    ],
    'titleChunk' => [
      'titleText titleIndex? (ws construct)?',
    ],
    'titleText' => [
      '/[-a-zăâéîíôóșț\'\/ ]*[-a-zăâéîíôóșț]/ui',
    ],
    'titleIndex' => [
      '/\^\d+/',
      '/\^\{\d+\}/',
    ],
    'construct' => [
       // e.g. (a ~), (pe ~ de), (din ~ în ~), (cu ~, cu vai)
      '/\([-a-zăâîșț\'\/~ ]+ ~( [a-zăâîșț]+)?\)/ui',
    ],

    // a microdefinition e.g. (regiune din SUA)
    'microdef' => [
      '/\([-a-zăâîöșț0-9;.,\/ ]+\)/ui',
    ],

    // usage, etymology etc. e.g. (astr.; med.)
    'infoBlock' => [
      '"(" infoList ")"',
    ],
    'infoList' => [
      'info (/[;,]/ ws info)*',
    ],
    'info' => [ /* set in getGrammar() */ ],

    // pronunciation e.g. [$aa$ #pron.# $a$]
    'pronBlock' => [
      '"[" (pron ws)? pronKeyword ws pron "]"',
    ],
    'pronKeyword' => [ /* set in getGrammar() */ ],
    'pron' => [
      '"$" pronUnformatted "$"',
      'pronUnformatted',
    ],
    'pronUnformatted' => [
      // use four backslashes to indicate that backslashes are allowed
      '/[a-zăâčẽéğĭîõôöșțŭə\\\\\'\/^{} ]+/ui',
    ],

    // hyphenation e.g. (desp. $a-bi-o-$)
    // accepts slash-separated lists, either as $la-la / la-la$ or as $la-la$ / $la-la$
    'hyphBlock' => [
      '"(" ("desp."|"#desp.#") ws hyphList ")"',
    ],
    'hyphList' => [
      'hyph (ws? "/" ws? hyph)*',
      '"$" hyphListUnformatted "$"',
    ],
    'hyphListUnformatted' => [
      'hyphUnformatted (ws? "/" ws? hyphUnformatted)*',
    ],
    'hyph' => [
      '"$" hyphUnformatted "$"',
      'hyphUnformatted',
    ],
    'hyphUnformatted' => [
      '/[-~a-zăâîöșț#;, ]+/ui',
    ],

    // parts of speech
    'body' => [
      // Make sure noun stays before adjective. Otherwise we'll go down the
      // wrong branch for 's.  m.', which also occurs in 'adj. m., s. m.'.
      'nounLikePosList (ws example)? noun coda*',
      'adjectiveLikePosList (ws example)? adjective coda*',
      'invariablePosList (ws example)? coda*',
      'verbLikePosList (ws example)? verb coda*',
      '("v."|"#v.#") ws reference',
    ],

    'adjectiveLikePosList' => [
      'adjectiveLikePos+", "',
      'adjectiveLikePos ws "(+" ws nounLikePos ":" ws "$" /[a-zăâîșț~ ]+/ "$)"',
    ],
    'adjectiveLikePos' => [
      '"adj. f."', '"#adj.# #f.#"',
      '"adj. m. pl."', '"#adj.# #m.# #pl.#"',
      '"adj. m. și f."', '"#adj.# #m.# și #f.#"',
      '"adj. m."', '"#adj.# #m.#"',
      '"adj. pr. antepus m."', '"#adj.# #pr.# antepus #m.#"',
      '"adj. pr. postpus m."', '"#adj.# #pr.# postpus #m.#"',
      '"adj. pr. m. pl."', '"#adj.# #pr.# #m.# #pl.#"',
      '"adj. pr. m."', '"#adj.# #pr.# #m.#"',
      '"loc. pr. pl."', '"#loc.# #pr.# #pl.#"',
      '"loc. pr."', '"#loc.# #pr.#"',
      '"num. m."', '"#num.# #m.#"',
      '"pr. m. pl."', '"#pr.# #m.# #pl.#"',
      '"pr. m."', '"#pr.# #m.#"',
      '"pr. pl."', '"#pr.# #pl.#"',
      '"pr."', '"#pr.#"',
      '"s. f."', '"#s.# #f.#"',
      '"s. m. pl."', '"#s.# #m.# #pl.#"',
      '"s. m. și f."', '"#s.# #m.# și #f.#"',
      '"s. m."', '"#s.# #m.#"', // for smf
    ],

    'invariablePosList' => [
      'invariablePos+", "',
    ],
    'invariablePos' => [
      '"adj. invar."', '"#adj.# #invar.#"',
      '"adv."', '"#adv.#"',
      '"conjcț."', '"#conjcț.#"',
      '"interj."', '"#interj.#"',
      '"loc. adj. pr."', '"#loc.# #adj.# #pr.#"',
      '"loc. adj."', '"#loc.# #adj.#"',
      '"loc. adv."', '"#loc.# #adv.#"',
      '"loc. conjcț."', '"#loc.# #conjcț.#"',
      '"loc. interj."', '"#loc.# #interj.#"',
      '"loc. prep."', '"#loc.# #prep.#"',
      '"pr. invar."', '"#pr.# #invar.#"',
      '"prep."', '"#prep.#"',
    ],

    'nounLikePosList' => [
      'nounLikePos+", "',
      'nounLikePos ws? "/" ws? infoBlock? ws? nounLikePosList',
    ],
    'nounLikePos' => [
      '"loc. s. f."', '"#loc.# #s.# #f.#"',
      '"loc. s. n. pl."', '"#loc.# #s.# #n.# #pl.#"',
      '"loc. s. n."', '"#loc.# #s.# #n.#"',
      '/(s\.|#s\.#)( propri[iu])? (f\.|#f\.#|m\.|#m\.#|n\.|#n\.#)( (pl\.|#pl\.#))?( (art\.|#art\.#))?/',
      '/(s\.|#s\.#) (f\.|#f\.#|m\.|#m\.#|n\.|#n\.#) (pl\.|#pl\.#)/',
      '/(s\.|#s\.#) propri[iu]/',
    ],

    'verbLikePosList' => [
      'verbLikePos+", "',
    ],
    'verbLikePos' => [
      '"vb. pred."', '"#vb.# #pred.#"',
      '"vb. refl."', '"#vb.# #refl.#"',
      '"vb."', '"#vb.#"',
    ],

    // inflected forms with optional details (hyphenation, pronunciation, abbreviation, examples)
    'reference' => [
      '/@(!)?[a-zăâîșț]+(\^\d+)?@/',
    ],
    'adjective' => [
      '(/[;,]/ ws (microdef ws)? adjInflection ws formWithDetails)*',
    ],
    'noun' => [
      '/[;,]/ ws (microdef ws)? nounInflection ws formWithDetails noun',
      // used for nouns with different plurals, e.g. "pl. m. $accelerat'ori$ /n. $accelerato'are$"
      'ws? "/" ws? nounSlashInflection ws formWithDetails noun',
      '""',
    ],
    'verb' => [
      '/[;,]/ ws (microdef ws)? verbInflection ws formWithDetails verb',
      // used for verbs with different conjugations, e.g. "intranz. $ajungi$ / tranz. $ajunge$"
      'ws? "/" ws? verbSlashInflection ws formWithDetails verb',
      '""',
    ],

    // inflection names
    'adjInflection' => [
      '"adj. f., s. f. sg. și pl."', '"#adj.# #f.#, #s.# #f.# #sg.# și #pl.#"',
      '"adj. f., s. f."', '"#adj.# #f.#, #s.# #f.#"',
      '"adj. f."', '"#adj.# #f.#"',
      '"art."', '"#art.#"',
      '"f. sg. și pl."', '"#f.# #sg.# și #pl.#"',
      '"f."', '"#f.#"',
      '"g.-d. art."', '"#g.-d.# #art.#"',
      '"g.-d. m. și f."', '"#g.-d.# #m.# și #f.#"',
      '"g.-d. sg. m. și f."', '"#g.-d.# #sg.# #m.# și #f.#"',
      '"g.-d. pl."', '"#g.-d.# #pl.#"',
      '"g.-d."', '"#g.-d.#"',
      '"pl. m. și f."', '"#pl.# #m.# și #f.#"',
      '"pl."', '"#pl.#"',
    ],
    'nounInflection' => [
      '"art."', '"#art.#"',
      '"g.-d. art."', '"#g.-d.# #art.#"',
      '"g.-d."', '"#g.-d.#"',
      '"neart."', '"#neart.#"',
      '"pl. m."', '"#pl.# #m.#"',
      '"pl. n."', '"#pl.# #n.#"',
      '"pl."', '"#pl.#"',
      '"voc. neart."', '"#voc.# #neart.#"',
      '"voc."', '"#voc.#"',
    ],
    'nounSlashInflection' => [
      '"m."', '"#m.#"',
      '"n."', '"#n.#"',
    ],
    'verbInflection' => [
      'impersonalTense',
      'personalTense ws person ws "și" ws person',
      'personalTense ws person',
      'person',
    ],
    'verbSlashInflection' => [
      '"tranz."', '"#tranz.#"',
    ],
    'impersonalTense' => [
      '/(ger\.|#ger\.#|part\.|#part\.#)/',
      // these are not really impersonal, but they only accept one person
      '/(imper\. 2 sg\. afirm\. intranz\.|#imper\.# 2 #sg\.# #afirm\.# #intranz\.#)/',
      '/(imper\. 2 sg\. afirm\.|#imper\.# 2 #sg\.# #afirm\.#)/',
      '/(imper\. 2 sg\.|#imper\.# 2 #sg\.#)/',
      '/(neg\.|#neg\.#)/',
    ],
    'personalTense' => [
      '/(conj\. prez\.|imperf\.|ind\. prez\.|m\.m\.c\.p\.|perf\. s\.)/',
      '/(#conj\.# #prez\.#|#imperf\.#|#ind\.# #prez\.#|#m\.m\.c\.p\.#|#perf\. s\.#)/',
    ],
    'person' => [
      '/[123]/',
      '/[123] (pl\.|#pl\.#|sg\.|#sg\.#)/',
    ],

    'formWithDetails' => [
      'form (ws pronBlock)? (ws hyphBlock)? (ws example)?',
    ],
    'form' => [
      '/\$[-a-zăâéîșț\'\/\(\) ]+\$/ui',
    ],
    'example' => [
      '/\(\$[-a-zăâîșț0-9#!;,.~\/\(\) ]+\$\)/ui',
    ],

    // abbrevation and symbol
    'coda' => [
      '";" ws ("abr."|"#abr.#"|"simb."|"#simb.#") ws /\$(\pL|[-0-9.,\/#° ])+\$/',
    ],

    // utilities
    'ws' => [
      '/(\s|\n)+/',
    ],
    'ignored' => [
      '/.*/s',
    ],
  ];

  function implodeStringConstants(array $a) {
    $regexp = '/(' . implode('|', $a) . ')/';
    $regexp = str_replace('.', '\\.', $regexp);
    return $regexp;
  }

  function getGrammar() {
    $infoKeywords = self::INFO_KEYWORDS;
    $abbrevs = Abbrev::loadAbbreviations(self::SOURCE_ID);

    foreach ($abbrevs as $short => $ignored) {
      if (Str::endsWith($short, '.')) {
        $infoKeywords[] = $short;
        $infoKeywords[] = "#{$short}#";
      }
    }

    $g = self::GRAMMAR;
    $g['pronKeyword'][] = self::implodeStringConstants(self::PRONUNCIATION_KEYWORDS);
    $g['info'][] = self::implodeStringConstants($infoKeywords);

    return $g;
  }

  function prepare($rep) {
    // migrate indices inside bold
    $rep = preg_replace('/@\^(\d+)(?=( |$))/', '^$1@', $rep);

    // migrate hyphens inside italics
    $rep = str_replace('(desp. -$', '(desp. $-', $rep);
    $rep = str_replace('(#desp.# -$', '(#desp.# $-', $rep);
    $rep = str_replace('$-)', '-$)', $rep);

    // migrate italics before punctuation
    $rep = preg_replace('/([;,])\$ /', '\$$1 ', $rep);

    // migrate italics inside parentheses and brackets
    $rep = preg_replace('/([\)\]])\$(?=([,; ]|$))/', '\$$1', $rep);
    $rep = str_replace(' $[', ' [$', $rep);
    $rep = str_replace(' $(', ' ($', $rep);

    // migrate edition markers inside bold
    $rep = preg_replace('/^([!+])@/', '@$1', $rep);

    // tildes in title should be bold
    $rep = str_replace('@ ~)', ' ~)@', $rep);
    $rep = str_replace('@ ~ @', ' ~ ', $rep);

    // tildes in examples should be italic
    $rep = str_replace('$ ~)', ' ~$)', $rep);
    $rep = str_replace(' (~ $', ' ($~ ', $rep);

    // remove duplicate markers
    $rep = str_replace('@ @', ' ', $rep);
    $rep = str_replace('@@', '', $rep);
    $rep = str_replace('$ $', ' ', $rep);
    $rep = str_replace('$/$', '/', $rep);
    $rep = str_replace('$$', '', $rep);

    return $rep;
  }

  function postProcess(string $rule, string $content, ParserState $state, array &$warnings) {
    switch ($rule) {
      case 'ignored':
        print "**** Warning: ignored [{$content}]\n";
        break;
    }
    return $content;
  }
}

/**
 * Remaining corner cases. If they turn out to be numerous, extend the grammar.

 errors and rare cases we won't handle
 * @+abac'a@ (cânepă de Manila) (cuv. filip.): no such abbreviation
 * @+abi'a ce@ (desp. $-bia$) (pop.): wrong order hyphenation > info
 * @+'afro-jazz@ [#pron.# $'afroğaz$ / #engl.# $'afrogez$]
 * $să ajungă/ajungă(-ți)$; : incorrectly changed to $să ajungă/ajungă(-ți$);
 * occurrences of "etc."

 wrong order of hyphenation / pronunciation / info / microdefinition blocks:
 * angstrom, buieci, cocleț, cote d'ivoire, disjunctivă

 pos / inflections with slashes (and sometimes further details):
 * #imper.# 2 #sg.# #afirm.# $ad'ormi$/(+ clitic) $ado'arme$ ($Adormi repede!$ dar: $Adoarme-l repede! Adoarme-i bănuielile!$)
 * aduce: ... #imper.# 2 #sg.# #afirm.# $ad'u$ /(#fam.#) '$adu$;

 pos / inflections with "+" signs:
 * @adormi (a ~)@ ... #imper.# 2 #sg.# #afirm.# $ad'ormi$/(+ clitic) $ado'arme$
 * @+alături de@ #adv.# + #prep.#
 * @+alt fel (de ~)@ (de alt soi) #prep.# + #adj.# #pr.# + #s.# #n.#
 * alt fel de #adj.# #pr.# + #s.# #n.# + #prep.#
 * altă dată^1 #adj.# #pr.# + #s.# #f.#
 * @+Alteța Voastră Regală@ #loc.# #pr.# + #adj.#

 form / inflections with "etc."
 * @a doua@ etc. @oară@
 * @+acr'i^2@ ... vb. refl., ind. prez. 3 sg. $mi$ (etc.) $se acr'ește$

 microdefinition after part of speech:
 * @+antemergător^{1}@ #adj.# #m.#, #s.# #m.# (persoană),

 microdefinition between inflection and form:
 * amândoi ... #g.-d.# (antepus) $amânduror$, (singur/postpus) $amândurora

 reference with inflected forms:
 * ad'uce aminte loc. vb. v. aduce; imper. 2 sg. afirm. ...
 * bate joc

 exotic pos:
 * @!Altețele Voastre@ #loc.# #pr.# #pl.#
 * @+Altețele Voastre Regale@ #loc.# #pr.# #pl.# + #adj.#

 example contains non-bold portions:
 * aman^2 ... #loc.# #adj.#, #loc.# #adv.# (în: $a fi/a ajunge/a lăsa la aman$)

 **/
