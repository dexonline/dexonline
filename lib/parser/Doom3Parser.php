<?php

class Doom3Parser extends Parser {

  const SOURCE_ID = 88;

  const INFO_KEYWORDS = [
    'antepus',
    'azi glumeț',
    '#cuv.# #amerind.#',
    '#cuv.# #ar.#',
    '#cuv.# #jap.#',
    'în tempo rapid',
    'mai #frecv.#',
    'rar',
    'singur/postpus',
    'sport',
    'uzual',
    // getGrammar() will add all abreviations
  ];

  const PRONUNCIATION_KEYWORDS = [
    '#cit.# #engl.#',
    '#cit.#',
    '#pron.# #engl.#',
    '#pron.# #fr.#',
    '#pron.# #germ.#',
    '#pron.# #gr.#',
    '#pron.# #it.#',
    '#pron.# #port.#',
    '#pron. rom.#',
    '#pron.# #sp.#',
    '#pron.# #sued.#',
    '#pron.#',
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
      'titleText (titleParent titleText?)*',
    ],
    'titleParent' => [
      '"(" titleText titleParent? ")"',
      '"(" titleText (titleParent titleText?)* ")"',
    ],
    'titleText' => [
      '/(\pL|[-.,~\'\/ ]|\^(\d+|\{\d+\}))+/ui',
    ],

    // a microdefinition e.g. (regiune din SUA)
    // we allow these to include infoBlocks, e.g. carcalete^2, complet^2, diplomat^2
    'microdef' => [
      '"(" microdefChunk+"; " ")"',
    ],
    'microdefChunk' => [
      'infoBlock ws microdefText',
      'microdefText infoBlock',
      'microdefText',
    ],
    'microdefText' => [
      '/(\pL|[-0-9.,\/ ])+/ui',
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
      '"[" (infoBlock ws)? (pron ws)? pronKeyword ws pron (ws "/" ws pronAltKeyword ws pron)? "]"',
    ],
    'pronKeyword' => [ /* set in getGrammar() */ ],
    'pronAltKeyword' => [
      '"(în tempo rapid)"',
      '"#engl.#"',
      '"#rom.#"',
    ],
    'pron' => [
      '"$" pronUnformatted "$"',
    ],
    'pronUnformatted' => [
      // use four backslashes to indicate that backslashes are allowed
      '/(\pL|[-\\\\\'\/^{}, ])+/ui',
    ],

    // hyphenation e.g. (desp. $a-bi-o-$)
    // accepts slash-separated lists, either as $la-la / la-la$ or as $la-la$ / $la-la$
    'hyphBlock' => [
      '"(#desp.#" ws hyphList ")"',
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
      '/(\pL|[-~#;, ])+/ui',
    ],

    // parts of speech
    'body' => [
      // Make sure noun stays before adjective. Otherwise we'll go down the
      // wrong branch for 's.  m.', which also occurs in 'adj. m., s. m.'.
      'compoundPosList (ws example)? adjective coda*',
      'nounLikePosList (ws example)? noun coda*',
      'adjectiveLikePosList (ws example)? adjective coda*',
      'invariablePosList (ws example)? coda*',
      'verbLikePosList (ws example)? verb (ws unusedTenses)? coda*',
      '"#v.#" ws reference',
    ],

    'adjectiveLikePosList' => [
      'adjectiveLikePosWithInfo+", "',
      'adjectiveLikePosWithInfo ws "/" ws (infoBlock ws)? adjectiveLikePosList',
      // one or more alternative parts of speech:
      // @+demidulce@ #adj.# #m.# și #f.# (+ #s.# #n.#: $vin ~$, + #s.# #f.#: $șampanie ~$);
      'adjectiveLikePos ws "(" altAdjectivePos+", " ")"',
    ],
    'adjectiveLikePosWithInfo' => [
      '(infoBlock ws)? adjectiveLikePos (ws infoBlock)? (ws example)?'
    ],
    'adjectiveLikePos' => [
      '"#adj.# #f.#"',
      '"#adj.# #m.# #pl.#"',
      '"#adj.# #m.# și #f.#"',
      '"#adj.# #m.#"',
      '"#adj.# #pr.# antepus #m.#"',
      '"#adj.# #pr.# postpus #m.#"',
      '"#adj.# #pr.# #m.# #pl.#"',
      '"#adj.# #pr.# #m.#"',
      '"#adj.# #pr.#"',
      '"#adj.#"',
      '"#loc.# #pr.# #pl.#"',
      '"#loc.# #pr.#"',
      '"#num.# #f.#"',
      '"#num.# #m.#"',
      '"#num.#"',
      '"#pr.# #m.# #pl.#"',
      '"#pr.# #m.#"',
      '"#pr.# #pl.#"',
      '"#pr.#"',
      '"#s.# #f.#"',
      '"#s.# #m.# #pl.#"',
      '"#s.# #m.# și #f.#"',
      '"#s.# #m.#"', // for smf
      '"#s.# #n.# #pl.#"',
    ],
    'altAdjectivePos' => [
      '"+" ws nounLikePos ":" ws "$" /[a-zăâîșț~ ]+/ "$"',
    ],

