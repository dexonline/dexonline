<?php
require_once '../../phplib/Core.php';
User::mustHave(User::PRIV_WOTD);
Util::assertNotMirror();
RecentLink::add('Asistent cuvântul zilei');

// Certain old wotds occur commonly as prefixes or suffixes and trigger too
// many duplicate warnings, so we ignore them. We still report duplicates if
// the exact word is chosen again.
const DUPLICATES_TO_IGNORE = [
  'bas', 'buf', 'bur', 'cid', 'geneză', 'inic', 'inter', 'ion', 'mat', 'oman', 'op',
  'ordie', 'pan', 'pat', 'rec', 'ster', 'tor',
];

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
    'duplicates' => [],
  ];
}

foreach ($wotds as $w) {
  list ($w->descHtml, $ignored) = Str::htmlize($w->description);
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

$duplicates = loadDuplicates($yearMonth);
foreach ($duplicates as $day => $dups) {
  $data[$day]['duplicates'] = $dups;
}

// mark properly assigned days
foreach ($data as &$rec) {
  $a = $rec['thisYear'];
  $rec['allOk'] =
    count($a) == 1 &&
    $a[0]->defHtml &&
    $a[0]->description &&
    empty($rec['duplicates']);
}

SmartyWrap::assign([
  'data' => $data,
  'yearMonth' => $yearMonth,
  'enMonthName' => Str::getEnglishMonthName($yearMonth),
]);
SmartyWrap::addCss('admin', 'bootstrap-datepicker');
SmartyWrap::addJs('bootstrap-datepicker');
SmartyWrap::display('admin/wotdAssistant.tpl');

/*************************************************************************/

// load similar wotds (those with a defined displayDate in the past, which are prefixes or suffixes
// of wotds from this month, or which contain these as a prefix or suffix).
function loadDuplicates($yearMonth) {
  // use a raw query due to the complex join condition
  $dupQuery = <<<SQL
    select trim(leading '0' from right(w1.displayDate, 2)) as day,
           d2.lexicon as oldLexicon,
           replace(w2.displayDate, '-', '/') as oldDate,
           d1.lexicon = d2.lexicon as exact
    from WordOfTheDay w1
    join Definition d1 on w1.definitionId = d1.id
    join Definition d2 on (
      d1.lexicon like concat(d2.lexicon, '%%') or
      d1.lexicon like concat('%%', d2.lexicon) or
      d2.lexicon like concat(d1.lexicon, '%%') or
      d2.lexicon like concat('%%', d1.lexicon)
    )
    join WordOfTheDay w2 on d2.id = w2.definitionId
    where w1.displayDate like '%s-__'
      and w2.displayDate != '0000-00-00'
      and w2.displayDate < '%s'
SQL;

  $dupQuery = sprintf($dupQuery, $yearMonth, $yearMonth);

  $duplicates = Model::factory('WordOfTheDay')->raw_query($dupQuery)->find_array();

  $results = [];
  foreach ($duplicates as $rec) {
    // ignore duplicates in DUPLICATES_TO_IGNORE when they match as prefixes or suffixes,
    // but include them when they match exactly.
    if ($rec['exact'] ||
        !in_array($rec['oldLexicon'], DUPLICATES_TO_IGNORE)) {
      $results[$rec['day']][] = [
        'oldLexicon' => $rec['oldLexicon'],
        'oldDate' => $rec['oldDate'],
        'exact' => $rec['exact'],
      ];
    }
  }
  return $results;
}
