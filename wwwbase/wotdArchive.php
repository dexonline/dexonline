<?php

require_once("../phplib/util.php");
require_once("../phplib/modelObjects.php");

$year = (int) util_getRequestParameter('y');
$month = (int) util_getRequestParameter('m');
if (!$year) {
    $year = date('Y');
    $month = date('n');
}

$refDate = mktime(0, 0, 0, $month, 1, $year);
$date = strtotime("last Monday", $refDate);

function getDaysOfMonth($year, $month) {
    $days_in_month = date('t', mktime(0, 0, 0, $month, 1, $year));
    return $days_in_month;
}

function listDaysOfMonth($year, $month) {
    $days = array();
    $last_day = getDaysOfMonth($year, $month);
    for($day=1; $day<=$last_day; $day++) {
        $days[] = sprintf("%04d-%02d-%02d",$year, $month, $day);
    }
    return $days;
}

function createCalendar($year, $month) {
    $days = listDaysOfMonth($year, $month);
    $today = date('Y-m-d');

    $words = WordOfTheDay::getArchiveWotD($year, $month);
    $inv = array();
    foreach ($words as $k => $v) {
        $inv[$v->displayDate] = $k;
    }

    $new_words = array();
    foreach($days as $day) {
        if ($day <= $today && array_key_exists($day, $inv)) {
            $new_words[] = $words[$inv[$day]];
        }
        else {
            $new_words[] = WotDArchive::setOnlyDate($day);
        }
    }

    return $new_words;
}

$monthsName = array(
    1 => 'Ianuarie',
    2 => 'Februarie',
    3 => 'Martie',
    4 => 'Aprilie',
    5 => 'Mai',
    6 => 'Iunie',
    7 => 'Iulie',
    8 => 'August',
    9 => 'Septembrie',
    10 => 'Octombrie',
    11 => 'Noiembrie',
    12 => 'Decembrie',
);

smarty_assign('month', $monthsName[$month]);
smarty_assign('year', $year);

$showPrev = (($year > 2011) || (($year == 2011) && ($month > 5))) ? 1 : 0;
$showNext = (time() < mktime(0, 0, 0, $month + 1, 1, $year)) ? 0 : 1;
smarty_assign('showPrev', $showPrev);
smarty_assign('showNext', $showNext);
$prefix = '/arhiva/cuvantul-zilei';
if ($month == '01') {
    $prevMonth = $prefix . '/' . ($year - 1) . '/12';
}
else {
    $m = sprintf("%02d",(int) $month - 1);
    $prevMonth = "{$prefix}/{$year}/{$m}";
}
if ($month == '12') {
    $nextMonth = $prefix . '/' . ($year + 1) . '/01';
}
else {
    $m = sprintf("%02d",(int) $month + 1);
    $nextMonth = "{$prefix}/{$year}/{$m}";
}
smarty_assign('prevMonth', $prevMonth);
smarty_assign('nextMonth', $nextMonth);

$words = createCalendar($year, $month);
smarty_assign('words', $words);

smarty_displayWithoutSkin('common/wotdArchive.ihtml');
?>
