<?php

require_once('../phplib/util.php');

print "\n\nAcest script nu a fost testat după conversia AdoDB -> Idiorm.\n";
print "El este corect d.p.d.v. sintactic, dar atât.\n";
print "Ștergeți aceste linii și asigurați-vă că scriptul face ceea ce trebuie.\n\n\n";
exit(1);

$list = array_slice($argv, 1);
$userId = 471;

foreach($list as $defId) {
  $status = WordOfTheDay::getStatus($defId);
  // if defId is not already in the list, add it
  if (is_null($status)) {
    $wotd = Model::factory('WordOfTheDay')->create();
    $wotd->userId = $userId;
    $wotd->priority = 0;
    $wotd->save();

    $wotdr = Model::factory('WordOfTheDayRel')->create();
    $wotdr->refId = $defId;
    $wotdr->refType = 'Definition';
    $wotdr->wotdId = $wotd->id;
    $wotdr->save();    
  }
}

?>
