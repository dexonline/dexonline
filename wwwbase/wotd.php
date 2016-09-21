<?php

define('ONE_DAY_IN_SECS',86400);
define('WOTD_BIG_BANG', '2011-05-01');
define('WOTD_REASON_DISPLAY_DELAY', 2);

require_once("../phplib/util.php");
$date = Request::get('d');
$type = Request::get('t');
$delay = Request::get('h', 0); //delay in minutes

// RSS stuff - could be separated from the rest
// TODO optimize & factorize
if ($type == 'rss' || $type == 'blog') {
  $words = WordOfTheDay::getRSSWotD($delay);
  $results = array();
  foreach ($words as $w) {
    $item = array();
    $ts = strtotime($w->displayDate);
    $defId = WordOfTheDayRel::getRefId($w->id);
    $def = Model::factory('Definition')->where('id', $defId)->where('status', Definition::ST_ACTIVE)->find_one();
    $source = Model::factory('Source')->where('id', $def->sourceId)->find_one();

    SmartyWrap::assign('def', $def);
    SmartyWrap::assign('source', $source);
    SmartyWrap::assign('imageUrl', $w->getImageUrl());
    if ($type == 'blog') {
        $curDate = strftime("%e %B", $ts);
        SmartyWrap::assign('curDate', $curDate);
        $item['title'] = "{$curDate} – " . $def->lexicon;
        $item['description'] = SmartyWrap::fetch('bits/wotdRssBlogItem.tpl');
    }
    else {
        $item['title'] = $def->lexicon;
        $item['description'] = SmartyWrap::fetch('bits/wotdRssItem.tpl');
    }
    $item['pubDate'] = date('D, d M Y H:i:s', $ts) . ' EEST';
    $item['link'] = util_getFullServerUrl() . 'cuvantul-zilei/' . date('Y/m/d', $ts);

    $results[] = $item;
  }

  header("Content-type: application/rss+xml; charset=utf-8");
  SmartyWrap::assign('rss_title', 'Cuvântul zilei');
  SmartyWrap::assign('rss_link', 'http://' . $_SERVER['HTTP_HOST'] . '/cuvantul-zilei/');
  SmartyWrap::assign('rss_description', 'Doza zilnică de cuvinte de la DEXonline!');
  SmartyWrap::assign('rss_pubDate', date('D, d M Y H:i:s') . ' EEST');
  SmartyWrap::assign('results', $results);
  SmartyWrap::displayWithoutSkin('xml/rss.tpl');
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
    ($date && strtotime($date) < time() - WOTD_REASON_DISPLAY_DELAY * ONE_DAY_IN_SECS)
  ) {
    SmartyWrap::assign('reason', $reason);
  }
}

$defId = WordOfTheDayRel::getRefId($wotd->id);
$def = Definition::get_by_id($defId);

if ($type == 'url') {
  SmartyWrap::assign('today', $today);
  SmartyWrap::assign('title', $def->lexicon);
  SmartyWrap::displayWithoutSkin('bits/wotdurl.tpl');
  exit;
}

$searchResults = SearchResult::mapDefinitionArray(array($def));

if ($mysqlDate > WOTD_BIG_BANG) {
  SmartyWrap::assign('prevday', date('Y/m/d', $timestamp - ONE_DAY_IN_SECS));
}
if ($mysqlDate < $today || util_isModerator(PRIV_ADMIN)) {
  SmartyWrap::assign('nextday', date('Y/m/d', $timestamp + ONE_DAY_IN_SECS));
}

// Load the WotD for this day in other years.
$year = date('Y', $timestamp);
$month = date('m', $timestamp);
$day = date('d', $timestamp);

$prevWotds = WordOfTheDay::getPreviousYearsWotds($month, $day);
$otherYears = [];
foreach ($prevWotds as $w) {
  if ($w->displayDate < $today) {
    $currentYear = substr($w->displayDate, 0, 4);
    if ($currentYear != $year) {
      $defId = WordOfTheDayRel::getRefId($w->id);
      $def = Definition::get_by_id_status($defId, Definition::ST_ACTIVE);

      $otherYears[] = [
        'wotd' => $w,
        'word' => $def->lexicon,
      ];
    }
  }
}

SmartyWrap::assign('imageUrl', $wotd->getImageUrl());
SmartyWrap::assign('artist', $wotd->getArtist());
SmartyWrap::assign('timestamp', $timestamp);
SmartyWrap::assign('otherYears', $otherYears);
SmartyWrap::assign('searchResult', array_pop($searchResults));

SmartyWrap::display('wotd.tpl');

?>
