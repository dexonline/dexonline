<?php
require_once('../../phplib/util.php');
util_assertModerator(PRIV_WOTD);

$year = util_getRequestParameter('year');
$month = util_getRequestParameter('month');
$day = util_getRequestParameter('day');
$artistId = util_getRequestParameter('artistId');

$date = sprintf('%d-%02d-%02d', $year, $month, $day);

if ($artistId) {
  $artist = WotdArtist::get_by_id($artistId);
  WotdAssignment::assign($date, $artist);
  Log::info("assigned {$artist->id} ({$artist->name}) on $date");
} else {
  WotdAssignment::unassign($date);
  Log::info("unassigned $date");
}
