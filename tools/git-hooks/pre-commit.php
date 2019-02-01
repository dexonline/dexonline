#!/usr/bin/php
<?php

/**
 * Checks whether the developer modified one of the files phplib/Config.php or
 * wwwbase/.htaccess. If they did, they should push the same changes to
 * phplib/Config.php.sample and wwwbase/.htaccess.sample respectively.
 *
 * Specifically, we check whether
 * - there are new constants in phplib/Config.php;
 * - some constants changed type in phplib/Config.php;
 * - there are new RewriteRules (commented or not) in wwwbase/.htaccess
 *
 * Checks whether any Selenium IDE tests contain
 * - a hard-coded base URL or
 * - an absolute URL path.
 **/

// We should already be at the root of the client
$sample = getConstants('phplib/Config.php.sample');
$actual = getConstants('phplib/Config.php');

foreach ($actual as $name => $value) {
  if (!array_key_exists($name, $sample)) {
    error("The constant *** $name *** is defined in lib/Config.php, " .
          "but not in lib/Config.php.sample. Please add it to lib/Config.php.sample.");
  }

  $actualType = gettype($value);
  $sampleType = getType($sample[$name]);
  if ($sampleType != $actualType) {
    error("The constant *** $name *** has type '$actualType' in lib/Config.php, " .
          "but type '$sampleType' in lib/Config.php.sample. Please reconcile them.");
  }
}

$htaccess = readRewriteRules('wwwbase/.htaccess');
$htaccessSample = readRewriteRules('wwwbase/.htaccess.sample');

foreach ($htaccess as $rule) {
  if (!in_array($rule, $htaccessSample)) {
    error("The RewriteRule *** $rule *** is defined in wwwbase/.htaccess, " .
          "but not in wwwbase/.htaccess.sample. Please reconcile the files.");
  }
}

/***************************************************************************/

// We cannot simply include both files because they declare the same class.
// Ugliness follows.
function getConstants($filename) {
  if (!file_exists($filename)) {
    error("Cannot read {$filename}");
  }

  $newName = 'c' . md5($filename);
  $code = file_get_contents($filename);
  $code = str_replace('class Config', "class {$newName}", $code);
  eval('?>' . $code);

  $reflectionClass = new ReflectionClass($newName);
  return $reflectionClass->getConstants();
}

// Reads the file, retains only the lines containing RewriteRule statements
// and strips the comments
function readRewriteRules($filename) {
  if (($lines = file($filename)) === false) {
    error("Cannot read $filename");
  }

  $result = [];
  foreach ($lines as $line) {
    $matches = [];
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
