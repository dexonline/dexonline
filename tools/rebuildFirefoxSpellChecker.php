<?php
require_once("../phplib/util.php");

log_scriptLog('Running rebuildFirefoxSpellChecker.php.');
$tmpDir = tempnam('/tmp', 'xpi_');
log_scriptLog('Setting up directories');
OS::executeAndAssert("rm $tmpDir");
OS::executeAndAssert("mkdir $tmpDir");
OS::executeAndAssert("mkdir $tmpDir/chrome");
OS::executeAndAssert("mkdir $tmpDir/dictionaries");
OS::executeAndAssert("echo 'SET UTF-8' > $tmpDir/dictionaries/ro-dex.aff");
OS::executeAndAssert("cp ../docs/install.rdf $tmpDir/");

$mysqlFile = tempnam('/tmp', 'mysql_');
$query = "select distinct formNoAccent from InflectedForm " .
  "where formNoAccent rlike '^[a-zăâîșț]+$' " .
  "into outfile '$mysqlFile'";
log_scriptLog("Running mysql query: [$query]");
mysql_query($query);

log_scriptLog("Prepending line count");
OS::executeAndAssert("wc -l $mysqlFile | cut -d ' ' -f 1 > $tmpDir/dictionaries/ro-dex.dic");
OS::executeAndAssert("cat $mysqlFile >> $tmpDir/dictionaries/ro-dex.dic");

log_scriptLog("Zipping");
OS::executeAndAssert("cd $tmpDir && zip -r dex-ff.xpi *");
OS::executeAndAssert("cp -f $tmpDir/dex-ff.xpi ../wwwbase/download/");

OS::executeAndAssert("rm -rf $tmpDir");
log_scriptLog('rebuildFirefoxSpellChecker.php completed successfully (against all odds)');

?>
