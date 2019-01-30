<?php

require_once __DIR__ . '/../phplib/Core.php';

$databaseUrl = Config::get('static.url') . 'download/mirrorAccess/dex-database.sql.gz';
$databaseTmpFileGzip = Config::get('global.tempDir') . '/dex-database.sql.gz';

$opts = getopt('', ['no-code', 'no-data']);
$doDatabaseCopy = !isset($opts['no-data']);
$doCodeUpdate = !isset($opts['no-code']);

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
