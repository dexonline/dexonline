<?php
require_once('../../phplib/util.php');
assert_options(ASSERT_BAIL, 1);
debug_off();

$stopWords = array('de', 'dea', 'o', 'pe', 'în');

$dbResult = mysql_query("select * from lexems where lexem_forma rlike ' '");
$seen = 0;

while (($dbRow = mysql_fetch_assoc($dbResult)) != null) {
  $l = Lexem::createFromDbRow($dbRow);
  $seen++;

  $defs = Definition::loadByLexemId($l->id);

  if (count($defs)) {
    $parts = split(' ', $l->form);
    print text_padRight($l->form, 30);
    foreach ($parts as $part) {
      $part = trim($part);
      if (!$part || in_array($part, $stopWords)) {
        // Skip common words
        continue;
      }
      print '[';
      $baseForms = Lexem::searchWordlists($part, true);
      if (!count($baseForms)) {
        $baseForm = Lexem::create($part, 'T', '1', '');
        $baseForm->comment = "Provine din despărțirea lexemului [{$l->form}]";
        $baseForm->noAccent = true;
        $baseForm->save();
        $baseForm->id = db_getLastInsertedId();
        $baseForm->regenerateParadigm();
        $baseForms[] = $baseForm;
      }
      // Associate every definition with every lexem
      foreach ($baseForms as $baseForm) {
        print $baseForm->form . ' ';
        foreach ($defs as $def) {
          LexemDefinitionMap::associate($baseForm->id, $def->id);
        }
      }
      print ']';
    }
    print "\n";
  }
  $l->delete();
}

print "Seen $seen lexems.\n";

?>
