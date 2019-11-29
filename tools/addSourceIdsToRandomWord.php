<?php
require_once __DIR__ . '/../lib/Core.php';
ini_set("memory_limit","512M");

$opts = getopt('n');
$dryRun = isset($opts['n']);

if (empty($opts) ) {
  print "There was a problem reading the arguments.".PHP_EOL;
  print "Options are:".PHP_EOL;
  print "  -n (dry run).".PHP_EOL;
  print "Are you sure you want to run without options set?  Type 'y' to continue: ";

  $handle = fopen ("php://stdin","r");
  $line = fgets($handle);
  fclose($handle);

  if(strtolower(trim($line)) != 'y'){
    print "ABORTING!".PHP_EOL;
    exit;
  }
  print "OK!, continuing...".PHP_EOL;
}

if ($dryRun) {
  print "---- DRY RUN ----".PHP_EOL;
} else {
  Log::info("Begin RandomWord indexing (sort of).");
}


const BATCH_SIZE = 10000;
$offset = 0;
$batch_count = 0;

$sourcesWithComma = Model::factory('Source')->where_like('shortName', '%,%')->find_many();
$sourcesWoutComma = Model::factory('Source')->where_not_like('shortName', '%,%')->find_many();
$rwCount = Model::factory('RandomWord')->count();

if (!$dryRun) { Log::info("$rwCount words in RandomWord are beeing processed, matching source names (col `surse`) to Ids (col `sourceIds`)."); }

$withCommas = [];
$woutCommas = [];

foreach($sourcesWithComma as $source) {
  $withCommas['/'.blowSpaces($source->shortName).'/'] = $source->id;
}
foreach($sourcesWoutComma as $source) {
  $woutCommas['/'.blowSpaces($source->shortName, true).'/'] = $source->id; // pounded array keys for preg_replace direct match DN != M(DN)
}

$query = Model::factory('RandomWord')->order_by_asc('seq');

if (!empty($withCommas) || !empty($woutCommas)) {
  do {
    $searchResults = $query
      ->limit(BATCH_SIZE)
      ->offset($offset)
      ->find_many();

    foreach($searchResults as $word) {
      // first replacing known sourceNames with comma inside, shouldn't be a false match
      $sourceNamesAltered = preg_replace(array_keys($withCommas), array_values($withCommas),  blowSpaces($word->surse));

      // second replacing sourceNames without comma in names, preparing string with pounded signes
      $realSourceIds = preg_replace(array_keys($woutCommas), array_values($woutCommas),  blowSpaces($sourceNamesAltered, true));

      // sanitize final result
      $word->sourceIds = str_replace('#', '', $realSourceIds);

      if (!$dryRun) { $word->save(); }
      echo "Processed: " . Util::percentageOf(++$batch_count, $rwCount, 0) . "% of " . $rwCount . " from RandomWord, $batch_count updated." . "\r";
    }
    $offset += count($searchResults);

    if (!$dryRun) { Log::info("$offset records from RandomWord processed, $batch_count updated."); }

  } while ($offset < $rwCount);

  if (!$dryRun) { Log::info("Finalized RandomWord indexing."); }
  print "FINISHED!".PHP_EOL;
  exit;
}

/*************************************************************************/

function blowSpaces($str, $pound = false) {
  $str = preg_replace('/\s+/', '', $str);
  if ($pound) $str = '#'.str_replace(',', '#,#', $str).'#'; // delimit names for preg_replace direct match DN != M(DN)
  return $str;
}
