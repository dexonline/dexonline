<?
// Now that a Concept maps several Words to several Definitions, we
// can no longer use the first Word to compute the Lexicon field. We have to
// extract it from the definition.
require_once("../../phplib/util.php");
ini_set('max_execution_time', '3600');

$dbResult = mysql_query("select * from Definition");
$numRows = mysql_num_rows($dbResult);
$i = 0;

while ($dbRow = mysql_fetch_assoc($dbResult)) {
  $def = new Definition();
  $def->populateFromDbRow($dbRow);
  
  $def->lexicon = text_extractLexicon($def);
  // write a custom query so we don't update the ModDate field (also for speed)
  mysql_query(sprintf("update Definition set Lexicon = '%s' where Id = '%s'",
                      addslashes($def->lexicon), $def->id));
  $i++;
  if ($i % 1000 == 0) {
    print "$i/$numRows definitions processed.\n";
  }
}
mysql_free_result($dbResult);

?>
