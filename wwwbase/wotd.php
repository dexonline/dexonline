<?php

define('ONE_DAY_IN_SECS',86400);
define('WOTD_BIG_BANG', '2011-05-01');
define('WOTD_REASON_BIG_BANG', '2013-09-01');
define('WOTD_REASON_DISPLAY_DELAY', 2);

require_once("../phplib/util.php");
$date = util_getRequestParameter('d');
$type = util_getRequestParameter('t');
$delay = util_getRequestParameter('h', 0); //delay in minutes

// RSS stuff - could be separated from the rest
// TODO optimize & factorize
if ($type == 'rss' || $type == 'blog') {
  $words = WordOfTheDay::getRSSWotD($delay);
  $results = array();
  foreach ($words as $w) {
    $item = array();
    $ts = strtotime($w->displayDate);
    $defId = WordOfTheDayRel::getRefId($w->id);
    $def = Model::factory('Definition')->where('id', $defId)->where('status', ST_ACTIVE)->find_one();
    $source = Model::factory('Source')->where('id', $def->sourceId)->find_one();

    SmartyWrap::assign('def', $def);
    SmartyWrap::assign('source', $source);
    SmartyWrap::assign('imageUrl', $w->getImageUrl());
    if ($type == 'blog') {
        $curDate = strftime("%e %B", $ts);
        SmartyWrap::assign('curDate', $curDate);
        $item['title'] = "{$curDate} – " . $def->lexicon;
        $item['description'] = SmartyWrap::fetch('bits/wotdRssBlogItem.ihtml');
    }
    else {
        $item['title'] = $def->lexicon;
        $item['description'] = SmartyWrap::fetch('bits/wotdRssItem.ihtml');
    }
    $item['pubDate'] = date('D, d M Y H:i:s', $ts) . ' EEST';
    $item['link'] = util_getFullServerUrl() . 'cuvantul-zilei/' . date('Y/m/d', $ts);

    $results[] = $item;
  }

  header("Content-type: application/rss+xml");
  SmartyWrap::assign('rss_title', 'Cuvântul zilei');
  SmartyWrap::assign('rss_link', 'http://' . $_SERVER['HTTP_HOST'] . '/cuvantul-zilei/');
  SmartyWrap::assign('rss_description', 'Doza zilnică de cuvinte de la DEXonline!');
  SmartyWrap::assign('rss_pubDate', date('D, d M Y H:i:s') . ' EEST');
  SmartyWrap::assign('results', $results);
  SmartyWrap::displayWithoutSkin('rss.ixml');
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

$reason = '';
if ($wotd) {
  $reason = $wotd->description;
  if (
    util_isModerator(PRIV_ADMIN) || 
    ($mysqlDate >= WOTD_REASON_BIG_BANG && $date && strtotime($date) < time() - WOTD_REASON_DISPLAY_DELAY * ONE_DAY_IN_SECS) 
    ) {
    SmartyWrap::assign('reason', $reason);
  }
}

$defId = WordOfTheDayRel::getRefId($wotd->id);
$def = Definition::get_by_id($defId);

if ($type == 'url') {
  SmartyWrap::assign('today', $today);
  SmartyWrap::assign('title', $def->lexicon);
  SmartyWrap::displayWithoutSkin('bits/wotdurl.ihtml');
  exit;
}

$searchResults = SearchResult::mapDefinitionArray(array($def));
$roDate = strftime("%e %B %Y", $timestamp);
$pageTitle = sprintf("Cuvântul zilei (%s): %s", $roDate, $def->lexicon);
$pageDesc = sprintf("Cuvântul zilei de la dexonline. Azi, %s: %s", $roDate, $def->lexicon);

if ($mysqlDate > WOTD_BIG_BANG) {
  SmartyWrap::assign('prevday', date('Y/m/d', $timestamp - ONE_DAY_IN_SECS));
}
if ($mysqlDate < $today || util_isModerator(PRIV_ADMIN)) {
  SmartyWrap::assign('nextday', date('Y/m/d', $timestamp + ONE_DAY_IN_SECS));
}

SmartyWrap::assign('imageUrl', $wotd->getImageUrl());
SmartyWrap::assign('imageCredits', $wotd->getImageCredits());
SmartyWrap::assign('timestamp', $timestamp);
SmartyWrap::assign('not_generic_img', true);
SmartyWrap::assign('mysqlDate', $mysqlDate);
SmartyWrap::assign('page_title', $pageTitle);
SmartyWrap::assign('page_keywords', "Cuvântul zilei, {$def->lexicon}, dexonline, $pageTitle");
SmartyWrap::assign('page_description', $pageDesc);
SmartyWrap::assign('searchResult', array_pop($searchResults));

SmartyWrap::display('wotd.ihtml');

?>
