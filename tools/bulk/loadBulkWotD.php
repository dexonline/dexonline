<?php

require_once('../phplib/util.php');
require_once("../phplib/modelObjects.php");

$list = array_slice($argv, 1);
$userId = 471;

foreach($list as $defId) {
    $wotd = new WordOfTheDay();
    $status = $wotd->getStatus($defId);
    # if defId is not already in the list, add it
    if (is_null($status)) {
        $wotd->defId = $defId;
        $wotd->priority = 0;
        $wotd->save($userId);
    }
}

?>
