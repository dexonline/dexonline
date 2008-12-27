<?
require_once("../phplib/util.php");

log_scriptLog('Running rebuildFirefoxSpellChecker.php.');
$tmpDir = tempnam('/tmp', 'xpi_');
log_scriptLog('Setting up directories');
os_executeAndAssert("rm $tmpDir");
os_executeAndAssert("mkdir $tmpDir");
os_executeAndAssert("mkdir $tmpDir/chrome");
os_executeAndAssert("mkdir $tmpDir/dictionaries");
os_executeAndAssert("echo 'SET ISO8859-2' > $tmpDir/dictionaries/ro-dex.aff");
os_executeAndAssert("cp ../docs/install.rdf $tmpDir/");

$mysqlFile = tempnam('/tmp', 'mysql_');
os_executeAndAssert("rm $mysqlFile");
$query = "select distinct wl_neaccentuat from wordlist " .
  "where wl_neaccentuat rlike '^[a-zăâîşţ]+$' " .
  "into outfile '$mysqlFile'";
log_scriptLog("Running mysql query: [$query]");
mysql_query($query);

log_scriptLog("Converting to ISO-8859-2");
os_executeAndAssert("wc -l $mysqlFile | cut -d ' ' -f 1 " .
                    "> $tmpDir/dictionaries/ro-dex.dic");
os_executeAndAssert("iconv -f UTF8 -t ISO_8859-2 $mysqlFile " .
                    ">> $tmpDir/dictionaries/ro-dex.dic");

log_scriptLog("Zipping");
os_executeAndAssert("cd $tmpDir && zip -r dex-ff.xpi *");
os_executeAndAssert("cp -f $tmpDir/dex-ff.xpi ../wwwbase/download/");

os_executeAndAssert("rm -rf $tmpDir");
log_scriptLog('rebuildFirefoxSpellChecker.php completed successfully (against all odds)');

?>
