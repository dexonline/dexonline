<?php
require_once('../../phplib/util.php');

$defIds = file('defIds.txt');

foreach ($defIds as $i => $defId) {
  $defId = trim($defId);
  if (!$defId) {
    continue;
  }
  $def = Definition::load($defId);
  print ($i + 1) . "/" . count($defIds) . " {$defId}\n";
  print "{$def->internalRep}\n";

  $newRep = '';
  $len = mb_strlen($def->internalRep);

  for ($i = 0; $i < $len; $i++) {
    $c = text_getCharAt($def->internalRep, $i);

    if ($c == '|') {
      $mid = mb_strpos($def->internalRep, '|', $i + 1);
      $close = mb_strpos($def->internalRep, '|', $mid + 1);
      $text = mb_substr($def->internalRep, $i + 1, $mid - $i - 1);
      $ref = mb_substr($def->internalRep, $mid + 1, $close - $mid - 1);
      print "|$text|$ref|\n";
      $i = $close;

      $c = readChar();
      if ($c == 'k') {
        $newRep .= "|$text|$ref|";
      } else if ($c == 'd') {
        $newRep .= $text;
      }
    } else {
      $newRep .= $c;
    }
  }

  if ($newRep != $def->internalRep) {
    print "Generating new HTML...\n";
    $def->internalRep = $newRep;
    $def->htmlRep = text_htmlize($def->internalRep);
    $def->save();
  }
  print "\n";
}

function readChar() {
  do {
    print "[D]elete or [K]eep: ";
    $s = strtolower(trim(fgets(STDIN,100)));
  } while ($s != 'd' && $s != 'k');
  return $s;
}

?>