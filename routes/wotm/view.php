<?php

const WOTM_BIG_BANG = '2012-04-01';
$default_month = date('m', strtotime("first day of previous month")); //self explanatory date('Y')
$default_year = date('Y', strtotime("first day of previous month")); //self explanatory date('m')


$year = (int)Request::get('year', $default_year);
$month = (int)Request::get('month', $default_month);
$format = Request::getFormat();

if (!checkDate($month, 1, $year)) {
  Util::redirectToRoute('wotm/view'); // current month
}

//$today = date('Y-m-01', time()); // Always use the first of the month
$today = date('Y-m-01', strtotime("first day of previous month")); // Always use the first of the month
$timestamp = strtotime("{$year}-{$month}");
$monthName = LocaleUtil::getMonthName($month);
$mysqlDate = sprintf('%s-%02s-01', $year, $month);

if ($mysqlDate < WOTM_BIG_BANG || (($mysqlDate > $today) && !User::can(User::PRIV_WOTD))) {
  Util::redirectToRoute('wotm/view');
}

$wotm = WordOfTheMonth::getWotM($mysqlDate);
$def = Definition::get_by_id($wotm->definitionId);

$searchResults = SearchResult::mapDefinitionArray([$def]);

$cYear = date('Y', $timestamp);
$cMonth = date('n', $timestamp);
$nextTS = mktime(0, 0, 0, $cMonth + 1, 1, $cYear);
$prevTS = mktime(0, 0, 0, $cMonth - 1, 1, $cYear);

if ($mysqlDate > WOTM_BIG_BANG) {
  Smart::assign('prevmon', date('Y/m', $prevTS));
}
if ($mysqlDate < $today || User::can(User::PRIV_WOTD)) {
  Smart::assign('nextmon', date('Y/m', $nextTS));
}

Smart::assign([
  'year' => $year,
  'month' => $month,
  'monthName' => $monthName,
  'imageUrl' => $wotm->getLargeThumbUrl(),
  'artist' => $wotm->getArtist(),
  'reason' => $wotm->description,
  'searchResult' => array_pop($searchResults),
]);


switch ($format['name']) {
  case 'xml':
  case 'json':
    header('Content-type: '.$format['content_type']);
    Smart::displayWithoutSkin($format['tpl_path'].'/wotm.tpl');
    break;
  default:
    Smart::display('wotm/view.tpl');
}
