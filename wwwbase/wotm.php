<?php

define('WOTM_BIG_BANG', '2012-04-01');

require_once("../phplib/Core.php");

$date = Request::get('d');
$type = Request::get('t');

$today = date('Y-m-01', time()); // Always use the first of the month
$timestamp = $date ? strtotime($date) : time();
$mysqlDate = date("Y-m-01", $timestamp);

if ($mysqlDate < WOTM_BIG_BANG || (($mysqlDate > $today) && !User::can(User::PRIV_WOTD))) {
  Util::redirect(Core::getWwwRoot() . 'cuvantul-lunii');
}

$wotm = WordOfTheMonth::getWotM($mysqlDate);
$def = Definition::get_by_id($wotm->definitionId);

if ($type == 'url') {
  SmartyWrap::assign('today', $today);
  SmartyWrap::assign('title', $def->lexicon);
  SmartyWrap::displayWithoutSkin('bits/wotmurl.tpl');
  exit;
}

$searchResults = SearchResult::mapDefinitionArray(array($def));

$cYear = date('Y', $timestamp);
$cMonth = date('n', $timestamp);
$nextTS = mktime(0, 0, 0, $cMonth + 1, 1, $cYear);
$prevTS = mktime(0, 0, 0, $cMonth - 1, 1, $cYear);

if ($mysqlDate > WOTM_BIG_BANG) {
  SmartyWrap::assign('prevmon', date('Y/m', $prevTS));
}
if ($mysqlDate < $today || User::can(User::PRIV_WOTD)) {
  SmartyWrap::assign('nextmon', date('Y/m', $nextTS));
}

SmartyWrap::assign('imageUrl', $wotm->getLargeThumbUrl());
SmartyWrap::assign('artist', $wotm->getArtist());
SmartyWrap::assign('timestamp', $timestamp);
SmartyWrap::assign('searchResult', array_pop($searchResults));

SmartyWrap::display('wotm.tpl');
