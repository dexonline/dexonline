<?php

require_once '../phplib/Core.php';
User::mustHave(User::PRIV_WOTD);

$defId = Request::get('defId');

$wotd = WordOfTheDay::get_by_definitionId($defId);

if (!$wotd) {
  $wotd = Model::factory('WordOfTheDay')->create();
  $wotd->userId = User::getActiveId();
  $wotd->definitionId = $defId;
  $wotd->priority = 0;
  $wotd->save();

  $d = Definition::get_by_id($defId);

  Log::info("Added WotD, ID = {$wotd->id}, definition ID = {$d->id}, lexicon = {$d->lexicon}");
}

$where_to_go = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '/';
header("Location: {$where_to_go}");
