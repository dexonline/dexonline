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
      '/[-a-zăâéîóșț\'\/ ]*[-a-zăâéóîșț]/i',
    ],
    'titleIndex' => [
      '/\^\d+/',
      '/\^\{\d+\}/',
    ],
    'construct' => [
      '/\([-a-zăâîșț\'\/ ]+ ~( [a-zăâîșț]+)?\)/i', // e.g. (a ~), (pe ~ de), (din ~ în ~)
    ],

    // a microdefinition e.g. (regiune din SUA)
    'microdef' => [
      '/\([-a-zăâîșț0-9;.,\/ ]+\)/i',
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
      '/[a-zăâčéğĭîõöșțŭ\'\/]+/i',
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
      '/[-a-zăâîșț, ]+/i',
    ],

    'body' => [
      '("adj. invar."|"#adj.# #invar.#")', // nothing further
      '("#adj.# #invar.#, #adv.#"|"adj. invar., adv.")', // nothing further
      '"#adj.# #m.#" adjective',
      '"adj. m." adjective',
      '"adj. m., s. m." adjective',
      '"#adj.# #m.#, #s.# #m.#" adjective',
      '("adv."|"#adv.#")', // nothing further
      '("interj."|"#interj.#")', // nothing further
      '("loc. adj., loc. adv."|"#loc.# #adj.#, #loc.# #adv.#")', // nothing further
      '("loc. adv."|"#loc.# #adv.#")', // nothing further
      '("loc. conjcț."|"#loc.# #conjcț.#")', // nothing further
      '("loc. prep."|"#loc.# #prep.#")', // nothing further
      '"#s.# #f.#" noun',
      '"s. f." noun',
      '"#s.# #m.#" noun',
      '"s. m." noun',
      '"#s.# #n.#" noun',
      '"s. propriu f." noun',
      '"#s.# propriu #f.#" noun',
      '"s. propriu m." noun',
      '"#s.# propriu #m.#" noun',
      '"s. propriu n." noun',
      '"#s.# propriu #n.#" noun',
      '"s. n." noun',
      '"v." ws reference',
      '("#vb.#"|"vb.") verb',
      '("#vb.# #refl.#"|"vb. refl.") verb',
    ],

    // parts of speech
    'reference' => [
      '/@[a-zăâîșț]+(\^\d+)?@/',
    ],
    'adjective' => [
      '(/[;,]/ ws (infoBlock ws)? adjInflection ws formWithDetails)*',
    ],
    'noun' => [
      '(/[;,]/ ws (infoBlock ws)? nounInflection ws formWithDetails)*',
    ],
    'verb' => [
      '(/[;,]/ ws (infoBlock ws)? verbInflection ws formWithDetails)*',
    ],

    'adjInflection' => [
      '"adj. f., s. f."', '"#adj.# #f.#, #s.# #f.#"',
      '"adj. f., s. f. sg. și pl."', '"#adj.# #f.#, #s.# #f.# #sg.# și #pl.#"',
      '"f."', '"#f.#"',
      '"f. sg. și pl."', '"#f.# #sg.# și #pl.#"',
      '"pl."', '"#pl.#"',
    ],
    'nounInflection' => [
      '"art."',
      '"#art.#"',
      '"g.-d."',
      '"#g.-d.#"',
      '"g.-d. art."',
      '"#g.-d.# #art.#"',
      '"pl."',
      '"#pl.#"',
    ],
    'verbInflection' => [
      'impersonalTense',
      'personalTense ws person ws "și" ws person',
      'personalTense ws person',
      'person',
    ],
    'impersonalTense' => [
      '/(ger\.|#ger\.#|part\.|#part\.#)/',
      // not really impersonal, but only accepts one person
      '/(imper\. 2 sg\. afirm\.|#imper\.# 2 #sg\.# #afirm\.#)/',
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
      'form (ws hyphBlock)?',
    ],
    'form' => [
      '/\$[-a-zăâîșț\'\/ ]+\$/i',
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

    // migrate italics inside parentheses and brackets
    $rep = preg_replace('/([\)\]])\$(?=( |$))/', '\$$1', $rep);
    $rep = str_replace(' $[', ' [$', $rep);

    // migrate italics before punctuation
    $rep = preg_replace('/([;,])\$ /', '\$$1 ', $rep);

    // migrate edition markers inside bold
    $rep = preg_replace('/^([!+])@/', '@$1', $rep);

    // tildes in title should be bold
    $rep = str_replace('@ ~)', ' ~)@', $rep);
    $rep = str_replace('@ ~ @', ' ~ ', $rep);

    // remove duplicate markers
    $rep = str_replace('@ @', ' ', $rep);
    $rep = str_replace('@@', '', $rep);
    $rep = str_replace('$ $', ' ', $rep);
    $rep = str_replace('$/$', '/', $rep);
    $rep = str_replace('$$', '', $rep);

    return $rep;
  }

  function postProcess(string $rule, string $content, ParserState $state, array &$warnings) {
    return $content;
  }
}

/**
 * Remainig corner cases. If they turn out to be numerous, extend the grammar.

 wrong order of hyphenation / pronunciation / info / microdefinition blocks:
 * abia ce, angstrom, buieci, cocleț, cote d'ivoire, disjunctivă

 qualifications of part of speech:
 * absorbant^2: s.n. / (tehn.) s.m.
 * accelerator^2: s. m/s. n., pl. m. $...$ / n $...$

 examples:
 * abundență (din ~): ($marfă ~, a produce ~$), idem acut, ad-hoc, ad-interim (also has abr.),
 aequo, afara-, afrikaans
 * example immediately after pos: adpres, aductor

 abbreviations:
 * last: ad-interim, adagio^1, adendă

 form pronunciations
 * advertising

 **/
