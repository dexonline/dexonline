<?php

$year = (int)Request::get('year', date('Y'));
$month = (int)Request::get('month', date('m'));

if (!checkDate($month, 1, $year)) {
  Util::redirectToRoute('wotd/archive'); // current month
}

function getDaysOfMonth($year, $month) {
  return date('t', mktime(0, 0, 0, $month, 1, $year));
}

function listDaysOfMonth($year, $month) {
  $days = [];
  $last_day = getDaysOfMonth($year, $month);
  for ($day = 1; $day <= $last_day; $day++) {
    $days[] = sprintf("%04d-%02d-%02d", $year, $month, $day);
  }
  return $days;
}

/**
 * Creates a matrix with 7 columns, one per day of week, and as many rows (weeks) as necessary.
 * Every cell contains a dictionary with the wotd, the definition and other info.
 */
function createCalendar($year, $month) {
  $days = listDaysOfMonth($year, $month);

  $today = date('Y-m-d');

  $calendar = [];

  // Pad beginning
  $startDow = date('N', strtotime("$year-$month-01"));
  for ($i = 1; $i < $startDow; $i++) {
    $calendar[] = [];
  }

  // Create a record per day
  foreach ($days as $i => $date) {
    $wotd = WordOfTheDay::get_by_displayDate($date);
    $def = $wotd ? Definition::get_by_id($wotd->definitionId) : null;
    $visible = $def && (($date <= $today) || User::can(User::PRIV_WOTD));
    $calendar[] = [
      'wotd' => $wotd,
      'def' => $def,
      'visible' => $visible,
      'dayOfMonth' => $i + 1,
    ];
  }

  // Pad end
  while (count($calendar) % 7 != 0) {
    $calendar[] = [];
  }

  // Wrap 7 records per line
  $weeks = [];
  while (count($calendar)) {
    $weeks[] = array_splice($calendar, 0, 7);
  }
  return $weeks;
}

$showPrev = ($year > 2011) || (($year == 2011) && ($month > 5));
$showNext = User::can(User::PRIV_ADMIN) || (time() >= mktime(0, 0, 0, $month + 1, 1, $year));

$prevMonth = ($month == '01')
  ? sprintf('%d/12', $year - 1)
  : sprintf('%d/%02d', $year, $month - 1);

$nextMonth = ($month == '12')
  ? sprintf('%d/01', $year + 1)
  : sprintf('%d/%02d', $year, $month + 1);

$words = createCalendar($year, $month);

Smart::assign([
  'month' => strftime("%B", strtotime("$year-$month-01")),
  'year' => $year,
  'showPrev' => $showPrev,
  'showNext' => $showNext,
  'prevMonth' => $prevMonth,
  'nextMonth' => $nextMonth,
  'words' => $words,
  'dayNames' => LocaleUtil::getWeekDayNames(),
]);
Smart::displayWithoutSkin('wotd/archive.tpl');
