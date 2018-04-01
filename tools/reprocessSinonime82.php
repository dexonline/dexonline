<?php

require_once __DIR__ . '/../phplib/Core.php';
$dbResult = DB::execute("select id from Definition where sourceId = 59 and status = 0 order by id", PDO::FETCH_ASSOC);

$withSave = false;
$defCount = $dbResult->rowCount();

$i = 0;
$modified = 0;
$ambiguousDefinitions = 0;
$ambiguities = 0;
$warnCount = 0;
$defIds = [];

foreach ($dbResult as $row) {
  $def = Definition::get_by_id($row['id']);
  $ambiguousMatches = [];
  $warnings = [];

  // Remove existing hash signs
  $newRep = str_replace('#', '', $def->internalRep);
  
  // Replace accented letters with new tonic accent notation
  $newRep = Str::changeAccents($newRep);

  list($newRep, $ambiguousMatches) = Str::sanitize($newRep, $def->sourceId, $warnings);

  if (!empty($warnings)) {
    $warnCount++;
    $defIds[] = $def->id;
  }

  if (count($ambiguousMatches) || ($newRep !== $def->internalRep)) {
    //print "{$def->id} {$newRep}\n";
  }
  if ($newRep !== $def->internalRep) {
    $modified++;
    $def->internalRep = $newRep;
    list($def->htmlRep, $ignored) = Str::htmlize($newRep, $def->sourceId, false);
  }
  if (count($ambiguousMatches)) {
    $def->abbrevReview = Definition::ABBREV_AMBIGUOUS;
    $ambiguousDefinitions++;
    $ambiguities += count($ambiguousMatches);
    print "  AMBIGUOUS:";
    foreach ($ambiguousMatches as $match) {
      print " [{$match['abbrev']}]@{$match['position']}";
    }
    print "\n";
    print "[edit]" . "(https://dexonline.ro/admin/definitionEdit.php?definitionId=" . $def->id . ")\n";
  } else {
    $def->abbrevReview = Definition::ABBREV_REVIEW_COMPLETE;
  }
  
  if ($withSave){
    $def->save();
  }
  $i++;
  echo "Processed: " . Util::percentageOf($modified, $defCount, 0) . "% of " . $defCount . " definitions." . "\r";
}
print "$i definitions reprocessed, $modified modified, $ambiguousDefinitions ambiguous with $ambiguities ambiguities.\n";
if ($warnCount) {
  print "A total of " . $warnCount . " sanitization warnings issued.\n";
  print "Definition Ids with sanitization warnings: " . implode(',', $defIds) . "\n";
  foreach ($defIds as $d) {
    print "[should edit]" . "(https://dexonline.ro/admin/definitionEdit.php?definitionId=" . $d . ")\n";
  }
}
