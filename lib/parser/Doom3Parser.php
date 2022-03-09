<?php

class Doom3Parser extends Parser {

  const SOURCE_ID = 88;

  const PARTS_OF_SPEECH = [
    'adj.', 'adv.', 'conjcț.', 'interj.', 'loc.', 'num.', 'pr.', 'prep.',
    'pron.', 's.', 'vb.',
  ];

  const INFO_KEYWORDS = [
    'cuv. amerind.', '#cuv.# #amerind.#',
    'cuv. ar.', '#cuv.# #ar.#',
    'cuv. jap.', '#cuv.# #jap.#',
    'în tempo rapid',
    'rar',
    'sport',
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
      'pos ignored',
    ],

    'pos' => [ /* set in getGrammar() */ ],

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

    $partsOfSpeech = [];
    foreach (self::PARTS_OF_SPEECH as $pos) {
      // include as-is and with every word abbreviated
      $partsOfSpeech[] = $pos;
      $partsOfSpeech[] = '#' . str_replace(' ', '# #', $pos) . '#';
    }

    $g = self::GRAMMAR;
    $g['pronKeyword'][] = self::implodeStringConstants(self::PRONUNCIATION_KEYWORDS);
    $g['info'][] = self::implodeStringConstants($infoKeywords);
    $g['pos'][] = self::implodeStringConstants($partsOfSpeech);

    return $g;
  }

  function prepare($rep) {
    // migrate indices inside bold
    $rep = preg_replace('/@\^(\d+) /', '^$1@ ', $rep);

    // migrate hyphens inside italics
    $rep = str_replace('(desp. -$', '(desp. $-', $rep);
    $rep = str_replace('(#desp.# -$', '(#desp.# $-', $rep);
    $rep = str_replace('$-)', '-$)', $rep);

    // migrate italics inside parentheses and brackets
    $rep = str_replace(')$ ', '$) ', $rep);
    $rep = str_replace(' $[', ' [$', $rep);
    $rep = str_replace(']$ ', '$] ', $rep);

    // migrate edition markers inside bold
    $rep = preg_replace('/^([!+])@/', '@$1', $rep);

    // tildes in title should be bold
    $rep = str_replace('@ ~)', ' ~)@', $rep);
    $rep = str_replace('@ ~ @', ' ~ ', $rep);

    // replace final tildes with dashes in hyphenations
    $rep = preg_replace('/(?<=\pL)~\$\)/', '-$)', $rep);

    // remove duplicate markers
    $rep = str_replace('@ @', ' ', $rep);
    $rep = str_replace('@@', '', $rep);
    $rep = str_replace('$ $', ' ', $rep);
    $rep = str_replace('$$', '', $rep);
    var_dump($rep);

    return $rep;
  }
}

/**
 * Remainig corner cases. If they turn out to be numerous, extend the grammar.

 wrong order:
 * abia ce (desp. ...) (pop.)
 * angstrom [pron. ...] (înv.)
 * buieci (desp. ...) (reg.)
 * cocleț (desp. ...) (pop.)
 * cote d'ivoire (microdef) (info) [pron] (microdef)
 * disjunctivă (desp.) (propoziție)

 two entries:
 * @+aceea (după ~)@ [pron.  ...] / (în tempo rapid) @dup-aceea@
 * @adendă@ / (lat.) @addenda@
 * @adineauri@ / (înv.) @adineaori@
 * @Aheron@ / (gr.) @Acheron@ [pron.] (desp.)
 * africanologă (livr.) / (colocv.) africanoloagă -> idem agrobiologă, agrogeologă,
 agrometeorologă, alergologă, anatomopatologă, bacteriologă...
 * @!alocuri (pe ~)@ / (în tempo rapid) @pe-alocuri@
 * @Amfitrion@ (erou mitic) / (gr.) @Amphitryon@
 * @antifilozofic@ / (livr.) @antifilosofic@
 * @asiduu@ [pron] (desp) / @asiduu@ [pron] (desp), idem continuu, discontinuu
 * @contiguu@ (livr.) [pron] (desp) / @contiguu@ [pron] (desp)
 * @asta (de ~)@ / (în tempo rapid) @de-asta@
 * @cluj-napoca@ (oraș) / (uzual) @cluj@, idem drobeta-turnu severin
 * corintian (desp) / (relig) corintean
 * derby (engl) / derbi (microdef)
 * @domnule@ / (fam., în tempo rapid) dom'le / domn'e
 -> between title and slash: info > pron > desp, microdef
 -> between slash and title: info*

 two pronunciations:
 * afro-jazz: [pron. afrogaz / engl.  afrogez ]

 dot in title:
 * atât... cât și

 expressions in title:
 * bază: (pe ~ de)

 **/
