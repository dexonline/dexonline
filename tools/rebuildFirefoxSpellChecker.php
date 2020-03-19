<?php
require_once __DIR__ . '/../lib/Core.php';

Log::notice('started');
$tmpDir = tempnam(Config::TEMP_DIR, 'xpi_');
Log::info('Setting up directories');
chdir(Config::ROOT);
OS::executeAndAssert("rm $tmpDir");
OS::executeAndAssert("mkdir $tmpDir");
OS::executeAndAssert("echo 'SET UTF-8' > $tmpDir/ro-dex.aff");
OS::executeAndAssert("cp docs/manifest.json $tmpDir/");

$mysqlFile = tempnam(Config::TEMP_DIR, 'mysql_');
unlink($mysqlFile);
$query = "select distinct formNoAccent from InflectedForm where formNoAccent rlike '^[a-zăâîșț]+$' into outfile '$mysqlFile'";
Log::info("Running mysql query: [$query]");
DB::execute($query);

Log::info("Prepending line count");
OS::executeAndAssert("wc -l $mysqlFile | cut -d ' ' -f 1 > $tmpDir/ro-dex.dic");
OS::executeAndAssert("cat $mysqlFile >> $tmpDir/ro-dex.dic");

Log::info("Zipping");
OS::executeAndAssert("cd $tmpDir && zip -r dex-ff.xpi *");
StaticUtil::move("$tmpDir/dex-ff.xpi", 'download/dex-ff.xpi');

OS::executeAndAssert("rm -rf $tmpDir");
Log::notice('finished');

// Note -- this leaves behind the temporary MySQL file created by "... into outfile...".
// The file is owned by mysql so we cannot delete it.
