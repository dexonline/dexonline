<?php
require_once("../../phplib/util.php");
assert_options(ASSERT_BAIL, 1);
debug_off();

$dbResult = mysql_query('select * from lexems ' .
                        'where lexem_forma not rlike "\'" ');
$seen = 0;
$fixed = 0;

while ($dbRow = mysql_fetch_assoc($dbResult)) {
  $lexem = Lexem::createFromDbRow($dbRow);
  //  print $lexem->form . "\n";
  $defs = Definition::loadByLexemId($lexem->id);

  // We want all the definitions to 'agree':
  // - Have the lexicon equal to $lexem->form. If not, skip this lexem.
  // - Contain 0 or 1 accents
  // - All those that have accents should have it in the same position
  $position = false;
  foreach($defs as $def) {
    $accented = _text_extractLexiconHelper($def);
    $accented = internalizeLexicon($accented);
    //print "      $accented\n";

    $accentCount = mb_substr_count($accented, "'");
    if ($accentCount > 1) {
      $position = false;
      break;
    }
    if ($accentCount) {
      $newPos = mb_strpos($accented, "'");
      if ($position !== false && $position != $newPos) {
        $position = false;
        $break;
      }
      $position = $newPos;
    }

    $unaccented = str_replace("'", "", $accented);
    if ($unaccented != $lexem->form) {
      $position = false;
      $break;
    }
  }

  if ($position !== false) {
    assert(!text_contains($lexem->form, "'"));
    //print "{$lexem->form} : ";
    $lexem->form = mb_substr($lexem->form, 0, $position) . "'" .
      mb_substr($lexem->form, $position);
    //print "{$lexem->form}\n";
    $lexem->save();
    $lexem->regenerateParadigm();
    $fixed++;
  }

  $seen++;
  if ($seen % 1000 == 0) {
    print "Seen: $seen lexems, fixed: $fixed\n";
  }
}

print "Seen: $seen lexems, fixed: $fixed\n";

function internalizeLexicon($name) {
  $name = text_shorthandToUnicode($name);

  $name = str_replace(array('á', 'Á', 'ắ', 'Ắ', 'ấ', 'Ấ',
                            'é', 'É', 'í', 'Í', 'î́', 'Î́',
                            'ó', 'Ó', 'ú', 'Ú', 'ý', 'Ý'),
                      array("'a", "'A", "'ă", "'Ă", "'â", "'Â",
                            "'e", "'E", "'i", "'I", "'î", "'Î",
                            "'o", "'O", "'u", "'U", "'y", "'Y"),
                      $name);

  //$name = text_removeAccents($name);
  $name = trim($name);
  $name = strip_tags($name);
  $name = text_unicodeToLower($name);
  // Strip HTML escape codes
  $name = preg_replace("/&[^;]+;/", "", $name);
  // Strip all illegal characters
  $result = '';
  $len = mb_strlen($name);
  for ($i = 0; $i < $len; $i++) {
    $c = text_getCharAt($name, $i);
    if (strstr(' !@#$%^&*()-_+=\\|[]{},.<>/?;:"`~0123456789', $c) === FALSE) {
      $result .= $c;
    }
  }
  return $result;
}

?>
