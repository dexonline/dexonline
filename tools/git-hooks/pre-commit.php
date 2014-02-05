#!/usr/bin/php
<?php

   /**
    * Checks whether the developer modified one of the files dex.conf or wwwbase/.htaccess.
    * If they did, they should push the same changes to dex.conf.sample and wwwbase/.htaccess.sample respectively.
    * Specifically, we check whether
    * - there are new sections in dex.conf
    * - there are new variables in dex.conf
    * - some variables changed type in dex.conf
    * - there are new RewriteRules (commented or not) in wwwbase/.htaccess
    */

   // We should already be at the root of the client
if (($dexConf = parse_ini_file('dex.conf', true)) === false) {
  error('Cannot read dex.conf');
}
if (($dexConfSample = parse_ini_file('dex.conf.sample', true)) === false) {
  error('Cannot read dex.conf');
}

foreach ($dexConf as $sectionTitle => $sectionVars) {
  // Check that no new sections are defined
  if (!array_key_exists($sectionTitle, $dexConfSample)) {
    error("The section *** [$sectionTitle] *** is defined in dex.conf, but not in dex.conf.sample. Please add it to dex.conf.sample.");
  }

  foreach ($sectionVars as $key => $value) {
    // Check that no new variables are defined
    if (!array_key_exists($key, $dexConfSample[$sectionTitle])) {
      error("The variable *** [$sectionTitle].$key *** is defined in dex.conf, but not in dex.conf.sample. Please add it to dex.conf.sample.");
    }

    // Check that variable types haven't changed
    $typeDexConf = gettype($value);
    $typeDexConfSample = getType($dexConfSample[$sectionTitle][$key]);
    if ($typeDexConf != $typeDexConfSample) {
      error("The variable *** [$sectionTitle].$key *** has type '$typeDexConf' in dex.conf, but type '$typeDexConfSample' in dex.conf.sample. Please reconcile them.");
    }
  }
}

if (($htaccess = readRewriteRules('wwwbase/.htaccess')) === false) {
  error('Cannot read wwwbase/.htaccess');
}
if (($htaccessSample = readRewriteRules('wwwbase/.htaccess.sample')) === false) {
  error('Cannot read wwwbase/.htaccess.sample');
}

foreach ($htaccess as $rule) {
  if (!in_array($rule, $htaccessSample)) {
    error("The RewriteRule *** $rule *** is defined in wwwbase/.htaccess, but not in wwwbase/.htaccess.sample. Please reconcile the files.");
  }
}

/***************************************************************************/

// Reads the file, retains only the lines containing RewriteRule statements
// and strips the comments
function readRewriteRules($filename) {
  if (($lines = file($filename)) === false) {
    return false;
  }

  $result = array();
  foreach ($lines as $line) {
    $matches = array();
    if (preg_match("/^(#+\s+)?RewriteRule\s+(.*)/", trim($line), $matches)) {
      $result[] = $matches[2];
    }
  }
  return $result;
}

function error($msg) {
  print "The pre-commit hook encountered an error.\n";
  print "If you know what you are doing, you can bypass this error by using the -n (--no-verify) flag:\n";
  print "\n";
  print "    git commit -n\n";
  print "\n";
  print "The error message was:\n";
  print $msg . "\n";
  exit(1);
}

?>
