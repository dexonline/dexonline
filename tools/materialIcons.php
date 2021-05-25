#!/usr/bin/php
<?php

/**
 * This script creates a subset of the Material Icons font. To use it, adapt
 * CODEPOINTS below and run the script.
 **/

require_once __DIR__ . '/../lib/Core.php';

// list of glyphs we want in the subset, specified by their codepoint
const CODEPOINTS = [
  'chevron_left',
  'chevron_right',
  'clear',
  'credit_card',
  'delete',
  'description',
  'done',
  'edit',
  'emoji_symbols',
  'expand_less',
  'favorite',
  'flag',
  'help',
  'history',
  'hourglass_empty',
  'info',
  'keyboard',
  'language',
  'link',
  'login',
  'logout',
  'person',
  'refresh',
  'repeat',
  'save',
  'search',
  'settings',
  'shield',
  'today',
  'visibility',
  'visibility_off',
];

const ASCII_GLYPHS = [ '5f-7a', '30-39' ]; // always include [_a-z0-9]

// Use the stable font, but the master codepoints (there are no codepoints in
// the release).
const CODEPOINT_URL = 'https://raw.githubusercontent.com/google/material-design-icons/master/font/MaterialIcons-Regular.codepoints';
const FONT_URL = 'https://github.com/google/material-design-icons/raw/4.0.0/font/MaterialIcons-Regular.ttf';

const TMP_FONT_FILE = '/tmp/material-icons.ttf';
const OUTPUT_FILE = __DIR__ . '/../www/fonts/material-icons.woff2';

$glyphs = getGlyphs();

// download the font file
file_put_contents(TMP_FONT_FILE, file_get_contents(FONT_URL));

$cmd = sprintf(
  'fonttools subset %s --unicodes=%s --no-layout-closure --output-file=%s --flavor=woff2',
  TMP_FONT_FILE,
  implode(',', $glyphs),
  OUTPUT_FILE
);

print("Running: {$cmd}\n");
OS::executeAndAssert($cmd);

unlink(TMP_FONT_FILE);

/*************************************************************************/

// parses the codepoints file and returns a set of Unicode glyph codes
function getGlyphs() {
  // convert the 'codepoint glyph' format to a codepoint => glyph array
  $codepointLines = file(CODEPOINT_URL);

  $codepoints = [];
  foreach ($codepointLines as $line) {
    $parts = explode(' ', $line, 2);
    $codepoints[$parts[0]] = trim($parts[1]);
  }

  $result = ASCII_GLYPHS;

  foreach (CODEPOINTS as $code) {
    isset($codepoints[$code]) || die("ERROR: Ligature {$code} is not defined in the font.\n");
    $result[] = $codepoints[$code];
  }

  return $result;
}
