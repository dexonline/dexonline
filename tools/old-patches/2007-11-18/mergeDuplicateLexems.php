<?php
require_once('../../phplib/util.php');
assert_options(ASSERT_BAIL, 1);
debug_off();

print "Finding lexem forms\n";
$query = 'select lexem_forma, lexem_model_type, lexem_model_no from lexems ' .
  'group by lexem_forma, lexem_model_type, lexem_model_no ' .
  'having count(*) > 1';
$dbResult = mysql_query($query);

while (($dbRow = mysql_fetch_assoc($dbResult)) != null) {
  $form = $dbRow['lexem_forma'];
  $modelType = $dbRow['lexem_model_type'];
  $modelNumber = $dbRow['lexem_model_no'];

  $query = sprintf("select * from lexems where lexem_forma = '%s' " .
                   "and lexem_model_type = '%s' and lexem_model_no = '%s'",
                   addslashes($form), addslashes($modelType),
                   addslashes($modelNumber));
  $lexems = Lexem::populateFromDbResult(mysql_query($query));

  // Skip cases like angstrom - angström or etate (F117) - etate (F117S)
  $allFormsEqual = true;
  foreach ($lexems as $l) {
    $allFormsEqual &= ($l->form == $lexems[0]->form &&
                       $l->extra == $lexems[0]->extra &&
                       $l->restriction == $lexems[0]->restriction);
  }
  if (!$allFormsEqual) {
    print "{$l->form} ({$l->modelType}{$l->modelNumber})\n";
    continue;
  }

  print "$form ($modelType$modelNumber)\n";
  $isLoc = false;
  foreach ($lexems as $i => $l) {
    print "  {$l->form} ({$l->modelType}{$l->modelNumber}) " .
      "D:[{$l->description}] " .
      "R:[{$l->restriction}] " .
      "E:[{$l->extra}] " .
      "P:[{$l->parseInfo}] " .
      "C:[{$l->comment}] " .
      "A:[{$l->noAccent}]\n";
    if ($l->form != $lexems[0]->form ||
        $l->unaccented != $lexems[0]->unaccented ||
        $l->reverse != $lexems[0]->reverse ||
        $l->description != $lexems[0]->description ||
        $l->modelType != $lexems[0]->modelType ||
        $l->modelNumber != $lexems[0]->modelNumber ||
        $l->restriction != $lexems[0]->restriction ||
        $l->extra != $lexems[0]->extra ||
        $l->parseInfo != $lexems[0]->parseInfo||
        $l->comment != $lexems[0]->comment ||
        $l->noAccent != $lexems[0]->noAccent) {
      die("Difference! ***********************************************\n");
    }
    $ldms = LexemDefinitionMap::loadByLexemId($l->id);
    foreach ($ldms as $ldm) {
      LexemDefinitionMap::associate($lexems[0]->id, $ldm->definitionId);
    }
    $isLoc |= $l->isLoc;

    if ($i) {
      $l->delete();
    }
  }
  if ($isLoc != $lexems[0]->isLoc) {
    $lexems[0]->isLoc = $isLoc;
    $lexems[0]->save();
  }
}

?>
