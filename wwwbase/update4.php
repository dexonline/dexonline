<?php
require_once("../phplib/util.php");

$TODAY = date("Y-m-d");
$REMOTE_FOLDER = 'download/xmldump';
$STATIC_FILES = file(Config::get('static.url') . 'fileList.txt');
$URL = Config::get('static.url') . 'download/xmldump';

if (count($_GET) == 0) {
  util_redirect("http://wiki.dexonline.ro/wiki/Protocol_de_exportare_a_datelor");
}

$lastDump = getLastDumpDate($TODAY, $REMOTE_FOLDER);
SmartyWrap::assign('lastDump', $lastDump);
SmartyWrap::assign('url', $URL);

$lastClientUpdate = util_getRequestParameterWithDefault('last', '0');
if ($lastClientUpdate == '0') {
  // Dump the freshest full dump we have
  // TODO: return an error if there is no full dump
  SmartyWrap::assign('serveFullDump', true);
  $lastClientUpdate = $lastDump;
}

SmartyWrap::assign('diffs', getDiffsBetween($lastClientUpdate, $TODAY, $REMOTE_FOLDER));

header('Content-type: text/xml');
print SmartyWrap::fetch('xml/update4.tpl');

/**************************************************************************/

// Do not return a dump for today, in case it is still being built
function getLastDumpDate($today, $folder) {
  global $STATIC_FILES;

  // Group existing files by date, excluding the diff files
  $map = array();
  foreach ($STATIC_FILES as $file) {
    $matches = array();
    if (preg_match(":^{$folder}/(\\d\\d\\d\\d-\\d\\d-\\d\\d)-[a-z]+.xml.gz:", $file, $matches)) {
      $date = $matches[1];
      if ($date < $today) {
        if (array_key_exists($date, $map)) {
          $map[$date]++;
        } else {
          $map[$date] = 1;
        }
      }
    }
  }

  // Now check if the most recent date has 6 dump files
  if (count($map)) {
    krsort($map);
    $date = key($map); // First key
    return ($map[$date] == 6) ? $date : null;
  } else {  
    return null;
  }
}

// Return diffs between the given date and today, exclusively.
// Do not return diffs for today, in case they are still being built.
function getDiffsBetween($date, $today, $folder) {
  global $STATIC_FILES;

  // Group existing diff files by date
  $map = array();
  foreach ($STATIC_FILES as $file) {
    $matches = array();
    if (preg_match(":^{$folder}/(\\d\\d\\d\\d-\\d\\d-\\d\\d)-[a-z]+-diff.xml.gz:", $file, $matches)) {
      $diffDate = $matches[1];
      if ($diffDate > $date && $diffDate < $today) {
        if (array_key_exists($matches[1], $map)) {
          $map[$matches[1]]++;
        } else {
          $map[$matches[1]] = 1;
        }
      }
    }
  }
  ksort($map);

  // Now returns those having all 3 diff files
  $results = array();
  foreach ($map as $date => $numFiles) {
    if ($numFiles == 3) {
      $results[] = $date;
    }
  }
  return $results;
}

?>
