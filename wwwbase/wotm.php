<?php

define('WOTM_BIG_BANG', '2012-03-01');

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
  smarty_assign('today', $today);
  smarty_assign('title', $def->lexicon);
  smarty_displayWithoutSkin('common/bits/wotmurl.ihtml');
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
  smarty_assign('prevmon', date('Y/m', $prevTS));
}
if ($mysqlDate < $today || util_isModerator(PRIV_ADMIN)) {
  smarty_assign('nextmon', date('Y/m', $nextTS));
}

smarty_assign('imageUrl', $wotm->getImageUrl());
smarty_assign('imageCredits', $wotm->getImageCredits());
smarty_assign('timestamp', $timestamp);
smarty_assign('mysqlDate', $mysqlDate); //???
smarty_assign('page_title', $pageTitle);
smarty_assign('page_keywords', "Cuvântul lunii, {$def->lexicon}, dexonline, DEX online, $pageTitle");
smarty_assign('page_description', "$pageTitle de la dexonline");
smarty_assign('searchResult', array_pop($searchResults));

smarty_displayCommonPageWithSkin('wotm.ihtml');

?>
