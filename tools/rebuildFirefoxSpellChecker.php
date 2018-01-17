<?php
require_once __DIR__ . '/../phplib/Core.php';

Log::notice('started');
$tmpDir = tempnam(Config::get('global.tempDir'), 'xpi_');
Log::info('Setting up directories');
chdir(Core::getRootPath());
OS::executeAndAssert("rm $tmpDir");
OS::executeAndAssert("mkdir $tmpDir");
OS::executeAndAssert("mkdir $tmpDir/chrome");
OS::executeAndAssert("mkdir $tmpDir/dictionaries");
OS::executeAndAssert("echo 'SET UTF-8' > $tmpDir/dictionaries/ro-dex.aff");
OS::executeAndAssert("cp docs/install.rdf $tmpDir/");

$mysqlFile = tempnam(Config::get('global.tempDir'), 'mysql_');
unlink($mysqlFile);
$query = "select distinct formNoAccent from InflectedForm where formNoAccent rlike '^[a-zăâîșț]+$' into outfile '$mysqlFile'";
Log::info("Running mysql query: [$query]");
DB::execute($query);

Log::info("Prepending line count");
OS::executeAndAssert("wc -l $mysqlFile | cut -d ' ' -f 1 > $tmpDir/dictionaries/ro-dex.dic");
OS::executeAndAssert("cat $mysqlFile >> $tmpDir/dictionaries/ro-dex.dic");

Log::info("Zipping");
OS::executeAndAssert("cd $tmpDir && zip -r dex-ff.xpi *");
$f = new FtpUtil();
$f->staticServerPut("$tmpDir/dex-ff.xpi", '/download/dex-ff.xpi');

OS::executeAndAssert("rm -rf $tmpDir");
Log::notice('finished');

// Note -- this leaves behind the temporary MySQL file created by "... into outfile...".
// The file is owned by mysql so we cannot delete it.
