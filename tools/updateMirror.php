<?php

require_once __DIR__ . '/../phplib/util.php';

define('DATABASE_URL', 'http://dexonline.ro/download/mirrorAccess/dex-database.sql.gz');
define('DATABASE_TMPFILE', '/tmp/dex-database.sql');
define('DATABASE_TMPFILE_GZIP', DATABASE_TMPFILE . '.gz');

$doDatabaseCopy = true;
$doCodeUpdate = true;

for ($i = 1; $i < count($argv); $i++) {
  $arg = $argv[$i];
  if ($arg == "-nc") {
    $doCodeUpdate = false;
  } else if ($arg == '-nd') {
    $doDatabaseCopy = false;
  } else {
    OS::errorAndExit("Unknown flag: $arg");
  }
}

log_scriptLog('Running updateMirror.php with databaseCopy:' .
              ($doDatabaseCopy ? 'yes' : 'no') .
              ' codeUpdate:' .
              ($doCodeUpdate ? 'yes' : 'no'));

if ($doDatabaseCopy) {
  $wget = sprintf("wget -q -O %s %s" , DATABASE_TMPFILE_GZIP, DATABASE_URL);
  OS::executeAndAssert($wget);
  $gzip = sprintf("gunzip %s", DATABASE_TMPFILE_GZIP);
  OS::executeAndAssert($gzip);
  $parts = db_splitDsn();
  $mysql = sprintf("mysql -h %s -u %s --password='%s' %s < %s", $parts['host'], $parts['user'], $parts['password'], $parts['database'], DATABASE_TMPFILE);
  OS::executeAndAssert($mysql);
  $rm = sprintf("rm -f %s", DATABASE_TMPFILE);
  OS::executeAndAssert($rm);
}

if ($doCodeUpdate) {
  OS::executeAndAssert('cd ' . util_getRootPath() . '; /usr/bin/git pull --quiet');  
}

log_scriptLog('updateMirror.php completed successfully (against all odds)');

?>
