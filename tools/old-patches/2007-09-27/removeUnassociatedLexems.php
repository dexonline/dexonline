<?php
require_once("../../phplib/util.php");
assert_options(ASSERT_BAIL, 1);
debug_off();

$query = 'select lexems.* from lexems left outer join LexemDefinitionMap ' .
  'on lexem_id = LexemId where not lexem_is_loc and Id is null';
$dbResult = mysql_query($query);
$count = mysql_num_rows($dbResult);

while ($dbRow = mysql_fetch_assoc($dbResult)) {
  $l = Lexem::createFromDbRow($dbRow);
  print $l->getExtendedName() . "\n";
  $l->delete();
}

print "Deleted $count lexems\n";

?>
