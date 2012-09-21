<?php

define('WOTD_BIG_BANG', '2011-05-01');

require_once("../phplib/util.php");
$date = util_getRequestParameter('d');
$type = util_getRequestParameter('t');

// RSS stuff - could be separated from the rest
// TODO optimize & factorize
if ($type == 'rss') {
  $words = WordOfTheDay::getRSSWotD();
  $results = array();
  foreach ($words as $w) {
    $item = array();
    $ts = strtotime($w->displayDate);
    $defId = WordOfTheDayRel::getRefId($w->id);
    $def = Model::factory('Definition')->where('id', $defId)->where('status', ST_ACTIVE)->find_one();
    smarty_assign('def', $def);
    smarty_assign('imageUrl', $w->getImageUrl());
    smarty_assign('fullServerUrl', util_getFullServerUrl());
    $item['title'] = $def->lexicon;
    $item['description'] = smarty_fetch('common/bits/wotdRssItem.ihtml');
    $item['pubDate'] = date('D, d M Y H:i:s', $ts) . ' EEST';
    $item['link'] = util_getFullServerUrl() . 'cuvantul-zilei/' . date('Y/m/d', $ts);

    $results[] = $item;
  }

  header("Content-type: text/xml");
  smarty_assign('rss_title', 'Cuvântul zilei');
  smarty_assign('rss_link', 'http://' . $_SERVER['HTTP_HOST'] . '/cuvantul-zilei/');
  smarty_assign('rss_description', 'Doza zilnică de cuvinte propuse de DEXonline!');
  smarty_assign('rss_pubDate', date('D, d M Y H:i:s') . ' EEST');
  smarty_assign('results', $results);
  smarty_displayWithoutSkin('common/rss.ixml');
  exit;
}

$today = date('Y-m-d', time());
$timestamp = $date ? strtotime($date) : time();
$mysqlDate = date("Y-m-d", $timestamp);

if ($mysqlDate < WOTD_BIG_BANG || (($mysqlDate > $today) && !util_isModerator(PRIV_ADMIN))) {
  util_redirect(util_getWwwRoot() . 'cuvantul-zilei');
}

$wotd = WordOfTheDay::get_by_displayDate($mysqlDate);
if (!$wotd) {
  // We shouldn't have missing words since the Big Bang.
  if ($mysqlDate != $today) {
    util_redirect(util_getWwwRoot() . 'cuvantul-zilei');
  }
  WordOfTheDay::updateTodaysWord();
  $wotd = WordOfTheDay::get_by_displayDate($mysqlDate);
}

$defId = WordOfTheDayRel::getRefId($wotd->id);
$def = Definition::get_by_id($defId);
$searchResults = SearchResult::mapDefinitionArray(array($def));
$roDate = strftime("%e %B %Y", $timestamp);
$pageTitle = sprintf("Cuvântul zilei: %s (%s)", $def->lexicon, $roDate);

if ($mysqlDate > WOTD_BIG_BANG) {
  smarty_assign('prevday', date('Y/m/d', $timestamp - 86400));
}
if ($mysqlDate < $today || util_isModerator(PRIV_ADMIN)) {
  smarty_assign('nextday', date('Y/m/d', $timestamp + 86400));
}

smarty_assign('imageUrl', $wotd->getImageUrl());
smarty_assign('imageCredits', $wotd->getImageCredits());
smarty_assign('timestamp', $timestamp);
smarty_assign('mysqlDate', $mysqlDate);
smarty_assign('page_title', $pageTitle);
smarty_assign('page_keywords', "Cuvântul zilei, {$def->lexicon}, dexonline, DEX online, $pageTitle");
smarty_assign('page_description', "$pageTitle de la dexonline");
smarty_assign('searchResult', array_pop($searchResults));

if ($type == 'url') {
  smarty_assign('today', $today);
  smarty_assign('title', $def->lexicon);
  smarty_displayWithoutSkin('common/bits/wotdurl.ihtml');
} else {
  smarty_displayCommonPageWithSkin('wotd.ihtml');
}

?>
