<?php
require_once("../../phplib/util.php");
ini_set('max_execution_time', '3600');
assert_options(ASSERT_BAIL, 1);
debug_off();

mysql_query('delete from wordlist');

$count = 0;

$dbResult = mysql_query('select * from lexems order by lexem_id');
while ($dbRow = mysql_fetch_assoc($dbResult)) {
  $l = Lexem::createFromDbRow($dbRow);
  //print "{$l->form} {$l->modelType} {$l->modelNumber} {$l->restriction}\n";
  $l->regenerateParadigm();
  $count++;
  if ($count % 1000 == 0) {
    $runTime = debug_getRunningTimeInMillis() / 1000;
    print $count . " lexems, " . $count / $runTime . " lexems/sec\n";
  }
}

?>
