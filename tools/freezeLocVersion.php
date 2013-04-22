<?php
require_once __DIR__ . '/../phplib/util.php';

$lvs = array_reverse(pref_getLocVersions());
if (count($lvs) < 2) {
  die("ERROR: You need at least two LOC versions in dex.conf: " .
      "one that indicates the version to be frozen and " .
      "one to indicate the next current version.\n");
}

$locDbPrefix = pref_getLocPrefix();
if (!$locDbPrefix) {
  die("ERROR: You forgot to define mysql_loc_prefix in dex.conf.\n");
}

// Assert that all the already frozen versions exist.
for ($i = 0; $i < count($lvs) - 2; $i++) {
  print "Asserting that version {$lvs[$i]->name} exists.\n";
  $dbName = $locDbPrefix . $lvs[$i]->getDbName();
  if (!databaseExists($dbName)) {
    die("ERROR: Database $dbName for version {$lvs[$i]->name} " .
	"does not exist.\n");
  }
  if (!$lvs[$i]->freezeTimestamp) {
    die("ERROR: Version {$lvs[$i]->name} should have a freeze date\n");
  }
}

// Assert that the next-to-last version does not yet exist.
$lvToFreeze = $lvs[count($lvs) - 2];
print "Asserting that version {$lvToFreeze->name} does not exist.\n";
$dbName = $locDbPrefix . $lvToFreeze->getDbName();
if (databaseExists($dbName)) {
  die("ERROR: Database $dbName for version {$lvToFreeze->name} " .
      "should not exist.\n");
}
if (!$lvToFreeze->freezeTimestamp) {
  die("ERROR: Version {$lvToFreeze->name} should have a freeze date\n");
}

// Assert that the last version is the new current version.
$currentLv = $lvs[count($lvs) - 1];
print "Asserting that version {$currentLv->name} does not exist.\n";
$dbName = $locDbPrefix . $currentLv->getDbName();
if (databaseExists($dbName)) {
  die("ERROR: Database $dbName for version {$currentLv->name} " .
      "should not exist.\n");
}
if ($currentLv->freezeTimestamp) {
  die("ERROR: Version {$currentLv->name} should not have a freeze date\n");
}

// Now create a database for $lvToFreeze and copy the relevant tables there.
$dbName = $locDbPrefix . $lvToFreeze->getDbName();
print "Creating database $dbName\n";
db_execute("create database $dbName");
$fileName = tempnam('/tmp', 'freeze_');
print "Dumping tables to $fileName\n";
$tablesToDump = "ConstraintMap Inflection Lexem ModelDescription ModelType Model ParticipleModel Transform InflectedForm";
$parts = db_splitDsn();
$mysql = sprintf("mysqldump -h %s -u %s --password='%s' %s %s > %s", $parts['host'], $parts['user'], $parts['password'], $parts['database'], $tablesToDump, $fileName);
OS::executeAndAssert($mysql);
print "Importing $fileName to $dbName\n";
$import = sprintf("mysql -h %s -u %s --password='%s' %s < %s", $parts['host'], $parts['user'], $parts['password'], $dbName, $fileName);
OS::executeAndAssert($import);
print "Success!\n";

/****************************************************************************/

function databaseExists($dbName) {
  $r = ORM::for_table('Definition')->raw_query("show databases like '$dbName'", null)->find_one();
  return ($r !== false);
}

?>
