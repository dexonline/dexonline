<?php

/* Big ugly constants sit here so as not to clutter the code base. */

class Constant {

  const CLEANUP_PATTERNS = [
    '/(?<!\\\\)ş/'   => 'ș',
    '/(?<!\\\\)Ş/'   => 'Ș',
    '/(?<!\\\\)ţ/'   => 'ț',
    '/(?<!\\\\)Ţ/'   => 'Ț',

    '/ ◊ /' => ' * ',
    '/ ♦ /' => ' **',

    // hyphens and spaces
    '/(?<!\\\\) /'   => ' ',     /* U+00A0 non-breaking space */
    '/(?<!\\\\)‑/'   => '-',     /* U+2011 non-breaking hyphen */
    '/(?<!\\\\)—/'   => '-',     /* U+2014 em dash */
    '/(?<!\\\\)­/'   => '',      /* U+00AD soft hyphen */

    // Replace all kinds of double quotes with the ASCII ones.
    // Do NOT alter ″ (double prime, 0x2033), which is used for inch and second symbols.
    '/(?<!\\\\)„/'   => '"',     /* U+201E */
    '/(?<!\\\\)”/'   => '"',     /* U+201D */
    '/(?<!\\\\)“/'   => '"',     /* U+201C */
    '/(?<!\\\\)‟/'   => '"',     /* U+201F */

    // Replace all kinds of single quotes and acute accents with the ASCII apostrophe.
    // Do NOT alter ′ (prime, 0x2032), which is used for foot and minute symbols.
    '/(?<!\\\\)´/'   => "'",     /* U+00B4 */
    '/(?<!\\\\)‘/'   => "'",     /* U+2018 */
    '/(?<!\\\\)’/'   => "'",     /* U+2019 */ 

    // Replace the ordinal indicator with the degree sign.
    '/(?<!\\\\)º/'   =>  '°',    /* U+00BA => U+00B0 */

    "/(?<!\\\\)\r\n/" => "\n"    /* Unix newlines only */
  ];

  const HTML_PATTERNS = [
    '/(?<!\\\\)"([^"]*)"/' => '„$1”',                              // "x" => „x”
    '/(?<!\\\\)%([^%]*)%/' => '<span class="spaced">$1</span>',    // %spaced%
    '/(?<!\\\\)@([^@]*)@/' => '<b>$1</b>',                         // @bold@
    '/(?<!\\\\)\\$([^$]*)\\$/' => '<i>$1</i>',                     // italic
    '/(?<!\\\\)\^(\d)/' => '<sup>$1</sup>',                        // superscript ^123
    '/(?<!\\\\)\^\{([^}]*)\}/' => '<sup>$1</sup>',                 // superscript ^{a b c}
    '/(?<!\\\\)_(\d)/' => '<sub>$1</sub>',                         // subscript _123
    '/(?<!\\\\)_\{([^}]*)\}/' => '<sub>$1</sub>',                  // superscript _{a b c}

    // |foo|bar| references
    '/(?<!\\\\)\|([^|]*)\|([^|]*)\|/' => '<a class="ref" href="/definitie/$2">$1</a>',

    // tree mentions
    '/([-a-zăâîșț]+)\[\[([0-9]+)\]\]/i' =>
    '<span data-toggle="popover" data-html="true" data-placement="auto right" ' .
    'class="treeMention" title="$2">$1</span>',

    // meaning mentions
    '/([-a-zăâîșț]+)\[([0-9]+)\]/i' =>
    '<span data-toggle="popover" data-html="true" data-placement="auto right" ' .
    'class="mention" title="$2">$1</span>',
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
  ];

}
