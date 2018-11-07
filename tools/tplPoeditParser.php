<?php

// Parses Smarty templates for untranslated strings. poedit will invoke it.

if (count($argv) < 3) {
  printf("Usage: {$argv[0]} <output file> <input file> [<input file>...]\n");
  exit();
}

$dict = [];
for ($i = 2; $i < count($argv); $i++) {
  $fileName = $argv[$i];
  $lines = file($fileName);
  foreach ($lines as $lineNo => $line) {
    $lineNo++;
    $line = trim($line);
    $matches = array();
    if (preg_match_all("/\{['\"]([^\}]*)['\"]\|_[^\}]*\}/", $line, $matches)) {
      foreach ($matches[1] as $match) {
        if (!array_key_exists($match, $dict)) {
          $dict[$match] = "{$fileName}:{$lineNo}";
        }
      }
    }
  }
}

$output = '';
foreach ($dict as $match => $pos) {
  $match = escape($match);
  $output .= "#: {$pos}\n";
  $output .= "msgid \"{$match}\"\n";
  $output .= "msgstr \"\"\n";
  $output .= "\n";
}

file_put_contents($argv[1], $output);

/****************************************************************************/

function escape($s) {
  $s = str_replace(array("\\", '"'), array("\\\\", "\\\""), $s);
  return $s;
}
