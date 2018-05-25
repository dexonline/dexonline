<?php
require_once("../../phplib/Core.php");
User::mustHave(User::PRIV_WOTD);
Util::assertNotMirror();
RecentLink::add('Asistent cuvântul zilei');

$nextMonth = date('Y-m', strtotime('+1 month'));
$yearMonth = Request::get('for', $nextMonth);

list($year, $month) = explode('-', $yearMonth);

$days = date('t', strtotime($yearMonth));

// collect wotds
$wotds = Model::factory('WordOfTheDay')
  ->table_alias('w')
  ->left_outer_join('Definition', ['w.definitionId', '=', 'd.id'], 'd')
  ->where_like('displayDate', "____-{$month}-__")
  // compensate for the fact that February has varying numbers of days
  ->where_raw('right(displayDate, 2) <= ?', $days)
  ->order_by_asc('displayDate')
  ->find_many();

// build an map of day number to
// 1. wotds for this day
// 2. wotds for that day in other years, including no year at all
$data = [];
foreach (range(1, $days) as $day) {
  $data[$day] = [
    'thisYear' => [],
    'otherYears' => [],
  ];
}

foreach ($wotds as $w) {
  list ($w->descHtml, $ignored) = Str::htmlize($w->description, 0);
  if ($w->internalRep) {
    list ($w->defHtml, $ignored) =
      Str::htmlize($w->internalRep, $w->sourceId);
  }

  list ($wyear, $wmonth, $wday) = explode('-', $w->displayDate);
  $wday = ltrim($wday, '0');

  if ($wyear == $year) {
    $data[$wday]['thisYear'][] = $w;
  } else {
    $data[$wday]['otherYears'][] = $w;
  }
}

// mark properly assigned days
foreach ($data as &$rec) {
  $a = $rec['thisYear'];
  $rec['allOk'] = count($a) == 1 && $a[0]->defHtml && $a[0]->description;
}

SmartyWrap::assign([
  'data' => $data,
  'yearMonth' => $yearMonth,
]);
SmartyWrap::addCss('admin', 'bootstrap-datepicker');
SmartyWrap::addJs('bootstrap-datepicker');
SmartyWrap::display('admin/wotdAssistant.tpl');
