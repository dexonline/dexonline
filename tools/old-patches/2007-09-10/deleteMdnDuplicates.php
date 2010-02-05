<?
require_once("../../phplib/util.php");
ini_set('max_execution_time', '3600');
assert_options(ASSERT_BAIL, 1);
debug_off();

$mdnSrc = Source::load(21);

$query = "select * from Definition where SourceId = 21 and Status = 0 " .
  "order by InternalRep, Id";
$defs = Definition::populateFromDbResult(mysql_query($query));
$idsToDelete = array();

foreach ($defs as $i=>$d) {
  if ($i) {
    $prev = $defs[$i - 1];
    if (trim($d->internalRep) == trim($prev->internalRep)) {
      print "Deleting extra definition for {$prev->lexicon}\n";
      $idsToDelete[] = $d->id;
    }
  }
}

foreach ($idsToDelete as $id) {
  assert(mysql_query("delete from Comment where DefinitionId = $id"));
  assert(mysql_query("delete from LexemDefinitionMap where DefinitionId = $id"));
  assert(mysql_query("delete from Definition where Id = $id"));
}

?>
