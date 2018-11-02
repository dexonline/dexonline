<?php
require_once __DIR__ . '/../phplib/Core.php';

// these tables will be dumped with data; the rest will have only the schema
const TABLES_TO_DUMP = [
  'ConstraintMap',
  'Entry',
  'EntryLexeme',
  'InflectedForm',
  'Inflection',
  'Lexeme',
  'LexemeSource',
  'Model',
  'ModelDescription',
  'ModelType',
  'OrthographicReforms',
  'PageIndex',
  'ParticipleModel',
  'Source',
  'SourceType',
  'TraineeSource',
  'Transform',
  'Tree',
  'TreeEntry',
  'Variable',
];

$lvs = array_reverse(Config::getLocVersions());
if (count($lvs) < 2) {
  die("ERROR: You need at least two LOC versions in dex.conf: " .
      "one that indicates the version to be frozen and " .
      "one to indicate the next current version.\n");
}

$locDbPrefix = Config::get('global.mysql_loc_prefix');
if (!$locDbPrefix) {
  die("ERROR: You forgot to define mysql_loc_prefix in dex.conf.\n");
}

// assert that all the already frozen versions exist
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

// assert that the next-to-last version does not yet exist
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

// assert that the last version is the new current version
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

// create a database for $lvToFreeze
$dbName = $locDbPrefix . $lvToFreeze->getDbName();
print "Creating database $dbName\n";
DB::execute("create database $dbName");

// dump database schema
$fileName = tempnam(Config::get('global.tempDir'), 'freeze_');
$mysql = sprintf(
  "mysqldump -h %s -u %s --password='%s' --no-data %s > %s",
  DB::$host,
  DB::$user,
  DB::$password,
  DB::$database,
  $fileName);
print "Dumping schema to $fileName: $mysql\n";
OS::executeAndAssert($mysql);

// dump the data for some tables
$mysql = sprintf(
  "mysqldump -h %s -u %s --password='%s' %s %s >> %s",
  DB::$host,
  DB::$user,
  DB::$password,
  DB::$database,
  implode(' ', TABLES_TO_DUMP),
  $fileName);
print "Dumping data to $fileName: $mysql\n";
OS::executeAndAssert($mysql);

// import the data into the new database
$mysql = sprintf(
  "mysql -h %s -u %s --password='%s' %s < %s",
  DB::$host,
  DB::$user,
  DB::$password,
  $dbName,
  $fileName);
print "Importing $fileName to $dbName: $mysql\n";
OS::executeAndAssert($mysql);

print "Success!\n";

/****************************************************************************/

function databaseExists($dbName) {
  $r = ORM::for_table('Definition')
    ->raw_query("show databases like '$dbName'")
    ->find_one();
  return ($r !== false);
}
