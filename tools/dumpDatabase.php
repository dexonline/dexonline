<?php

require_once("../phplib/util.php");

$TMP_DIR = '/tmp';
$FILENAME = 'dex-database.sql';
$GZ_FILENAME = 'dex-database.sql.gz';
$LICENSE = util_getRootPath() . '/tools/dumpDatabaseLicense.txt';

$parts = db_splitDsn();
$COMMON_COMMAND = sprintf("mysqldump -h %s -u %s --password='%s' %s ", $parts['host'], $parts['user'], $parts['password'], $parts['database']);

$schemaOnly = array('RecentLink', 'Cookie', 'UserWordBookmark', 'DefinitionSimple', 'history_Comment', 'history_Definition');
$currentYear = date("Y");

// Full/Public dump: the public dump omits the user table, which contains emails and md5-ed passwords.
$doFullDump = false;

for ($i = 1; $i < count($argv); $i++) {
  $arg = $argv[$i];
  if ($arg == "--full") {
    $doFullDump = true;
  } else if ($arg == '--public') {
    $doFullDump = false;
  } else {
    OS::errorAndExit("Unknown flag: $arg");
  }
}

log_scriptLog('Running dumpDatabase.php with argument ' . ($doFullDump ? 'full' : 'public'));

$dbName = $parts['database'];
$tablesToIgnore = '';
foreach ($schemaOnly as $table) {
  $tablesToIgnore .= "--ignore-table=$dbName.$table ";
}
if ($doFullDump) {
  $targetDir = util_getRootPath() . '/wwwbase/download/mirrorAccess/';
} else {
  $targetDir = util_getRootPath() . '/wwwbase/download/';
  $tablesToIgnore .= "--ignore-table=$dbName.User --ignore-table=$dbName.Definition --ignore-table=$dbName.diverta_Book --ignore-table=$dbName.divertaIndex ";
}

OS::executeAndAssert("rm -f $TMP_DIR/$FILENAME");
OS::executeAndAssert("echo \"-- Copyright (C) 2004-$currentYear DEX online (http://dexonline.ro)\" > $TMP_DIR/$FILENAME");
OS::executeAndAssert("cat $LICENSE >> $TMP_DIR/$FILENAME");
$mysql = "$COMMON_COMMAND $tablesToIgnore >> $TMP_DIR/$FILENAME";
OS::executeAndAssert($mysql);

// Dump only the schema for some tables
$command = "$COMMON_COMMAND --no-data";
foreach ($schemaOnly as $table) {
  $command .= " $table";
}
$command .= " >> $TMP_DIR/$FILENAME";
OS::executeAndAssert($command);

if (!$doFullDump) {
  // Anonymize the User table. Handle the case for id = 0 separately, since
  // "insert into _User_Copy set id = 0" doesn't work (it inserts an id of 1).
  log_scriptLog('Anonymizing the User table');
  db_execute("create table _User_Copy like User");
  db_execute("insert into _User_Copy select * from User where id = 0");
  db_execute("update _User_Copy set id = 0 where id = 1");
  db_execute("insert into _User_Copy select * from User where id > 0");
  db_execute("update _User_Copy set password = md5('1234'), email = concat(id, '@anonymous.com'), identity = null");
  OS::executeAndAssert("$COMMON_COMMAND _User_Copy | sed 's/_User_Copy/User/g' >> $TMP_DIR/$FILENAME");
  db_execute("drop table _User_Copy");

  // Dump only the Definitions for which we have redistribution rights
  log_scriptLog('Filtering the Definition table');
  OS::executeAndAssert("$COMMON_COMMAND Definition --lock-all-tables --where='Definition.sourceId in (select id from Source where canDistribute)' " .
                      ">> $TMP_DIR/$FILENAME");
}

OS::executeAndAssert("gzip -f $TMP_DIR/$FILENAME");
OS::executeAndAssert("rm -f $targetDir/$GZ_FILENAME");
OS::executeAndAssert("mv $TMP_DIR/$GZ_FILENAME $targetDir");
OS::executeAndAssert("chmod 644 $targetDir/$GZ_FILENAME");

log_scriptLog('dumpDatabase.php completed successfully (against all odds)');

?>
