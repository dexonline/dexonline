<?php
require_once("../../phplib/util.php");
ini_set('max_execution_time', '3600');
assert_options(ASSERT_BAIL, 1);
debug_off();

$dbResult = mysql_query("select * from Definition where Status = 0");
print "Examining " . mysql_num_rows($dbResult) . " definitions.\n";
$count = 0;
$dropped = 0;
$kept = 0;

while (($dbRow = mysql_fetch_assoc($dbResult)) != null) {
  $d = new Definition();
  $d->populateFromDbRow($dbRow);
  $count++;
  if ($count % 10000 == 0) {
    print "$count definitions examined, $dropped dropped, $kept kept\n";
  }

  $newRep = cleanupReferences($d->internalRep);
  if ($newRep != $d->internalRep) {
    //print "{$d->internalRep}\n{$newRep}\n";
    $d->internalRep = $newRep;
    $d->htmlRep = text_htmlize($d->internalRep);
    $d->save();
  }
}

print "$count definitions examined, $dropped dropped, $kept kept\n";

function cleanupReferences($s) {
  global $dropped;
  global $kept;

  $result = '';
  $text = '';
  $ref = '';
  $mode = 0; // 0 = not between bars; 1 = text; 2 = reference
  for ($i = 0; $i < strlen($s); $i++) {
    $char = $s[$i];
    if ($char == '|') {
      if ($mode == 2) {
	$sText = simplifyText($text);
	$sRef = simplifyText($ref);
	if ($sText == $sRef || $sRef == '') {
	  $result .= $text; $dropped++;
	} else if (isInflectedForm($sText, $sRef)) {
	  $result .= $text; $dropped++;
	} else if (text_endsWith($sText, ' ' . $ref)) {
	  $result .= $text; $dropped++;
	} else if (text_startsWith($sText, $ref . ' ')) {
	  $result .= $text; $dropped++;
	} else {
	  //print "Keeping reference |$text|$ref|\n";
	  $result .= "|$text|$ref|"; $kept++;
	}
	$text = '';
	$ref = '';
      }
      $mode = ($mode + 1) % 3;
    } else {
      switch($mode) {
      case 0: $result .= $char; break;
      case 1: $text .= $char; break;
      case 2: $ref .= $char;
      }
    }
  }
  assert($mode == 0);
  return $result;
}

function simplifyText($s) {
  $s = preg_replace("/[@$^0-9()%.]/", "", text_unicodeToLower(trim($s)));
  if (text_endsWith($s, '-')) {
    $s = substr($s, 0, strlen($s) - 1);
  }
  return $s;
}

function isInflectedForm($form, $baseForm) {
  $parts = split('[- ]', $form);

  foreach ($parts as $part) {
    $part = addslashes($part);
    $baseForm = addslashes($baseForm);
    $query = "select count(*) from wordlist, lexems " .
      "where wl_neaccentuat = '$part' " .
      "and wl_lexem = lexem_id " .
      "and (lexem_utf8_general = '$baseForm' " .
      "or lexem_neaccentuat = '$baseForm')";
    if (db_fetchInteger(mysql_query($query))) {
      return true;
    }
  }
  return false;
}

?>
