<?php

require_once("../phplib/util.php");

define('DATABASE_URL',
       'http://dexonline.ro/download/mirrorAccess/dex-database.sql.gz');

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
  OS::executeAndAssert('wget -q -O /tmp/dex-database.sql.gz ' . DATABASE_URL);
  OS::executeAndAssert('gunzip /tmp/dex-database.sql.gz');
  $parts = db_splitDsn();
  $mysql = sprintf("mysql -h %s -u %s --password='%s' %s < /tmp/dex-database.sql", $parts['host'], $parts['user'], $parts['password'], $parts['database']);
  OS::executeAndAssert($mysql);
  OS::executeAndAssert('rm -f /tmp/dex-database.sql');
}

if ($doCodeUpdate) {
  OS::executeAndAssert('cd ' . util_getRootPath() . '; /usr/bin/svn up');  
}

log_scriptLog('updateMirror.php completed successfully (against all odds)');

?>
