<?php

const WOTM_BIG_BANG = '2012-04-01';

require_once '../lib/Core.php';

$date = Request::get('d');
$type = Request::get('t');

$today = date('Y-m-01', time()); // Always use the first of the month
$timestamp = $date ? strtotime($date) : time();
$mysqlDate = date("Y-m-01", $timestamp);

if ($mysqlDate < WOTM_BIG_BANG || (($mysqlDate > $today) && !User::can(User::PRIV_WOTD))) {
  Util::redirect(Config::URL_PREFIX . 'cuvantul-lunii');
}

$wotm = WordOfTheMonth::getWotM($mysqlDate);
$def = Definition::get_by_id($wotm->definitionId);

if ($type == 'url') {
  Smart::assign('today', $today);
  Smart::assign('title', $def->lexicon);
  Smart::displayWithoutSkin('bits/wotmurl.tpl');
  exit;
}

$searchResults = SearchResult::mapDefinitionArray([$def]);

$cYear = date('Y', $timestamp);
$cMonth = date('n', $timestamp);
$nextTS = mktime(0, 0, 0, $cMonth + 1, 1, $cYear);
$prevTS = mktime(0, 0, 0, $cMonth - 1, 1, $cYear);

if ($mysqlDate > WOTM_BIG_BANG) {
  Smart::assign('prevmon', date('Y/m', $prevTS));
}
if ($mysqlDate < $today || User::can(User::PRIV_WOTD)) {
  Smart::assign('nextmon', date('Y/m', $nextTS));
}

Smart::assign([
  'imageUrl' => $wotm->getLargeThumbUrl(),
  'artist' => $wotm->getArtist(),
  'reason' => $wotm->description,
  'timestamp' => $timestamp,
  'searchResult' => array_pop($searchResults),
]);

Smart::display('wotm.tpl');
