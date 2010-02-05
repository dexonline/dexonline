<?
require_once('../../phplib/util.php');
assert_options(ASSERT_BAIL, 1);
debug_off();

// When a T1 lexems with the save form as another, non-T1 lexem, delete the
// T1 lexem.

$dbResult = mysql_query("select distinct l1.* from lexems l1, lexems l2 " .
                        "where l1.lexem_forma = l2.lexem_forma " .
                        "and l1.lexem_model_type = 'T' " .
                        "and l2.lexem_model_type != 'T'");
$fixed = 0;
while (($dbRow = mysql_fetch_assoc($dbResult)) != null) {
  $l = Lexem::createFromDbRow($dbRow);
  $homonyms = $l->loadHomonyms();
  $ldms = LexemDefinitionMap::loadByLexemId($l->id);

  print "{$l->id} ({$l->form} {$l->modelType}{$l->modelNumber}) :: ";
  foreach ($homonyms as $h) {
    print "{$h->id} ({$h->form} {$h->modelType}{$h->modelNumber}) ";
    foreach($ldms as $ldm) {
      LexemDefinitionMap::associate($h->id, $ldm->definitionId);
    }
  }
  $l->delete();
  print "\n";
  $fixed++;
}

print "Fixed $fixed lexems.\n";

?>
