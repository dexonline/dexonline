<?php

require_once __DIR__ . '/../phplib/util.php';

// Make sure we are in testing mode.
Config::get('testing.enabled')
  or die("Please set enabled = true in the [testing] section.\n");

// Make sure we are in development mode. We need fake logins.
Config::get('global.developmentMode')
  or die("Please set developmentMode = 1 in the [global] section.\n");

// Drop and recreate the testing DB.
// Execute this at PDO level, since idiorm cannot connect to a non-existing DB.
$gdsn = db_splitDsn(Config::get('general.database'));
$tdsn = db_splitDsn(Config::get('testing.database'));

$pdo = new PDO('mysql:host=' . $tdsn['host'], $tdsn['user'], $tdsn['password']);
$pdo->query('drop database if exists ' . $tdsn['database']);
$pdo->query('create database if not exists ' . $tdsn['database']);

// Warning about passwords on command line.
if ($gdsn['password'] || $tdsn['password']) {
  print "This script needs to run some mysqldump and mysql shell commands.\n";
  print "However, your DB DSN includes a password. We cannot add plaintext passwords\n";
  print "to MySQL commands. Please specify your username/password in ~/.my.cnf like so:\n";
  print "\n";
  print "[client]\n";
  print "user=your_username\n";
  print "password=your_password\n";
}

// Copy the schema from the regular DB.
// Use sed to remove AUTO_INCREMENT values - we want to start at 1.
exec(sprintf('mysqldump -h %s -u %s %s -d | sed -e "s/AUTO_INCREMENT=[[:digit:]]* //" | mysql -h %s -u %s %s',
             $gdsn['host'], $gdsn['user'], $gdsn['database'],
             $tdsn['host'], $tdsn['user'], $tdsn['database']));

// create some data
$u = Model::factory('User')->create();
$u->email = 'john@x.com';
$u->nick = 'john';
$u->name = 'John Smith';
$u->save();

$s = Model::factory('Source')->create();
$s->shortName = 'Source 1';
$s->urlName = 'source1';
$s->name = 'English - Klingon Dictionary';
$s->author = 'Worf';
$s->publisher = 'The Klingon Academy';
$s->year = '2010';
$s->isOfficial = 2; // TODO add constants in Source.php
$s->displayOrder = 1;
$s->save();

$s = Model::factory('Source')->create();
$s->shortName = 'Source 2';
$s->urlName = 'source2';
$s->name = "The Devil's Dictionary";
$s->author = 'Ambrose Bierce';
$s->publisher = 'Neale Publishing Co.';
$s->year = '1911';
$s->isOfficial = 1;
$s->displayOrder = 2;
$s->save();

$d = Model::factory('Definition')->create();
$d->userId = 1;
$d->sourceId = 1;
$d->lexicon = 'cheese';
$d->internalRep = '@cheese:@ a food made from the pressed curds of milk.';
$d->htmlRep = AdminStringUtil::htmlize($d->internalRep, $d->sourceId);
$d->status = Definition::ST_ACTIVE;
$d->save();

