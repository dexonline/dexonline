<?
require_once('../../phplib/util.php');
assert_options(ASSERT_BAIL, 1);
debug_off();

$dbResult = mysql_query("select * from lexems where lexem_extra != ''");
$seen = 0;
$removed = 0;

while (($dbRow = mysql_fetch_assoc($dbResult)) != null) {
  $l = Lexem::createFromDbRow($dbRow);
  $seen++;

  $extra = $l->extra;
  if (text_startsWith($extra, '[') && text_endsWith($extra, ']')) {
    $extra = mb_substr($extra, 1, mb_strlen($extra) - 2);
  }
  if (text_startsWith($extra, '(') && text_endsWith($extra, ')')) {
    $extra = mb_substr($extra, 1, mb_strlen($extra) - 2);
  }

  // Sometimes the extra is just the model number
  $found = ($extra == $l->modelType . $l->modelNumber . $l->restriction);

  // Sometimes the extra refers to a homonym's model
  if (!$found) {
    $homonyms = $l->loadHomonyms();
    foreach ($homonyms as $h) {
      $found |= ($extra == $h->modelType . $h->modelNumber . $h->restriction);
    }
  }

  if (!$found) {
    $defs = Definition::loadByLexemId($l->id);
    foreach ($defs as $def) {
      $found |= text_contains($def->internalRep, $extra);
    }
  }

  // Sometimes the extra contains more hyphenation information than
  // the definitions, but some of that information is obvious and can
  // be deleted.
  if (!$found) {
    $lower = text_unicodeToLower($extra);
    foreach ($defs as $def) {
      $letterSet = 'A-Za-zăâîşţĂÂÎŞŢ';
      $letter = "[$letterSet]";
      $letterOrDash = "[-$letterSet]";
      $other = "[^-$letterSet]";
      $regexp = "$other($letterOrDash+-$letterOrDash+)$other";
      $matches = array();
      $result = preg_match_all("/$regexp/", $def->internalRep, $matches);
      foreach ($matches[1] as $match) {
        $found |= text_contains($lower, $match);
      }
    }
  }

  // Sometimes the extra indicates the part of speech
  if (!$found) {
    $parts = split('\.', $extra);
    if (count($parts) > 1) {
      $allPartsFound = true;
      foreach ($parts as $part) {
        $part = trim($part);
        if ($part) {
          $part .= '.';
          // Now look this part up in every definition.
          $anyDef = false;
          foreach ($defs as $def) {
            $anyDef |= text_contains($def->internalRep, $part);
          }
          if (!$anyDef) {
            $allPartsFound = false;
          }
        }
      }
      if ($allPartsFound) {
        $found = true;
      }
    }
  }

  if ($found) {
    //print "Removing {$l->form}\t$extra\t$l->extra\n";
    $l->extra = '';
    $l->save();
    $removed++;
  } else {
    print "{$l->form} ({$l->modelType}{$l->modelNumber}" .
      "{$l->restriction})\t$extra\t$l->extra\n";
  }
}

print "Seen $seen lexems, removed $removed extra fields.\n";

?>
