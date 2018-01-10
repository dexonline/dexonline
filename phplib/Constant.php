<?php

/* Big ugly constants sit here so as not to clutter the code base. */

class Constant {

  const CLEANUP_REPLACEMENTS = [
    'ş'   => 'ș',
    'Ş'   => 'Ș',
    'ţ'   => 'ț',
    'Ţ'   => 'Ț',
    '‑'   => '-',     /* U+2011 non-breaking hyphen */
    '—'   => '-',     /* U+2014 em dash */
    ' ◊ ' => ' * ',
    ' ♦ ' => ' **',
    ' '   => ' ',     /* U+00A0 non-breaking space */
    '­'   => '',      /* U+00AD soft hyphen */

    // Replace all kinds of double quotes with the ASCII ones.
    // Do NOT alter ″ (double prime, 0x2033), which is used for inch and second symbols.
    '„'   => '"',     /* U+201E */
    '”'   => '"',     /* U+201D */
    '“'   => '"',     /* U+201C */
    '‟'   => '"',     /* U+201F */

    // Replace all kinds of single quotes and acute accents with the ASCII apostrophe.
    // Do NOT alter ′ (prime, 0x2032), which is used for foot and minute symbols.
    '´'   => "'",     /* U+00B4 */
    '‘'   => "'",     /* U+2018 */
    '’'   => "'",     /* U+2019 */ 

    // Replace the ordinal indicator with the degree sign.
    'º'   =>  '°', /* U+00BA => U+00B0 */
  ];

}
