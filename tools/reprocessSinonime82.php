<?php

require_once __DIR__ . '/../phplib/Core.php';

$opts = getopt('an');
$ambiguous = isset($opts['a']);
$dryRun = isset($opts['n']);

if (empty($opts) ) { 
  print "There was a problem reading the arguments.\n"; 
  print "Options are: -- -a (mark ambiguities).\n"; 
  print "                -n (dry run).\n"; 
	print "Are you sure you want to run without options set?  Type 'y' to continue: ";
	
  $handle = fopen ("php://stdin","r");
	$line = fgets($handle);
	fclose($handle);
  
	if(trim($line) != 'y'){
		print "ABORTING!\n";
		exit;
	}
  
	print "\n"; 
	print "OK!, continuing...\n";  
} 

if ($dryRun) {
  print "---- DRY RUN ----\n";
}

$dbResult = DB::execute("select id from Definition where sourceId = 59 and status = 0 order by id", PDO::FETCH_ASSOC);

$withSave = false;
$defCount = $dbResult->rowCount();

$i = 0;
$modified = 0;
$ambiguousDefinitions = 0;
$ambiguities = 0;
$warnCount = 0;
$warnDefIds = [];

foreach ($dbResult as $row) {
  $def = Definition::get_by_id($row['id']);
  $ambiguousMatches = [];
  $warnings = [];
  $newRep = $def->internalRep;
  
  // Remove existing hash signs if ambiguous option (-a) is set
  if ($ambiguous) {
    $newRep = str_replace('#', '', $newRep);
  }
  
  // Replace accented letters with new tonic accent notation
  $newRep = Str::changeAccents($newRep);

  list($newRep, $ambiguousMatches) = Str::sanitize($newRep, $def->sourceId, $warnings);

  if (!empty($warnings)) {
    $warnCount++;
    $warnDefIds[] = $def->id;
  }

  if ($newRep !== $def->internalRep) {
    $modified++;
    $def->internalRep = $newRep;
    list($def->htmlRep, $ignored) = Str::htmlize($newRep, $def->sourceId, false);
  }
  
  if (count($ambiguousMatches)) {
    if ($ambiguous) {
      $def->abbrevReview = Definition::ABBREV_AMBIGUOUS;
    }
    $ambiguousDefinitions++;
    $ambiguities += count($ambiguousMatches);
    /*print "  AMBIGUOUS:";
    foreach ($ambiguousMatches as $match) {
      print " [{$match['abbrev']}]@{$match['position']}";
    }
    print "\n";
    print "[edit]" . "(https://dexonline.ro/admin/definitionEdit.php?definitionId=" . $def->id . ")\n";*/
  } else {
    $def->abbrevReview = Definition::ABBREV_REVIEW_COMPLETE;
  }
  
  if (!$dryRun){
    $def->save();
  }
  $i++;
  echo "Processed: " . Util::percentageOf($modified, $defCount, 0) . "% of " . $defCount . " definitions." . "\r";
}
print "$i definitions reprocessed, $modified modified, $ambiguousDefinitions ambiguous with $ambiguities ambiguities.\n";
if ($warnCount) {
  print "A total of " . $warnCount . " sanitization warnings issued.\n";
  print "Definition Ids with sanitization warnings: " . implode(',', $warnDefIds) . "\n";
  foreach ($warnDefIds as $d) {
    print "[should review]" . "(https://dexonline.ro/admin/definitionEdit.php?definitionId=" . $d . ")\n";
  }
}
