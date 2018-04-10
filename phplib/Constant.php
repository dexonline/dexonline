<?php

/* Big ugly constants sit here so as not to clutter the code base. */

class Constant {

  const CLEANUP_PATTERNS = [
    '/(?<!\\\\)ş/'   => 'ș',
    '/(?<!\\\\)Ş/'   => 'Ș',
    '/(?<!\\\\)ţ/'   => 'ț',
    '/(?<!\\\\)Ţ/'   => 'Ț',

    '/ ◊ /' => ' * ',  /* (U+25CA) LOZENGE ◊ */
    '/ ♦ /' => ' ** ', /* (U+2666) BLACK DIAMOND SUIT ♦ */

    // hyphens and spaces
    '/(?<!\\\\) /'   => ' ',     /* U+00A0 non-breaking space */
    '/(?<!\\\\)‑/'   => '-',     /* U+2011 non-breaking hyphen */
    '/(?<!\\\\)—/'   => '-',     /* U+2014 em dash */
    '/(?<!\\\\)­/'   => '',      /* U+00AD soft hyphen */
    '/[ \t]+/'       => ' ',     /* Leave newlines alone. Some editors like to use them in definitions. */

    // Replace a quotation mark that may look like comma
    '/(?<!\\\\)‚/'   => ',',     /* U+201A SINGLE LOW-9 QUOTATION MARK */

    // Replace all kinds of double quotes with the ASCII ones.
    // Do NOT alter ″ (double prime, 0x2033), which is used for inch and second symbols.
    '/(?<!\\\\)“/'   => '"',     /* U+201C LEFT DOUBLE QUOTATION MARK */
    '/(?<!\\\\)”/'   => '"',     /* U+201D RIGHT DOUBLE QUOTATION MARK */
    '/(?<!\\\\)„/'   => '"',     /* U+201E DOUBLE LOW-9 QUOTATION MARK */
    '/(?<!\\\\)‟/'   => '"',     /* U+201F DOUBLE HIGH-REVERSED-9 QUOTATION MARK */

    // Replace the ordinal indicator with the degree sign.
    '/(?<!\\\\)º/'   =>  '°',    /* U+00BA => U+00B0 */

    "/(?<!\\\\)\r\n/" => "\n"    /* Unix newlines only */
  ];

  const APOSTROPHE_CLEANUP_PATTERNS = [
    // Replace all kinds of single quotes and acute accents with the ASCII apostrophe.
    // Do NOT alter ′ (prime, 0x2032), which is used for foot and minute symbols.
    // Apostrophes are different from other patterns because they double as accent indicators.
    '/(?<!\\\\)´/'   => "'",     /* U+00B4 */
    '/(?<!\\\\)‘/'   => "'",     /* U+2018 */
    '/(?<!\\\\)’/'   => "'",     /* U+2019 */
  ];

  // will use preg_replace for string values, preg_replace_callback for arrays
  const HTML_PATTERNS = [
    '/▶(.*?)◀/s' => '',                                                 // remove unwanted parts of definition
    '/(?<!\\\\)"([^"]*)"/' => '„$1”',                                    // "x" => „x” - romanian quoting style
    '/(?<!\\\\)\{{2}(.*)(?<![+])\}{2}/U' => ['FootnoteHtmlizer', 'htmlize'],      // {{fotnote}}
    '/(?<!\\\\)#([^#]*)#/' => ['AbbrevHtmlizer', 'htmlize'],            // #abbreviation#
    '/(?<!\\\\)%(.*)(?<!\\\\)%/Us' => '<span class="spaced">$1</span>',  // %spaced%
    '/(?<!\\\\)@(.*)(?<!\\\\)@/Us' => '<b>$1</b>',                       // @bold@
    '/(?<!\\\\)\\$(.*)(?<!\\\\)\\$/Us' => '<i>$1</i>',                   // $italic$
    '/(?<!\\\\)\^(\d)/' => '<sup>$1</sup>',                             // superscript ^123
    '/(?<!\\\\)\^\{([^}]*)\}/' => '<sup>$1</sup>',                      // superscript ^{a b c}
    '/(?<!\\\\)_(\d)/' => '<sub>$1</sub>',                              // subscript _123
    '/(?<!\\\\)_\{([^}]*)\}/' => '<sub>$1</sub>',                       // superscript _{a b c}
    '/(?<!\\\\)\{-([^}]*)-\}/' => '<del>$1</del>',                      // deletions {-foo-}
    '/(?<!\\\\)\{\+([^}]*)\+\}/' => '<ins>$1</ins>',                    // insertions {+foo+}

    // |foo|bar| references
    '/(?<!\\\\)\|([^|]*)\|([^|]*)\|/' => '<a class="ref" href="/definitie/$2">$1</a>',

    // tree mentions
    '/([-a-zăâîșț]+)\[\[([0-9]+)\]\]/iu' =>
    '<span data-toggle="popover" data-html="true" data-placement="auto right" ' .
    'class="treeMention" title="$2">$1</span>',

    '/([-a-zăâîșț]+)\[([0-9]+)(\*{0,2})\]/iu' => [ 'MentionHtmlizer' ],      // meaning mentions
  ];

  const HTML_REPLACEMENTS = [
    ' - '  => ' – ',  /* U+2013 */
    ' ** ' => ' ♦ ',  /* U+2666 */
    ' * '  => ' ◊ ',  /* U+25CA */
    "\\'"  => '’',    /* U+2019 */
  ];

  const ACCENTS = [
    'accented' => [
      'á', 'Á', 'ắ', 'Ắ', 'ấ', 'Ấ', 'é', 'É', 'í', 'Í', 'î́', 'Î́',
      'ó', 'Ó', 'ö́', 'Ö́', 'ú', 'Ú', 'ǘ', 'Ǘ', 'ý', 'Ý',
    ],
    'unaccented' => [
      'a', 'A', 'ă', 'Ă', 'â', 'Â', 'e', 'E', 'i', 'I', 'î', 'Î',
      'o', 'O', 'ö', 'Ö', 'u', 'U', 'ü', 'Ü', 'y', 'Y',
    ],
    'marked' => [
      "'a", "'A", "'ă", "'Ă", "'â", "'Â", "'e", "'E", "'i", "'I", "'î", "'Î",
      "'o", "'O", "'ö", "'Ö", "'u", "'U", "'ü", "'Ü", "'y", "'Y",
    ],
  ];
    
  // prefixes which should be followed by 'î', not 'â'
  const I_PREFIXES = [
    'auto',
    'bine',
    'bun',
    'cap',
    'co',
    'de',
    'dez', // false positive: "dezânoaie"
    'ex',
    'ne',
    'nemai',
    'ori',
    'prea',
    'pre',
    're',
    'semi',
    'sub',
    'supra',
    'ultra',
    // false negatives: "altîncotro"
  ];

}
