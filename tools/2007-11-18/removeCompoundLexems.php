<?
require_once('../../phplib/util.php');
assert_options(ASSERT_BAIL, 1);
debug_off();

// Create the Latin model type if it doesn't exist
$model = Model::loadByTypeNumber('I', '2');
if (!$model) {
  print "Creating model I2 for biology terms\n";
  $model = Model::create('I', '2', '', 'termeni biologici');
  $model->save();
  $model->id = db_getLastInsertedId();

  $md = ModelDescription::create($model->id, 84, 0, 0, 1, NO_ACCENT_SHIFT, '');
  $md->save();
}

$dbResult = mysql_query("select * from lexems where lexem_model_type = 'T' " .
                        "order by lexem_neaccentuat");
$seen = 0;
$removed = 0;

$biologyTerms = array('plantă', 'pom', 'arbore', 'arbust', 'bot', 'zool',
                      'mamifer', 'animal');

while (($dbRow = mysql_fetch_assoc($dbResult)) != null) {
  $l = Lexem::createFromDbRow($dbRow);
  $seen++;

  $defs = Definition::loadByLexemId($l->id);
  $matchingLexicon = false;
  $biology = false;
  $appears = false;
  foreach ($defs as $def) {
    if (str_replace('î', 'â', $def->lexicon) ==
        str_replace('î', 'â', $l->unaccented)) {
      $matchingLexicon = true;
    }

    $rep = text_unicodeToLower($def->internalRep);
    $rep = str_replace(array('$', '@', '%', '.', ',', '(', ')', ';', ':'),
                       array('', '', '', '', '', '', '', '', ''),
                       $rep);
    $words = split("[ \n\t]", $rep);
    foreach ($words as $word) {
      $biology |= in_array($word, $biologyTerms);
      $appears |= ($l->unaccented == $word);
    }
  }

  if (!$matchingLexicon && !text_contains($l->form, ' ') && $biology) {
    if ($appears) {
      print "Changing {$l->id} {$l->form} to I2\n";
      $l->modelType = 'I';
      $l->modelNumber = '2';
      $l->restriction = '';
      $l->noAccent = true;
      $l->save();
      $l->regenerateParadigm();
    } else {
      print "DELETING {$l->id} {$l->form}\n"; 
     $l->delete();
    }
    $removed++;
  }
}

print "Seen $seen lexems, removed $removed lexems.\n";

?>
