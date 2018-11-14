<?php

// Parses Smarty templates for untranslated strings. poedit will invoke it.

if (count($argv) < 3) {
  printf("Usage: {$argv[0]} <output file> <input file> [<input file>...]\n");
  exit();
}

$dict = [];
for ($i = 2; $i < count($argv); $i++) {
  $fileName = $argv[$i];
  $contents = file_get_contents($fileName);
  preg_match_all("/\{['\"]([^|]*)['\"]\|_/Us", $contents, $matches, PREG_OFFSET_CAPTURE);

  // reverse the array to prefer the earliest occurrence of identical strings
  foreach ($matches[1] as $match) {
    $text = addslashes($match[0]);
    $text = str_replace("\n", "\\n", $text);

    if (!isset($dict[$text])) {
      // get the line number as 1 + the number of newlines up to the offset
      $lineNo = substr_count($contents, "\n", 0, $match[1]);
      $dict[$text] = "{$fileName}:{$lineNo}";
    }
  }
}

$output = '';
foreach ($dict as $match => $pos) {
  $output .= "#: {$pos}\n";
  $output .= "msgid \"{$match}\"\n";
  $output .= "msgstr \"\"\n";
  $output .= "\n";
}

file_put_contents($argv[1], $output);
