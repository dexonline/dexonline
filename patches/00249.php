<?php

require_once '../phplib/Core.php';
require_once '../phplib/third-party/parseIniFile.php';

$ABBREV_INDEX = [];
$ABBREVS_CSV = [];
$ABBREVS_DB = [];
$userId = User::getActiveId(); // useless, as it is running from cli

$ini = new parseIniFile();

$raw = $ini->readConfig(Core::getRootPath() . "docs/abbrev/abbrev.conf", true);
foreach ($raw['sources'] as $sourceId => $sectionList) {
  $ABBREV_INDEX[$sourceId] = preg_split('/, */', $sectionList);
}

foreach (array_keys($ABBREV_INDEX) as $sourceId) {
  $result_csv = [];
  $list = [];

  foreach ($ABBREV_INDEX[$sourceId] as $section) {
    $raw = $ini->readConfig(Core::getRootPath() . "docs/abbrev/{$section}.conf");

    // If an abbreviation is defined in several sections, use the one that's defined later
    $list = array_merge($list, $raw[$section]);
  }

  foreach ($list as $from => $to) {
    $ambiguous = ($from[0] == '*');
    if ($ambiguous) {
      $from = substr($from, 1);
    }
    $numWords = 1 + substr_count($from, ' ');

    $result_csv[$from] = [
      'to' => $to,
      'ambiguous' => $ambiguous,
      'numWords' => $numWords,
    ];
  }

  $ABBREVS_CSV[$sourceId] = $result_csv;
}

$abbrevCount = 0;
$errCount = 0;
$errMessages = "";

foreach (array_keys($ABBREV_INDEX) as $sourceId) { // handle each source
  foreach ($ABBREVS_CSV[$sourceId] as $from => $values) {

    $htmlRep = $values['to'];

    foreach (Constant::HTML_ABBREV_PATTERNS as $internal => $replacement) {
      if (is_string($replacement)) {
        $htmlRep = preg_replace($internal, $replacement, $htmlRep);
      } else {
        $htmlRep = null;
        $errCount++;
        $errMessages .= 'Error in source: ' . $sourceId . ' : abbreviation: ' . trim($from) . ' - Unknown value type in HTML_ABBREV_PATTERNS.' . "\n";
      }
    }

    $abbrev = Model::factory('Abbreviation')->create();

    $abbrev->sourceId = $sourceId;
    $abbrev->enforced = false;
    $abbrev->ambiguous = $values['ambiguous'];
    $abbrev->caseSensitive = false;
    $abbrev->short = trim($from);
    $abbrev->internalRep = trim($values['to']);
    $abbrev->htmlRep = trim($htmlRep);
    $abbrev->modUserId = $userId;

    try {
      $abbrev->save();
      $message = 'Added from dource: ' . $sourceId . ' : abbreviation: ' . trim($from);
      $abbrevCount++;
    } catch (Exception $e) {
      $errCount++;
      $message = 'Error: ' . $e->getMessage() . "\n";
      $errMessages .= 'Error: ' . $e->getMessage() . "\n";
    } finally {
      echo $message . "\n";
    }
  }
}

echo 'Done! Added: ' . $abbrevCount . ' abbreviations ' . ($errCount ? (' collectiong: ' . $errCount . ' errors.') : '' ) . "\n";
echo $errMessages;