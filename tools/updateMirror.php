<?php

require_once __DIR__ . '/../phplib/Core.php';

const DATABASE_URL = Config::STATIC_URL . 'download/mirrorAccess/dex-database.sql.gz';
const DATABASE_TMP_FILE_GZIP = Config::TEMP_DIR . '/dex-database.sql.gz';

$opts = getopt('', ['no-code', 'no-data']);
$doDatabaseCopy = !isset($opts['no-data']);
$doCodeUpdate = !isset($opts['no-code']);

Log::notice('started with databaseCopy:%s codeUpdate:%s',
            ($doDatabaseCopy ? 'yes' : 'no'),
            ($doCodeUpdate ? 'yes' : 'no'));

if ($doDatabaseCopy) {
  $wget = sprintf("wget -q -O %s %s" , DATABASE_TMP_FILE_GZIP, DATABASE_URL);
  OS::executeAndAssert($wget);
  $mysql = sprintf("zcat %s | mysql -h %s -u %s --password='%s' %s",
                   DATABASE_TMP_FILE_GZIP, DB::$host, DB::$user, DB::$password, DB::$database);
  OS::executeAndAssert($mysql);
  $rm = sprintf("rm -f %s", DATABASE_TMP_FILE_GZIP);
  OS::executeAndAssert($rm);
}

if ($doCodeUpdate) {
  OS::executeAndAssert('cd ' . Core::getRootPath() . '; /usr/bin/git pull --quiet');
}

Log::notice('finished');
