<?php
require_once('../../phplib/util.php');
assert_options(ASSERT_BAIL, 1);
debug_off();

$dbResult = mysql_query('select LexemId, InflectionId, count(*) as c ' .
                        'from FullTextIndex group by LexemId, InflectionId ' .
                        'order by c desc limit 50');
while (($dbRow = mysql_fetch_assoc($dbResult)) != null) {
  $lexemId = $dbRow['LexemId'];
  $inflectionId = $dbRow['InflectionId'];
  $c = $dbRow['c'];
  $wls = WordList::loadByLexemIdInflectionId($lexemId, $inflectionId);
  foreach ($wls as $i => $wl) {
    if ($i) {
      print ", ";
    }
    print $wl->form;
  }
  print ": $c occurrences\n";
}

?>
