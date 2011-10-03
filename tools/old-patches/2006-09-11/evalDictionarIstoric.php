<?php

require_once("../../phplib/util.php");

$lines = file('/home/cata/Desktop/dict.html');

foreach ($lines as $line) {
  $line = trim($line);

  $parts = split("=", $line, 2);

  // Take the first word
  $dnameArray = split(' ', $parts[0], 2);

  $dname = text_internalizeDname($dnameArray[0]);

  $words = Word::loadByDname($dname);
  if (!count($words)) {
    print $dname . "\n";
  }
}

?>
