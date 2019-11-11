<?php

// hide reason for newer words
const HIDE_REASON = false;
const MAX_DATE_FOR_REASON_DISPLAY = '-2 days midnight';

$year = Request::get('year');
$month = Request::get('month');
$day = Request::get('day');
$format = Request::getFormat();

// use objects from here on
// catch invalid dates like 1900/13/50
try {
  $dateStr = ($year && $month && $day)
    ? "{$year}/{$month}/{$day}"
    : date('Y/m/d');

  $date = new DateTimeImmutable($dateStr);
} catch (Exception $e) {
  FlashMessage::add('Ați introdus o dată incorectă.', 'warning');
  Util::redirectToRoute('wotd/view');
}
$today = new DateTimeImmutable('today midnight');
$bigBang = new DateTimeImmutable(WordOfTheDay::BIG_BANG);
$maxReasonDate = HIDE_REASON ? new DateTimeImmutable(MAX_DATE_FOR_REASON_DISPLAY) : $today;

if (($date < $bigBang) ||
    (($date > $today) && !User::can(User::PRIV_WOTD))) {
  FlashMessage::add('Nu puteți vedea cuvântul acelei zile.', 'warning');
  Util::redirectToRoute('wotd/view');
}

$mysqlDate = $date->format('Y-m-d');

$wotd = WordOfTheDay::get_by_displayDate($mysqlDate);
if (!$wotd) {
  // We shouldn't have missing words since the Big Bang.
  if ($date != $today) {
    Util::redirectToRoute('wotd/view');
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

switch ($format['name']) {
  case 'xml':
  case 'json':
    header('Content-type: '.$format['content_type']);
    Smart::displayWithoutSkin($format['tpl_path'].'/wotd.tpl');
    break;
  default:
    Smart::display('wotd/view.tpl');
}


