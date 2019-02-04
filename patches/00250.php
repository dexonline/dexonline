<?php

require_once __DIR__ . '/../lib/third-party/parseIniFile.php';

// special case of some sources
const HTML_QIQ_PATTERN = [
  '/(?<!\\\\)„((?:[^„”]+|(?R))*)”/u' => '\$$1\$',
];

$ABBREV_INDEX = [];
$ABBREVS_CSV = [];

$userId = User::getActiveId(); // useless, as it is running from cli

$ini = new parseIniFile(true);

$raw = $ini->readConfig(Config::ROOT . "docs/abbrev/abbrev.conf", true);
foreach ($raw['sources'] as $sourceId => $sectionList) {
  $ABBREV_INDEX[$sourceId] = preg_split('/, */', $sectionList);
}

$warnings = [];
$errors = [];
$abbrevCount = 0;

foreach (array_keys($ABBREV_INDEX) as $sourceId) {
  $result_csv = [];
  $list = [];

  foreach ($ABBREV_INDEX[$sourceId] as $section) {
    $raw = $ini->readConfig(Config::ROOT . "docs/abbrev/{$section}.conf");

    // If an abbreviation is defined in several sections, use the one that's defined later
    if (is_array($raw)) {
      $list = array_merge($list, $raw[$section]);
    }
  }

  foreach ($list as $from => $to) {
    $ambiguous = ($from[0] == '*');
    if ($ambiguous) {
      $from = substr($from, 1);
    }

    $comment = ($from[0] == ';');
    if ($comment) {
      $warnings[] = 'WARN: ' . 'commented line in sourceId ' . $sourceId . ' ::: ' . trim(substr($from, 1)) . " ::: ADDED!";
      $from = substr($from, 1);
    }

    $to = str_replace(["'", "’"], "\'", $to);
    $result_csv[$from] = [
      'internalRep' => $to,
      'ambiguous' => $ambiguous,
    ];
  }

  //if (count($result_csv)){
    $ABBREVS_CSV[$sourceId] = $result_csv;
    $abbrevCount += count($result_csv);
  //}

}

$abbrevProcessed = 0;

echo "Total abbreviations: " . $abbrevCount . "\n";

foreach (array_keys($ABBREV_INDEX) as $sourceId) { // handle each source
  foreach ($ABBREVS_CSV[$sourceId] as $from => $values) {

    $abbrev = Model::factory('Abbreviation')->create();

    $abbrev->sourceId = $sourceId;
    $abbrev->enforced = false;
    $abbrev->ambiguous = $values['ambiguous'];
    $abbrev->caseSensitive = false;

    // some abbreviations are in html form e.g. „22”
    $abbrev->short = Str::cleanup($from);

    // internalRep shoul be
    $internalRep = $values['internalRep'];
    if ($sourceId == 30) { // DCR2 contains quotes in quotes
      foreach (HTML_QIQ_PATTERN as $internal => $replacement) {
        $internalRep = preg_replace($internal, $replacement, $internalRep);
      }
    }

    // further cleaning string
    $abbrev->internalRep = Str::cleanup($internalRep);

    list($htmlRep, $ignored) = Str::htmlize($abbrev->internalRep, $sourceId, $errors, $warnings);

    $abbrev->htmlRep = $htmlRep;
    $abbrev->modUserId = $userId;

    try {
      $abbrev->save();
      //$message = 'Added from source: ' . $sourceId . ' : abbreviation: ' . trim($from);
      $abbrevProcessed++;
    } catch (Exception $e) {
      $errors[] = 'Error: ' . $e->getMessage() . "\n";
    } finally {
      //echo $message . "\n";
      echo "Processed: " . Util::percentageOf($abbrevProcessed, $abbrevCount, 0) . "% of " . $abbrevCount . " abbreviations." . "\r";
    }
  }
}

echo "\n" . 'Done! Added: ' . $abbrevProcessed . ' abbreviations ' . (count($errors) ? (' collecting: ' . count($errors) . ' errors.') : '' ) . "\n";

if (count($warnings)) {
  echo count($warnings) . " warnings issued." . "\n";
  foreach (array_values($warnings) as $m) {
    echo $m . "\n";
  }
}
if (count($errors) > 0) {
  echo count($errors) . " errors encountered.";
  foreach (array_values($errors) as $m) {
    echo $m . "\n";
  }
}
