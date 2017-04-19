<?php

require_once __DIR__ . '/../phplib/util.php';

$SQL_FILE = Config::get('global.tempDir') . '/dex-database.sql';
$GZ_FILE = Config::get('global.tempDir') . '/dex-database.sql.gz';
$LICENSE = util_getRootPath() . '/tools/dumpDatabaseLicense.txt';

$parts = DB::splitDsn();
  // Skip the username/password here to avoid a Percona warning.
  // Place them in my.cnf.
$COMMON_COMMAND = sprintf("mysqldump -h %s %s ", $parts['host'], $parts['database']);

$schemaOnly = [
  'AccuracyProject',
  'AccuracyRecord',
  'Cookie',
  'DefinitionSimple',
  'Donation',
  'RecentLink',
  'UserWordBookmark',
  'history_Comment',
  'history_Definition',
];
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

Log::notice('started with argument %s', ($doFullDump ? 'full' : 'public'));

$dbName = $parts['database'];
$tablesToIgnore = '';
foreach ($schemaOnly as $table) {
  $tablesToIgnore .= "--ignore-table=$dbName.$table ";
}
if ($doFullDump) {
  $remoteFile = '/download/mirrorAccess/dex-database.sql.gz';
} else {
  $remoteFile = '/download/dex-database.sql.gz';
  $tablesToIgnore .= "--ignore-table=$dbName.User --ignore-table=$dbName.Definition --ignore-table=$dbName.Source --ignore-table=$dbName.diverta_Book --ignore-table=$dbName.divertaIndex ";
}

OS::executeAndAssert("rm -f $SQL_FILE");
OS::executeAndAssert("echo \"-- Copyright (C) 2004-$currentYear dexonline (https://dexonline.ro)\" > $SQL_FILE");
OS::executeAndAssert("cat $LICENSE >> $SQL_FILE");
$mysql = "$COMMON_COMMAND $tablesToIgnore >> $SQL_FILE";
OS::executeAndAssert($mysql);

// Dump only the schema for some tables
$command = "$COMMON_COMMAND --no-data";
foreach ($schemaOnly as $table) {
  $command .= " $table";
}
$command .= " >> $SQL_FILE";
OS::executeAndAssert($command);

if (!$doFullDump) {
  // Anonymize the User table. Handle the case for id = 0 separately, since
  // "insert into _User_Copy set id = 0" doesn't work (it inserts an id of 1).
  Log::info('Anonymizing the User table');
  DB::execute("create table _User_Copy like User");
  DB::execute("insert into _User_Copy select * from User where id = 0");
  DB::execute("update _User_Copy set id = 0 where id = 1");
  DB::execute("insert into _User_Copy select * from User where id > 0");
  DB::execute("update _User_Copy set password = md5('1234'), email = concat(id, '@anonymous.com'), identity = null");
  OS::executeAndAssert("$COMMON_COMMAND _User_Copy | sed 's/_User_Copy/User/g' >> $SQL_FILE");
  DB::execute("drop table _User_Copy");

  Log::info('Anonymizing the Source table');
  DB::execute("create table _Source_Copy like Source");
  DB::execute("insert into _Source_Copy select * from Source");
  DB::execute("update _Source_Copy set link = null");
  OS::executeAndAssert("$COMMON_COMMAND _Source_Copy | sed 's/_Source_Copy/Source/g' >> $SQL_FILE");
  DB::execute("drop table _Source_Copy");

  // Dump only the Definitions for which we have redistribution rights
  Log::info('Filtering the Definition table');
  DB::execute("create table _Definition_Copy like Definition");
  DB::execute("insert into _Definition_Copy select * from Definition");
  $query = <<<EOT
    update _Definition_Copy
    set internalRep = concat(left(internalRep, 20), '...'), 
        htmlRep = '[această definiție nu poate fi redistribuită]' 
    where sourceId in (select id from Source where !canDistribute)
EOT;
  DB::execute($query);
  OS::executeAndAssert("$COMMON_COMMAND _Definition_Copy | sed 's/_Definition_Copy/Definition/g' >> $SQL_FILE");
  DB::execute("drop table _Definition_Copy");
}

OS::executeAndAssert("gzip -f $SQL_FILE");
$f = new FtpUtil();
$f->staticServerPut($GZ_FILE, $remoteFile);
unlink($GZ_FILE);

Log::notice('finished');

?>
