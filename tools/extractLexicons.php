<?php

require_once __DIR__ . '/../lib/Core.php';

$opts = getopt('n');
$dryRun = isset($opts['n']);

if (empty($opts) ) {
  print "There was a problem reading the arguments.\n";
  print "Options are: -- -n (dry run).\n";
  print "Are you sure you want to run without options set?  Type 'y' to continue: ";

  $handle = fopen ("php://stdin","r");
	$line = fgets($handle);
	fclose($handle);

	if(strtolower(trim($line)) != 'y'){
		print "ABORTING!\n";
		exit;
	}

	print "\n";
	print "OK!, continuing...\n";
}

if ($dryRun) {
  print "---- DRY RUN ----\n";
} else {
  Log::info("Begin verifying lexicons.");
}


$query = Model::factory('Definition')
        ->where_in('status', [Definition::ST_ACTIVE, Definition::ST_HIDDEN]);

$defCount = $query->count();

const BATCH_SIZE = 10000;
const START_ID = 0;
$offset = 0;
$modified = 0;
$batch_defCount = 0;

do {
  $defs = $query
          ->where_gte('id', START_ID)
          ->order_by_asc('id')
          ->limit(BATCH_SIZE)
          ->offset($offset)
          ->find_many();

  foreach ($defs as $d) {
    $l = $d->lexicon;
    $d->extractLexicon();
    if ($l !== $d->lexicon) {
      $modified++;
      if ($dryRun){
        printf("%s OLD:[%s] NEW:[%s]\n", defUrl($d), $l, $d->lexicon);
      } else {
        $d->save();
        Log::info("sourceId:[%d] defId:[%d] - OLD:[%s] NEW:[%s]", $d->sourceId, $d->id, $l, $d->lexicon);
      }
    }
    $batch_defCount++;
    echo "Processed: " . Util::percentageOf($batch_defCount, $defCount, 0) . "% of " . $defCount . " definitions, $modified modified." . "\r";
  }

  $offset += count($defs);

  Log::info("$offset definitions reprocessed, $modified modified.");
} while (count($defs));

Log::info("$offset definitions reprocessed, $modified modified.");

/*************************************************************************/

function defUrl($d) {
  return "https://dexonline.ro/editare-definitie/{$d->id}";
}
