<?php
require_once '../../phplib/Core.php';
User::mustHave(User::PRIV_WOTD);

$year = Request::get('year');
$month = Request::get('month');
$day = Request::get('day');
$artistId = Request::get('artistId');

$date = sprintf('%d-%02d-%02d', $year, $month, $day);

if ($artistId) {
  $artist = WotdArtist::get_by_id($artistId);
  WotdAssignment::assign($date, $artist);
  Log::info("assigned {$artist->id} ({$artist->name}) on $date");
} else {
  WotdAssignment::unassign($date);
  Log::info("unassigned $date");
}
