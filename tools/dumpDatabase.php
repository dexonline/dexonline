<?

require_once("../phplib/util.php");

$TMP_DIR = '/tmp';
$FILENAME = 'dex-database.sql';
$GZ_FILENAME = 'dex-database.sql.gz';
$LICENSE = util_getRootPath() . '/tools/dumpDatabaseLicense.txt';

$parts = db_splitDsn();
$COMMON_COMMAND = sprintf("mysqldump -h %s -u %s --password='%s' %s ", $parts['host'], $parts['user'], $parts['password'], $parts['database']);

$schemaOnly = array('RecentLink', 'Cookie', 'history_Comment', 'history_Definition');
$alteredFields = array('User' => array('Email' => 'anonymous@anonymous.com',
                                       'Password' => ''));
$currentYear = date("Y");

// Full/Public dump: the public dump omits the user table, which contains
// emails and md5-ed passwords.
$doFullDump = false;

for ($i = 1; $i < count($argv); $i++) {
  $arg = $argv[$i];
  if ($arg == "--full") {
    $doFullDump = true;
  } else if ($arg == '--public') {
    $doFullDump = false;
  } else {
    os_errorAndExit("Unknown flag: $arg");
  }
}

log_scriptLog('Running dumpDatabase.php with argument ' .
              ($doFullDump ? 'full' : 'public'));

$dbName = $parts['database'];
$tablesToIgnore = '';
foreach ($schemaOnly as $table) {
  $tablesToIgnore .= "--ignore-table=$dbName.$table ";
}
if ($doFullDump) {
  $targetDir = util_getRootPath() . '/wwwbase/download/mirrorAccess/';
} else {
  $targetDir = util_getRootPath() . '/wwwbase/download/';
  foreach ($alteredFields as $table => $fields) {
    $tablesToIgnore .= "--ignore-table=$dbName.$table ";
  }
}

os_executeAndAssert("rm -f $TMP_DIR/$FILENAME");
os_executeAndAssert("echo \"-- Copyright (C) 2004-$currentYear DEX online (http://dexonline.ro)\" > $TMP_DIR/$FILENAME");
os_executeAndAssert("cat $LICENSE >> $TMP_DIR/$FILENAME");
$mysql = "$COMMON_COMMAND $tablesToIgnore >> $TMP_DIR/$FILENAME";
os_executeAndAssert($mysql);

// Dump only the schema for some tables
$command = "$COMMON_COMMAND --no-data";
foreach ($schemaOnly as $table) {
  $command .= " $table";
}
$command .= " >> $TMP_DIR/$FILENAME";
os_executeAndAssert($command);

// Alter some fields for other tables
if (!$doFullDump) {
  foreach ($alteredFields as $table => $fieldArray) {
    $copyTable = "_{$table}_Copy";
    mysql_query("create table $copyTable select * from $table");
    foreach ($fieldArray as $field => $value) {
      mysql_query("update $copyTable set $field = '$value'");
    }
    os_executeAndAssert("$COMMON_COMMAND $copyTable " .
                        "| sed 's/$copyTable/$table/g' " .
                        ">> $TMP_DIR/$FILENAME");
    mysql_query("drop table $copyTable");
  }
}

os_executeAndAssert("gzip -f $TMP_DIR/$FILENAME");
os_executeAndAssert("rm -f $targetDir/$GZ_FILENAME");
os_executeAndAssert("mv $TMP_DIR/$GZ_FILENAME $targetDir");
os_executeAndAssert("chmod 644 $targetDir/$GZ_FILENAME");

log_scriptLog('dumpDatabase.php completed successfully (against all odds)');

?>
