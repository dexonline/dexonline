<?php

require_once("../phplib/util.php");
util_assertModerator(PRIV_WOTD);
util_assertNotMirror();

$defId = util_getRequestParameter('defId');

$status = WordOfTheDay::getStatus($defId);

if (is_null($status)) {
  $wotd = Model::factory('WordOfTheDay')->create();
  $wotd->userId = session_getUserId();
  $wotd->priority = 0;
  $wotd->save();

  $wotdr = Model::factory('WordOfTheDayRel')->create();
  $wotdr->refId = $defId;
  $wotdr->refType = 'Definition';
  $wotdr->wotdId = $wotd->id;
  $wotdr->save();
  
  log_userLog("Added new word of the day: {$wotd->id} - the definition with the id {$wotdr->refId}");
}

$where_to_go = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '/';
header("Location: {$where_to_go}");
?>
