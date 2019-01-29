<?php

require_once __DIR__ . '/../phplib/Core.php';

$databaseUrl = Config::get('static.url') . 'download/mirrorAccess/dex-database.sql.gz';
$databaseTmpFileGzip = Config::get('global.tempDir') . '/dex-database.sql.gz';

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

Log::notice('started with databaseCopy:%s codeUpdate:%s',
            ($doDatabaseCopy ? 'yes' : 'no'),
            ($doCodeUpdate ? 'yes' : 'no'));

if ($doDatabaseCopy) {
  $wget = sprintf("wget -q -O %s %s" , $databaseTmpFileGzip, $databaseUrl);
  OS::executeAndAssert($wget);
  $mysql = sprintf("zcat %s | mysql -h %s -u %s --password='%s' %s",
                   $databaseTmpFileGzip, DB::$host, DB::$user, DB::$password, DB::$database);
  OS::executeAndAssert($mysql);
  $rm = sprintf("rm -f %s", $databaseTmpFileGzip);
  OS::executeAndAssert($rm);
}

if ($doCodeUpdate) {
  OS::executeAndAssert('cd ' . Core::getRootPath() . '; /usr/bin/git pull --quiet');
}

Log::notice('finished');
