<?php

require_once("../phplib/util.php");

$year = util_getRequestIntParameterWithDefault('y', date('Y'));
$month = util_getRequestIntParameterWithDefault('m', date('m'));

function getDaysOfMonth($year, $month) {
  return date('t', mktime(0, 0, 0, $month, 1, $year));
}

function listDaysOfMonth($year, $month) {
  $days = array();
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

  $calendar = array();

  // Pad beginning
  $startDow = date('N', strtotime("$year-$month-01"));
  for ($i = 1; $i < $startDow; $i++) {
    $calendar[] = array();
  }

  // Create a record per day
  foreach ($days as $i => $date) {
    $wotd = WordOfTheDay::get_by_displayDate($date);
    $wotdr = $wotd ? WordOfTheDayRel::get_by_wotdId($wotd->id) : null;
    $def = $wotdr ? Definition::get_by_id($wotdr->refId) : null;
    $visible = $def && (($date <= $today) || util_isModerator(PRIV_WOTD));
    $calendar[] = array('wotd' => $wotd,
                        'def' => $def,
                        'visible' => $visible,
                        'dayOfMonth' => $i + 1);
  }

  // Pad end
  while (count($calendar) % 7 != 0) {
    $calendar[] = array();
  }

  // Wrap 7 records per line
  $weeks = array();
  while (count($calendar)) {
    $weeks[] = array_splice($calendar, 0, 7);
  }
  return $weeks;
}

SmartyWrap::assign('month', strftime("%B", strtotime("$year-$month-01")));
SmartyWrap::assign('year', $year);

$showPrev = (($year > 2011) || (($year == 2011) && ($month > 5))) ? 1 : 0;
$showNext = util_isModerator(PRIV_ADMIN) || (time() >= mktime(0, 0, 0, $month + 1, 1, $year));

SmartyWrap::assign('showPrev', $showPrev);
SmartyWrap::assign('showNext', $showNext);
$prefix = 'arhiva/cuvantul-zilei';
if ($month == '01') {
  $prevMonth = $prefix . '/' . ($year - 1) . '/12';
} else {
  $m = sprintf("%02d",(int) $month - 1);
  $prevMonth = "{$prefix}/{$year}/{$m}";
}
if ($month == '12') {
  $nextMonth = $prefix . '/' . ($year + 1) . '/01';
} else {
  $m = sprintf("%02d",(int) $month + 1);
  $nextMonth = "{$prefix}/{$year}/{$m}";
}
SmartyWrap::assign('prevMonth', $prevMonth);
SmartyWrap::assign('nextMonth', $nextMonth);

$words = createCalendar($year, $month);
SmartyWrap::assign('words', $words);

SmartyWrap::displayWithoutSkin('wotdArchive.tpl');
?>
