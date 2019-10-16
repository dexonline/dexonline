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
  $today = date('Y-m-d');
  $levels = Medal::ARTIST_LEVELS;

  $stats = Model::factory('User')
    ->table_alias('u')
    ->select('u.id')
    ->select_expr('count(*)', 'c')
    ->join('WotdArtist', ['a.userId', '=', 'u.id'], 'a')
    ->join('WotdAssignment', ['s.artistId', '=', 'a.id'], 's')
    ->where_lte('s.date', $today)
    ->group_by('u.id')
    ->find_array();

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

  $query = 'select users.login, count(*) as count ' .
    'from otrs.users ' .
    'join otrs.article on users.id = article.change_by ' .
    'where users.id != 1 ' .
    'group by users.id';
  $dbResult = DB::execute($query, PDO::FETCH_ASSOC);

  foreach ($dbResult as $r) {
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
  $needMedals = Model::factory('User')
              ->table_alias('u')
              ->select('u.*')
              ->select_expr('sum(d.amount)', 'total')
              ->distinct()
              ->join('Donation', ['d.email', '=', 'u.email'], 'd')
              ->where('anonymousDonor', 0)
              ->where_raw('!(medalMask & ?)', Medal::MEDAL_SPONSOR)
              ->group_by('u.id')
              ->having_raw('total >= ?', MIN_DONATION_FOR_MEDAL)
              ->find_many();
  foreach ($needMedals as $u) {
    Log::info("Granting {$u->id} {$u->nick} a sponsor medal");
    $u->medalMask |= Medal::MEDAL_SPONSOR;
    if (!$dryRun) {
      $u->save();
    }
  }

  // Hide banners for users who have donated recently (since their banners were last hidden)
  $oneYearFromNow = strtotime('+1 year');
  $noBanners = Model::factory('User')
             ->table_alias('u')
             ->select('u.*')
             ->select_expr('sum(d.amount)', 'total')
             ->distinct()
             ->join('Donation', ['d.email', '=', 'u.email'], 'd')
             ->where_not_in('u.id', MUST_SEE_BANNERS)
             ->where_raw('d.createDate >= u.noAdsUntil')
             ->group_by('u.id')
             ->having_raw('total >= ?', MIN_DONATION_FOR_HIDDEN_BANNERS)
             ->find_many();
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
