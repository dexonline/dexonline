<?
require_once("../../phplib/util.php");
ini_set('max_execution_time', '3600');
assert_options(ASSERT_BAIL, 1);
debug_off();

$mdnSrc = Source::load(21);

$query = "select Id from Definition where SourceId = 21 and Status = 1 ";
$idsToDelete = db_getScalarArray(mysql_query($query));
$count = 0;

foreach ($idsToDelete as $id) {
  assert(mysql_query("delete from Comment where DefinitionId = $id"));
  assert(mysql_query("delete from LexemDefinitionMap where DefinitionId = $id"));
  assert(mysql_query("delete from Definition where Id = $id"));
  $count++;
  if ($count % 1000 == 0) {
    print "$count definitions deleted.\n";
  }
}

print "$count definitions deleted.\n";

?>
