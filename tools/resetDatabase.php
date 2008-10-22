<?php

require_once("../phplib/util.php");

$database = pref_getDbDatabase();
$user = pref_getDbUser();
$password = pref_getDbPassword();
$host = pref_getDbHost();

if (!strcasecmp($database, 'DEX')) {
  echo "ERROR: Database $database might be the production one! Aborting.\n";
  exit;
}

mysql_connect($host, $user, $password);
mysql_query('set names utf8');

echo "INFO: Dropping database $database on host $host\n";
$drop_result = mysql_query("drop database $database");
if (!$drop_result) {
  echo "WARNING: Could not drop database $database, " .
    "assuming it's already gone.\n";
}

echo "INFO: Creating database $database on host $host\n";
$create_result = mysql_query("create database $database");
if (!$create_result) {
  echo "ERROR: Could not create database $database. Aborting.\n";
}

$command_opt = "-u".escapeshellarg($user);
if ($password) {
    $command_opt .= " -p".escapeshellarg($password);
}
$command = "mysql $command_opt ".escapeshellarg($database)." < schema.sql";
echo "INFO: Creating schema ($command)\n";
$exit_code = 0;
$output = null;
exec($command, $output, $exit_code);
if ($exit_code) {
  echo "ERROR: Could not create schema (exit code $exit_code). Aborting.\n";
}

$command = "mysql -u$user $database < sampleData.sql";
echo "INFO: Populating sample data ($command)\n";
exec($command, $output, $exit_code);
if ($exit_code) {
  echo "ERROR: Could not populate data (exit code $exit_code). Aborting.\n";
}

?>