    'invariablePosList' => [
      'invariablePosWithInfo+", "',
    ],
    'invariablePosWithInfo' => [
      '(infoBlock ws)? invariablePos (ws infoBlock)? (ws example)?'
    ],
    'invariablePos' => [
      '"#art.#"', // e.g. dintr-al
      '"#adj.# #invar.#"',
      '"#adv.#"',
      '"#conjcț.#"',
      '"#interj.#"',
      '"#loc.# #adj.# #pr.#"',
      '"#loc.# #adj.#"',
      '"#loc.# #adv.#"',
      '"#loc.# #conjcț.#"',
      '"#loc.# #interj.#"',
      '"#loc.# #prep.#"',
      '"#pr.# #invar.#"',
      '"#prep.#"',
    ],

    'nounLikePosList' => [
      'nounLikePosWithInfo+", "',
      'nounLikePosWithInfo ws? "/" ws (infoBlock ws)? nounLikePosList',
    ],
    'nounLikePosWithInfo' => [
      '(infoBlock ws)? nounLikePos (ws infoBlock)?'
    ],
    'nounLikePos' => [
      '/#loc\.# #s\.# #[fmn].#( #pl\.#)?/',
      '/#s\.#( propri[iu])? #[fmn]\.#( #pl\.#)?( #art\.#)?/',
      '/#s\.# propri[iu]/',
      '"#f.#"', // e.g. Belarus
    ],

    'verbLikePosList' => [
      'verbLikePosWithInfo+", "',
      '/#loc\.# #vb\.#( #refl\.#)?(,)? #v\.# / reference',
    ],
    'verbLikePosWithInfo' => [
      '(infoBlock ws)? verbLikePos (ws infoBlock)?'
    ],
    'verbLikePos' => [
      '/#vb\.#( #(pred|refl)\.#)?/',
    ],

    'compoundPosList' => [
      '(nounLikePos|adjectiveLikePos|invariablePos|verbLikePos)+" + "',
    ],

    // inflected forms with optional details (hyphenation, pronunciation, abbreviation, examples)
    'reference' => [
      '/@(!)?(\pL|\')+(\^\d+|\^\{\d+\})?@/ui',
    ],
    'adjective' => [
      '/[;,]/ ws (microdef ws)? (infoBlock ws)? adjInflectionList ws formWithDetails adjective',
      // e.g. atât^1
      '" / " (infoBlock ws)? (adjSlashInflection ws)? formWithDetails adjective',
      '""',
    ],
    'noun' => [
      '/[;,]/ ws (microdef ws)? (infoBlock ws)? nounInflection ws formWithDetails noun',
      // used for nouns with different plurals, e.g. "pl. m. $accelerat'ori$ /n. $accelerato'are$"
      '" / " (infoBlock ws)? (nounSlashInflection ws)? formWithDetails noun',
      '""',
    ],
    'verb' => [
      '/[;,]/ ws (microdef ws)? (infoBlock ws)? verbInflection ws formWithDetails verb',
      // used for verbs with different conjugations, e.g. "intranz. $ajungi$ / tranz. $ajunge$"
      '" / " (infoBlock ws)? (verbSlashInflection ws)? formWithDetails verb',
      '""',
    ],

    // inflection names
    'adjInflectionList' => [
      'adjInflectionWithDetails+", "',
    ],
    'adjInflectionWithDetails' => [
      '(infoBlock ws)? adjInflection (ws example)?',
    ],
    'adjInflection' => [
      '"#adj.# #f.#"',
      '"#art.#"',
      '/#f\.#( #sg\.# și #pl\.#)?/',
      '"#g.-d.# #art.#"',
      '/#g\.-d\.#( #(pl|sg)\.#)?( #m\.# și #f\.#)?/',
      '/#pl\.#( #m\.# și #f\.#)?/',
      '/#s\.# #f\.#( #sg\.# și #pl\.#)?/',
    ],
    'adjSlashInflection' => [
      '"#ac.# #m.#"',
    ],
    'nounInflection' => [
      '"#art.# #m.#"',
      '"#art.#"',
      '/#g.-d\.#( #art\.#)?/',
      '"#neart.#"',
      '"#pl.# #art.#"',
      '/#pl\.#( #[fmn]\.#)?/',
      '/#voc\.#( #neart\.#)?/',
    ],
    'nounSlashInflection' => [
      '/#[fmn]\.#/',
    ],
    'verbInflection' => [
      'impersonalTense',
      'personalTense ws person ws "și" ws person',
      'personalTense ws person',
      'person',
    ],
    'verbSlashInflection' => [
      '"#tranz.#"',
    ],
    'impersonalTense' => [
      '"#ger.#"',
      '"#part.#"',
      // these are not really impersonal, but they only accept one person
      '/#imper\.# 2 #sg\.#( #afirm\.#( #intranz\.#)?)?/',
      '"#neg.#"',
    ],
    'personalTense' => [
      '/(#conj\.# #prez\.#|#imperf\.#|#ind\.# #prez\.#|#m\.m\.c\.p\.#|#perf\. s\.#)/',
    ],
    'person' => [
      '/[123]/',
      '/[123] (#pl\.#|#sg\.#)/',
    ],
    'unusedTenses' => [
      '/\((mai folosit|nefolosit).*\)/',
    ],

