<?php

define('WOTM_BIG_BANG', '2012-04-01');

require_once("../phplib/util.php");

$date = util_getRequestParameter('d');
$type = util_getRequestParameter('t');

$today = date('Y-m-d', time());
$timestamp = $date ? strtotime($date) : time();
$mysqlDate = date("Y-m-d", $timestamp);

if ($mysqlDate < WOTM_BIG_BANG || (($mysqlDate > $today) && !util_isModerator(PRIV_ADMIN))) {
  util_redirect(util_getWwwRoot() . 'cuvantul-lunii');
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
$roDate = strftime("%e %B %Y", $timestamp);
$pageTitle = sprintf("Cuvântul lunii: %s (%s)", $def->lexicon, $roDate);

$cYear = date('Y', $timestamp);
$cMonth = date('n', $timestamp);
$nextTS = mktime(0, 0, 0, $cMonth + 1, 1, $cYear);
$prevTS = mktime(0, 0, 0, $cMonth - 1, 1, $cYear);

if ($mysqlDate > WOTM_BIG_BANG) {
  SmartyWrap::assign('prevmon', date('Y/m', $prevTS));
}
if ($mysqlDate < $today || util_isModerator(PRIV_ADMIN)) {
  SmartyWrap::assign('nextmon', date('Y/m', $nextTS));
}

SmartyWrap::assign('imageUrl', $wotm->getImageUrl());
SmartyWrap::assign('imageCredits', $wotm->getImageCredits());
SmartyWrap::assign('timestamp', $timestamp);
SmartyWrap::assign('not_generic_img', true);
SmartyWrap::assign('mysqlDate', $mysqlDate); //???
SmartyWrap::assign('page_title', $pageTitle);
SmartyWrap::assign('page_keywords', "Cuvântul lunii, {$def->lexicon}, dexonline, $pageTitle");
SmartyWrap::assign('page_description', "$pageTitle de la dexonline");
SmartyWrap::assign('searchResult', array_pop($searchResults));

SmartyWrap::display('wotm.tpl');

?>
