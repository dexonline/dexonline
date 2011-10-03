<?php
require_once('../../phplib/util.php');
assert_options(ASSERT_BAIL, 1);
debug_off();

// First remove all restrictions from 'T' lesems
mysql_query('update lexems set lexem_restriction = "" ' .
            'where lexem_model_type = "T"');

$query = "select * from lexems where lexem_id not in " .
  "(select wl_lexem from wordlist)";
$dbResult = mysql_query($query);

while (($dbRow = mysql_fetch_assoc($dbResult)) != null) {
  $l = Lexem::createFromDbRow($dbRow);
  print "{$l->id} {$l->form}\n";
  $l->regenerateParadigm();
}

?>
