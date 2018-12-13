<?php

// hide reason for newer words
define('HIDE_REASON', false);
define('MAX_DATE_FOR_REASON_DISPLAY', '-2 days midnight');

require_once("../phplib/Core.php");
$date = Request::get('d');
$type = Request::get('t');
$delay = Request::get('h', 0); //delay in minutes

if (!$date) {
  $date = date('Y/m/d');
}
// use objects from here on
$date = new DateTimeImmutable($date);
$today = new DateTimeImmutable('today midnight');
$bigBang = new DateTimeImmutable(WordOfTheDay::BIG_BANG);
$maxReasonDate = HIDE_REASON ? new DateTimeImmutable(MAX_DATE_FOR_REASON_DISPLAY) : $today;

// RSS stuff - could be separated from the rest
// TODO optimize & factorize
if ($type == 'rss' || $type == 'blog') {
  $words = WordOfTheDay::getRSSWotD($delay);
  $results = [];
  foreach ($words as $w) {
    $item = [];
    $ts = strtotime($w->displayDate);
    $def = $w->getDefinition();
    $source = Source::get_by_id($def->sourceId);

    SmartyWrap::assign([
      'def' => $def,
      'source' => $source,
      'reason' => Str::htmlize($w->description)[0],
      'imageUrl' => $w->getLargeThumbUrl(),
      'html' => HtmlConverter::convert($def),
    ]);
    if ($type == 'blog') {
      $curDate = LocaleUtil::date($ts, "%e %B");
      SmartyWrap::assign('curDate', $curDate);
      $item['title'] = "{$curDate} – " . $def->lexicon;
      $item['description'] = SmartyWrap::fetch('bits/wotdRssBlogItem.tpl');
    }
    else {
      $item['title'] = $def->lexicon;
      $item['description'] = SmartyWrap::fetch('bits/wotdRssItem.tpl');
    }
    $item['pubDate'] = date('D, d M Y H:i:s', $ts) . ' EEST';
    $item['link'] = Request::getFullServerUrl() . 'cuvantul-zilei/' . date('Y/m/d', $ts);

    $results[] = $item;
  }

  header("Content-type: application/rss+xml; charset=utf-8");
  SmartyWrap::assign('rss_title', 'Cuvântul zilei');
  SmartyWrap::assign('rss_link', Request::getFullServerUrl() . 'cuvantul-zilei/');
  SmartyWrap::assign('rss_description', 'Doza zilnică de cuvinte de la dexonline!');
  SmartyWrap::assign('rss_pubDate', date('D, d M Y H:i:s') . ' EEST');
  SmartyWrap::assign('results', $results);
  SmartyWrap::displayWithoutSkin('xml/rss.tpl');
  exit;
}

if (($date < $bigBang) ||
    (($date > $today) && !User::can(User::PRIV_WOTD))) {
  FlashMessage::add('Nu puteți vedea cuvântul acelei zile.', 'warning');
  Util::redirect(Core::getWwwRoot() . 'cuvantul-zilei');
}

$mysqlDate = $date->format('Y-m-d');

$wotd = WordOfTheDay::get_by_displayDate($mysqlDate);
if (!$wotd) {
  // We shouldn't have missing words since the Big Bang.
  if ($date != $today) {
    Util::redirect(Core::getWwwRoot() . 'cuvantul-zilei');
  }
  $wotd = WordOfTheDay::updateTodaysWord();
}

$reason = '';
if ($wotd) {
  $reason = Str::htmlize($wotd->description)[0];
  if (User::can(User::PRIV_WOTD) || ($date <= $maxReasonDate)) {
    SmartyWrap::assign('reason', $reason);
  }
}

$def = Definition::get_by_id($wotd->definitionId);

if ($type == 'url') {
  SmartyWrap::assign('today', $today);
  SmartyWrap::assign('title', $def->lexicon);
  SmartyWrap::displayWithoutSkin('bits/wotdurl.tpl');
  exit;
}

$searchResults = SearchResult::mapDefinitionArray([$def]);

if ($date > $bigBang) {
  $prevDay = $date->sub(new DateInterval('P1D'))->format('Y/m/d');
  SmartyWrap::assign('prevDay', $prevDay);
} else {
  SmartyWrap::assign('prevDay', false);
}

if ($date < $today || User::can(User::PRIV_ADMIN)) {
  $nextDay = $date->add(new DateInterval('P1D'))->format('Y/m/d');
  SmartyWrap::assign('nextDay', $nextDay);
} else {
  SmartyWrap::assign('nextDay', false);
}

// Load the WotD for this day in other years.
$year = $date->format('Y');
$month = $date->format('m');
$monthName = LocaleUtil::date($date->getTimestamp(), '%B');
$day = $date->format('j');

$otherWotds = WordOfTheDay::getWotdsInOtherYears($year, $month, $day);
$otherYears = [];
foreach ($otherWotds as $w) {
  $def = $w->getDefinition();
  $w->description = Str::htmlize($w->description)[0];

  $otherYears[] = [
    'wotd' => $w,
    'word' => $def->lexicon,
  ];
}

SmartyWrap::assign([
  'wotd' => $wotd,
  'year' => $year,
  'month' => $month,
  'monthName' => $monthName,
  'day' => $day,
  'otherYears' => $otherYears,
  'searchResult' => array_pop($searchResults),
]);

SmartyWrap::display('wotd.tpl');
