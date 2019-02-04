<?php

// hide reason for newer words
const HIDE_REASON = false;
const MAX_DATE_FOR_REASON_DISPLAY = '-2 days midnight';

require_once '../lib/Core.php';
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

    Smart::assign([
      'def' => $def,
      'source' => $source,
      'reason' => Str::htmlize($w->description)[0],
      'imageUrl' => $w->getLargeThumbUrl(),
      'html' => HtmlConverter::convert($def),
    ]);
    if ($type == 'blog') {
      $curDate = LocaleUtil::date($ts, "%e %B");
      Smart::assign('curDate', $curDate);
      $item['title'] = "{$curDate} – " . $def->lexicon;
      $item['description'] = Smart::fetch('bits/wotdRssBlogItem.tpl');
    }
    else {
      $item['title'] = $def->lexicon;
      $item['description'] = Smart::fetch('bits/wotdRssItem.tpl');
    }
    $item['pubDate'] = date('D, d M Y H:i:s', $ts) . ' EEST';
    $item['link'] = sprintf('%s%scuvantul-zilei/%s',
                            Config::URL_HOST,
                            Config::URL_PREFIX,
                            date('Y/m/d', $ts));
    $results[] = $item;
  }

  header("Content-type: application/rss+xml; charset=utf-8");
  Smart::assign([
    'rss_title' => 'Cuvântul zilei',
    'rss_link' => Config::URL_HOST . Config::URL_PREFIX . 'cuvantul-zilei/',
    'rss_description' => 'Doza zilnică de cuvinte de la dexonline!',
    'rss_pubDate' => date('D, d M Y H:i:s') . ' EEST',
    'results' => $results,
  ]);
  Smart::displayWithoutSkin('xml/rss.tpl');
  exit;
}

if (($date < $bigBang) ||
    (($date > $today) && !User::can(User::PRIV_WOTD))) {
  FlashMessage::add('Nu puteți vedea cuvântul acelei zile.', 'warning');
  Util::redirect(Config::URL_PREFIX . 'cuvantul-zilei');
}

$mysqlDate = $date->format('Y-m-d');

$wotd = WordOfTheDay::get_by_displayDate($mysqlDate);
if (!$wotd) {
  // We shouldn't have missing words since the Big Bang.
  if ($date != $today) {
    Util::redirect(Config::URL_PREFIX . 'cuvantul-zilei');
  }
  $wotd = WordOfTheDay::updateTodaysWord();
}

$reason = '';
if ($wotd) {
  $reason = Str::htmlize($wotd->description)[0];
  if (User::can(User::PRIV_WOTD) || ($date <= $maxReasonDate)) {
    Smart::assign('reason', $reason);
  }
}

$def = Definition::get_by_id($wotd->definitionId);

if ($type == 'url') {
  Smart::assign('today', $today);
  Smart::assign('title', $def->lexicon);
  Smart::displayWithoutSkin('bits/wotdurl.tpl');
  exit;
}

$searchResults = SearchResult::mapDefinitionArray([$def]);

if ($date > $bigBang) {
  $prevDay = $date->sub(new DateInterval('P1D'))->format('Y/m/d');
  Smart::assign('prevDay', $prevDay);
} else {
  Smart::assign('prevDay', false);
}

if ($date < $today || User::can(User::PRIV_ADMIN)) {
  $nextDay = $date->add(new DateInterval('P1D'))->format('Y/m/d');
  Smart::assign('nextDay', $nextDay);
} else {
  Smart::assign('nextDay', false);
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

Smart::assign([
  'wotd' => $wotd,
  'year' => $year,
  'month' => $month,
  'monthName' => $monthName,
  'day' => $day,
  'otherYears' => $otherYears,
  'searchResult' => array_pop($searchResults),
]);

Smart::display('wotd.tpl');
