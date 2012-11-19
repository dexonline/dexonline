<?php
require_once("../phplib/util.php");

$TODAY = date("Y-m-d");
$FOLDER = util_getRootPath() . '/wwwbase/download/xmldump';
$URL = 'http://dexonline.ro/download/xmldump';

if (count($_GET) == 0) {
  util_redirect("http://wiki.dexonline.ro/wiki/Update4Instructions");
}

$lastDump = getLastDumpDate($TODAY, $FOLDER);
SmartyWrap::assign('lastDump', $lastDump);
SmartyWrap::assign('url', $URL);

$lastClientUpdate = util_getRequestParameterWithDefault('last', '0');
if ($lastClientUpdate == '0') {
  // Dump the freshest full dump we have
  // TODO: return an error if there is no full dump
  SmartyWrap::assign('serveFullDump', true);
  $lastClientUpdate = $lastDump;
}

SmartyWrap::assign('diffs', getDiffsBetween($lastClientUpdate, $TODAY, $FOLDER));

header('Content-type: text/xml');
print SmartyWrap::fetch('common/update4.ihtml');

/**************************************************************************/

// Do not return a dump for today, in case it is still being built
function getLastDumpDate($today, $folder) {
  $files = scandir($folder, 1); // descending
  foreach ($files as $file) {
    $matches = array();
    if (preg_match('/^(\\d\\d\\d\\d-\\d\\d-\\d\\d)-abbrevs.xml.gz$/', $file, $matches)) {
      $candidate = $matches[1];
      if ($candidate < $today &&
          file_exists("$folder/$candidate-abbrevs.xml.gz") &&
          file_exists("$folder/$candidate-definitions.xml.gz") &&
          file_exists("$folder/$candidate-inflections.xml.gz") &&
          file_exists("$folder/$candidate-ldm.xml.gz") &&
          file_exists("$folder/$candidate-lexems.xml.gz") &&
          file_exists("$folder/$candidate-sources.xml.gz")) {
        return $candidate;
      }
    }
  }
  return null;
}

// Return diffs between the given date and today, exclusively.
// Do not return diffs for today, in case they are still being built.
function getDiffsBetween($date, $today, $folder) {
  $files = scandir($folder, 0);
  $results = array();
  foreach ($files as $file) {
    $matches = array();
    if (preg_match('/^(\\d\\d\\d\\d-\\d\\d-\\d\\d)-definitions-diff.xml.gz$/', $file, $matches)) {
      $candidate = $matches[1];
      if ($candidate > $date && $candidate < $today &&
          file_exists("$folder/$candidate-definitions-diff.xml.gz") &&
          file_exists("$folder/$candidate-ldm-diff.xml.gz") &&
          file_exists("$folder/$candidate-lexems-diff.xml.gz")) {
        $results[] = $candidate;
      }
    }
  }
  return $results;
}

?>
