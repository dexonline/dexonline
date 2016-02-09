<?php
require_once('../../phplib/util.php');
util_assertModerator(PRIV_WOTD);

$year = util_getRequestParameter('year');
$month = util_getRequestParameter('month');

if (!$year) {
  $year = date('Y');
}
if (!$month) {
  $month = date('n');
}

// get the localized month name and year, e.g. "februarie 2016"
$month = str_pad($month, 2, '0', STR_PAD_LEFT);
$timestamp = strtotime("{$year}-{$month}-01");
$dateString = strftime('%B %Y', $timestamp);
$numDays = date('t', $timestamp);

// create a day => artistMap, one for each day, with 0 if no artist is assigned
$was = Model::factory('WotdAssignment')
     ->where_like('date', "{$year}-{$month}-%")
     ->order_by_asc('date')
     ->find_many();

$artists = [];
for ($i = 1; $i <= $numDays; $i++) {
  $artists[$i] = 0;
}
foreach ($was as $wa) {
  $day = date('j', strtotime($wa->date));
  $artists[$day] = $wa->artistId;
}

$response = [
  'year' => (int)$year,
  'month' => (int)$month,
  'date' => $dateString,
  'artists' => $artists,
];

header('Content-type: application/json');
print json_encode($response);
