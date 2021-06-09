#!/usr/bin/php
<?php

/**
 * This script creates a subset of the Material Icons font. To use it, adapt
 * CODEPOINTS below and run the script.
 **/

require_once __DIR__ . '/../lib/Core.php';

// list of glyphs we want in the subset, specified by their codepoint
const CODEPOINTS = [
  'add',
  'arrow_back',
  'attach_file',
  'badge',
  'chevron_left',
  'chevron_right',
  'clear',
  'comment',
  'content_copy',
  'content_cut',
  'credit_card',
  'delete',
  'description',
  'done',
  'drag_indicator',
  'edit',
  'email',
  'expand_less',
  'expand_more',
  'favorite',
  'file_download',
  'filter_alt',
  'first_page',
  'flag',
  'help',
  'history',
  'hourglass_empty',
  'info',
  'keyboard',
  'language',
  'last_page',
  'link',
  'lock',
  'login',
  'logout',
  'merge_type',
  'navigate_before',
  'navigate_next',
  'park',
  'person',
  'play_arrow',
  'refresh',
  'remove',
  'repeat',
  'save',
  'savings',
  'school',
  'search',
  'settings',
  'shield',
  'shuffle',
  'subdirectory_arrow_right',
  'swap_horiz',
  'today',
  'translate',
  'visibility',
  'visibility_off',
];

const ASCII_GLYPHS = [ '5f-7a', '30-39' ]; // always include [_a-z0-9]

// Use the stable font, but the master codepoints (there are no codepoints in
// the release).
const CODEPOINT_URL = 'https://raw.githubusercontent.com/google/material-design-icons/master/font/MaterialIcons-Regular.codepoints';
const FONT_URL = 'https://github.com/google/material-design-icons/blob/master/font/MaterialIcons-Regular.ttf?raw=true';

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
