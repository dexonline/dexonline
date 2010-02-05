<?
require_once("../../phplib/util.php");
ini_set('max_execution_time', '3600');
assert_options(ASSERT_BAIL, 1);
debug_off();

$mdnSrc = Source::load(21);

$query = "select Id, md5(InternalRep) as m from Definition " .
  "where Status = 0 order by m, Id";
$dbResult = mysql_query($query);

$prevId = 0;
$prevMd5 = 'xyz';
while ($dbRow = mysql_fetch_assoc($dbResult)) {
  $id = $dbRow['Id'];
  $md5 = $dbRow['m'];

  if ($md5 == $prevMd5) {
    print "Definition $id is identical to $prevId\n";
    assert(mysql_query("delete from Comment where DefinitionId = $id"));
    assert(mysql_query("delete from LexemDefinitionMap where DefinitionId = $id"));
    assert(mysql_query("delete from Definition where Id = $id"));
  } else {
    $prevId = $id;
    $prevMd5 = $md5;
  }
}

?>
