<?php
/**
 * This script grants artist, code, e-mail and editor medals.
 **/

require_once __DIR__ . '/../lib/Core.php';

const OTRS_MAP = [
  'cata' => 1,
  'Octavian.Mocanu' => 4,
  'radu_borza' => 471,
];

const MIN_DONATION_FOR_MEDAL = 20;
const MIN_DONATION_FOR_HIDDEN_BANNERS = 50;

// user IDs of people for whom we never want to hide banners
const MUST_SEE_BANNERS = [ 1 ];

Log::notice('started');

$opts = getopt('adeno');
$skipArtists = isset($opts['a']);
$skipDonors = isset($opts['d']);
$skipEditors = isset($opts['e']);
$skipOtrs = isset($opts['o']);
$dryRun = isset($opts['n']);

if ($dryRun) {
  print "---- DRY RUN ----\n";
}

// Artist credits
if (!$skipArtists) {
  $levels = Medal::ARTIST_LEVELS;

  $stats = UserStats::getTopArtists();
  // update artists in cache
  FileCache::putArtistTop($stats);

  foreach ($stats as $r) {
    $user = User::get_by_id($r['id']);
    reset($levels);
    while ($r['c'] < current($levels)) {
      next($levels);
    }
    $medal = key($levels);
    grant($user, $medal);
  }
}

// OTRS medals
if (!$skipOtrs) {
  $levels = Medal::EMAIL_LEVELS;

  $otrsResults = UserStats::getEmailContribution();

  foreach ($otrsResults as $r) {
    if (array_key_exists($r['login'], OTRS_MAP)) {
      $user = User::get_by_id(OTRS_MAP[$r['login']]);

      reset($levels);
      while ($r['count'] < current($levels)) {
        next($levels);
      }
      $medal = key($levels);
      grant($user, $medal);
    }
  }
}

// Editor medals
if (!$skipEditors) {
  $levels = Medal::EDITOR_LEVELS;
  $topData = TopEntry::getTopData(TopEntry::SORT_CHARS, SORT_DESC, true);

  foreach ($topData as $e) {
    $user = User::get_by_nick($e->userNick);
    reset($levels);
    while ($e->numChars < current($levels)) {
      next($levels);
    }
    $medal = key($levels);
    grant($user, $medal);
  }
}

// Donor perks
if (!$skipDonors) {
  // Grant medals to users who don't have one, but have donated enough
  $needMedals = UserStats::getDonors(MIN_DONATION_FOR_MEDAL);

  foreach ($needMedals as $u) {
    grant($u, Medal::MEDAL_SPONSOR);
  }

  // Hide banners for users who have donated recently (since their banners were last hidden)
  $oneYearFromNow = strtotime('+1 year');
  $noBanners = UserStats::getBannerFreeAccounts(MIN_DONATION_FOR_HIDDEN_BANNERS, MUST_SEE_BANNERS);

  foreach ($noBanners as $u) {
    Log::info("Hiding banners for {$u->id} {$u->nick}");
    $u->noAdsUntil = $oneYearFromNow;
    if (!$dryRun) {
      $u->save();
    }
  }
}

Log::notice('finished');

/*************************************************************************/

/* Grants the user a medal. If the medal is null or the user already has the
   medal, does nothing. */
function grant($user, $medal) {
  global $dryRun;

  if ($medal && !($user->medalMask & $medal)) {
    Log::info("Granting %s (user ID %d) medal %s",
              $user->nick, $user->id, Medal::getName($medal));

    $user->medalMask |= $medal;
    $user->medalMask = Medal::getCanonicalMask($user->medalMask);

    if (!$dryRun) {
      $user->save();
    }
  }
}
