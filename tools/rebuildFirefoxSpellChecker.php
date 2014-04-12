<?php
require_once __DIR__ . '/../phplib/util.php';

log_scriptLog('Running rebuildFirefoxSpellChecker.php.');
$tmpDir = tempnam('/tmp', 'xpi_');
log_scriptLog('Setting up directories');
chdir(util_getRootPath());
OS::executeAndAssert("rm $tmpDir");
OS::executeAndAssert("mkdir $tmpDir");
OS::executeAndAssert("mkdir $tmpDir/chrome");
OS::executeAndAssert("mkdir $tmpDir/dictionaries");
OS::executeAndAssert("echo 'SET UTF-8' > $tmpDir/dictionaries/ro-dex.aff");
OS::executeAndAssert("cp docs/install.rdf $tmpDir/");

$mysqlFile = tempnam('/tmp', 'mysql_');
unlink($mysqlFile);
$query = "select distinct formNoAccent from InflectedForm where formNoAccent rlike '^[a-zăâîșț]+$' into outfile '$mysqlFile'";
log_scriptLog("Running mysql query: [$query]");
db_execute($query);

log_scriptLog("Prepending line count");
OS::executeAndAssert("wc -l $mysqlFile | cut -d ' ' -f 1 > $tmpDir/dictionaries/ro-dex.dic");
OS::executeAndAssert("cat $mysqlFile >> $tmpDir/dictionaries/ro-dex.dic");

log_scriptLog("Zipping");
OS::executeAndAssert("cd $tmpDir && zip -r dex-ff.xpi *");
FtpUtil::staticServerPut("$tmpDir/dex-ff.xpi", '/download/dex-ff.xpi');

OS::executeAndAssert("rm -rf $tmpDir");
log_scriptLog('rebuildFirefoxSpellChecker.php completed successfully (against all odds)');

// Note -- this leaves behind the temporary MySQL file created by "... into outfile...".
// The file is owned by mysql so we cannot delete it.
?>
