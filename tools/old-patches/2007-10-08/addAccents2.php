<?
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

  // Phase 2:
  // - Lexems with only one vowel
  // - Paticiples (extra -T at the end, type A2)
  // - Long infinitves (extra -RE at the end, type F107/F113).

  $vowelCount = text_countVowels($lexem->form);
  if ($vowelCount == 1) {
    $lexem->form = text_placeAccent($lexem->form, 1, null);
    //print "{$lexem->form}\n";
    $lexem->save();
    $lexem->regenerateParadigm();
    $fixed++;
    $seen++;
    continue;
  }

  $position = false;
  $form = false;
  foreach($defs as $def) {
    $accented = _text_extractLexiconHelper($def);
    $accented = internalizeLexicon($accented);

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

    $newForm = str_replace("'", "", $accented);
    if ($form !== false && $form != $newForm) {
      $position = false;
      $break;
    }
    $form = $newForm;
  }

  assert(!text_contains($lexem->form, "'"));
  if ($position !== false) {
    if (($lexem->form == $form . "re" &&
        $lexem->modelType == 'F' &&
        ($lexem->modelNumber == 107 ||
         $lexem->modelNumber == 113)) ||

        ($lexem->form == $form . "t" &&
         $lexem->modelType == 'A' &&
         $lexem->modelNumber == 2)) {
      $lexem->form = mb_substr($lexem->form, 0, $position) . "'" .
        mb_substr($lexem->form, $position);
      //print "[{$lexem->form}] [$form] [$position]\n";
      $lexem->save();
      $lexem->regenerateParadigm();
      $fixed++;
    }
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
