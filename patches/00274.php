<?php

const VERSIONS = [ '4.0', '4.1', '5.0', '6.0' ];

foreach (VERSIONS as $ver) {

  $zipUrl = sprintf('%sdownload/scrabble/loc-reduse-%s.zip',
                    Config::get('static.url'), $ver);
  $zipFile = tempnam(Config::get('global.tempDir'), 'loc_') . '.zip';
  $txtFile = tempnam(Config::get('global.tempDir'), 'loc_') . '.txt';

  print "Downloading version {$ver} to {$zipFile}\n";
  if (!@copy($zipUrl, $zipFile)) {
    print "Incorrect URL for reduced forms: {$zipUrl}\n";
    exit(1);
  }

  print "Unzipping version {$ver} to {$txtFile}\n";
  OS::executeAndAssert("unzip -p $zipFile > $txtFile");

  print "Importing version {$ver} to table Loc\n";
  $cmd =
    'load data local infile "%s" ' .
    'into table Loc ' .
    'lines terminated by "\r\n" ' .
    '(form) set version = "%s"';
  $cmd = sprintf($cmd, $txtFile, $ver);
  DB::executeFromOS($cmd);

  @unlink($zipFile);
  @unlink($txtFile);
}
