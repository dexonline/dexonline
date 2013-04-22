<?php
require_once __DIR__ . '/../phplib/util.php';
$dbResult = db_execute("select id from Definition where sourceId = 33 and status = 0 order by id", PDO::FETCH_ASSOC);

$i = 0;
$modified = 0;
$ambiguousDefinitions = 0;
$ambiguities = 0;
foreach ($dbResult as $row) {
  $def = Definition::get_by_id($row['id']);
  $ambiguousMatches = array();
  // Remove existing hash signs
  $newRep = str_replace('#', '', $def->internalRep);
  $newRep = AdminStringUtil::internalizeDefinition($newRep, $def->sourceId, $ambiguousMatches);
  if (count($ambiguousMatches) || ($newRep !== $def->internalRep)) {
    print "{$def->id} {$newRep}\n";
  }
  if ($newRep !== $def->internalRep) {
    $modified++;
    $def->internalRep = $newRep;
    $def->htmlRep = AdminStringUtil::htmlize($newRep, $def->sourceId);
  }
  if (count($ambiguousMatches)) {
    $def->abbrevReview = ABBREV_AMBIGUOUS;
    $ambiguousDefinitions++;
    $ambiguities += count($ambiguousMatches);
    print "  AMBIGUOUS:";
    foreach ($ambiguousMatches as $match) {
      print " [{$match['abbrev']}]@{$match['position']}";
    }
    print "\n";
  } else {
    $def->abbrevReview = ABBREV_REVIEW_COMPLETE;
  }
  $def->save();
  $i++;
  if ($i % 1000 == 0) {
    print "$i definitions reprocessed, $modified modified, $ambiguousDefinitions ambiguous with $ambiguities ambiguities.\n";
  }
}
print "$i definitions reprocessed, $modified modified, $ambiguousDefinitions ambiguous with $ambiguities ambiguities.\n";

?>
