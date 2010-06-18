<?
require_once("../phplib/util.php");

log_scriptLog('Running rebuildFirefoxSpellChecker.php.');
$tmpDir = tempnam('/tmp', 'xpi_');
log_scriptLog('Setting up directories');
os_executeAndAssert("rm $tmpDir");
os_executeAndAssert("mkdir $tmpDir");
os_executeAndAssert("mkdir $tmpDir/chrome");
os_executeAndAssert("mkdir $tmpDir/dictionaries");
os_executeAndAssert("echo 'SET UTF-8' > $tmpDir/dictionaries/ro-dex.aff");
os_executeAndAssert("cp ../docs/install.rdf $tmpDir/");

$mysqlFile = tempnam('/tmp', 'mysql_');
$query = "select distinct formNoAccent from InflectedForm " .
  "where formNoAccent rlike '^[a-zăâîșț]+$' " .
  "into outfile '$mysqlFile'";
log_scriptLog("Running mysql query: [$query]");
mysql_query($query);

log_scriptLog("Prepending line count");
os_executeAndAssert("wc -l $mysqlFile | cut -d ' ' -f 1 > $tmpDir/dictionaries/ro-dex.dic");
os_executeAndAssert("cat $mysqlFile >> $tmpDir/dictionaries/ro-dex.dic");

log_scriptLog("Zipping");
os_executeAndAssert("cd $tmpDir && zip -r dex-ff.xpi *");
os_executeAndAssert("cp -f $tmpDir/dex-ff.xpi ../wwwbase/download/");

os_executeAndAssert("rm -rf $tmpDir");
log_scriptLog('rebuildFirefoxSpellChecker.php completed successfully (against all odds)');

?>