    'formWithDetails' => [
      '(infoBlock ws)? form (ws pronBlock)? (ws hyphBlock)? (ws example)?',
    ],
    'form' => [
      '/\$(\pL|[-\'\/\(\) ])+\$/ui',
    ],
    'example' => [
      '/\(((dar:|în:|mai ales în:|și:|și în:) )?\$(\pL|[-0-9#!?;,.=~\/\(\) ])+\$\)/ui',
    ],

    // abbrevation and symbol
    'coda' => [
      '";" ws ("#abr.#"|"#simb.#") ws (infoBlock ws)? /\$(\pL|[-0-9.,\/#° ])+\$/ui (ws pronBlock)?',
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
    $regexp = implode('|', $a);
    $regexp = str_replace('.', '\\.', $regexp);
    $regexp = str_replace('/', '\\/', $regexp);
    $regexp = '/(' . $regexp . ')/';
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

    // migrate bold and italics before punctuation
    $rep = preg_replace('/([;,])([@$]) /', '$2$1 ', $rep);

    // migrate italics inside parentheses and brackets
    $rep = preg_replace('/([\)\]])\$(?=([,; ]|$))/', '\$$1', $rep);
    $rep = str_replace(' $[', ' [$', $rep);
    $rep = str_replace(' $(', ' ($', $rep);

    // migrate novelty markers inside bold
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
      case 'hyphUnformatted':
        if (strpos($content, '~') !== false) {
          $warnings[] = "Am înlocuit ~ cu - în silabisirea «{$content}».";
          $content = str_replace('~', '-', $content);
        }
        break;
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
 * $să ajungă/ajungă(-ți)$; : incorrectly changed to $să ajungă/ajungă(-ți$);
 * Atlantic/Oceanul ~: tildes are usually in parentheses
 * @!crede (a ~)@ ... #imper.# 2 #sg.# $(nu) crede$: incorrectly changed to ($nu) crede$
 * @!de mâncat@ #vb.# la supin ($~ a mâncat.$) idem de scăzut
 * @dus și întors@: "în ritm rapid" instead of "în tempo rapid"
 * @!Baba-Cloanța(-Cotoroanța)@ ... $Babei-Cloanța(-Cotoroanța)$ : incorrectly changed
 * occurrences of "etc.", "dar:", "și:"

 wrong order of hyphenation / pronunciation / info / microdefinition blocks:
 * body, câteodată, cel ce, cocleț, cote d'ivoire, disjunctivă, după-masă

 index after construct:
 * @câte (de ~ ori)^{1}@ #loc.# #adv.# ...

 incompatible pos:
 @+detox@ (#fam.#) #adj.# #invar.#, #s.# #n.# $(cure ~)$
 @+drege-strică@ #adj.# #invar.#, #s.# #m.# ($meșter ~$)
 @+anticearcăn@ #adj.# #invar.# ($creme ~$), #s.# #n.#, #pl.# ...

 pos / inflections with slashes (and sometimes further details):
 * #imper.# 2 #sg.# #afirm.# $ad'ormi$/(+ clitic) $ado'arme$ ($Adormi repede!$ dar: $Adoarme-l repede! Adoarme-i bănuielile!$)

 microdefinition before simbol:
 * ($bolívar soberano$) #simb.#

 microdefinition after part of speech:
 * @+antemergător^{1}@ #adj.# #m.#, #s.# #m.# (persoană),

 microdefinition after inflected form:
 * @!centralist@ ... #adj.# #f.#, #s.# #f.# $centralistă$ (persoană), ...

 multiple forms for one inflection, with infoBlocks
 * amândoi ... #g.-d.# (antepus) $amânduror$, (singur/postpus) $amândurora

 exotic pos:
 * @!Altețele Voastre@ #loc.# #pr.# #pl.#
 * @+Altețele Voastre Regale@ #loc.# #pr.# #pl.# + #adj.#

 compound pos with addon pos:
 * @+cât privește@ #adv.# + #vb.# (+ #s.# #sg.# / #pl.#: $~ condițiile$)

 compound with redundant square bracket:
 * @+condiția (cu ~)@ #prep.# + #s.# #f.# [= $cu condiția$]
 * @+condiția ca/să (cu ~)@ #prep.# + #s.# #f.# + #conjcț.# [$=cu condiția ca/să$]

 **/
