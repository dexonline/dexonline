<?php
/**
 * This script grants code, e-mail and volunteer medals.
 **/

require_once __DIR__ . '/../phplib/Core.php';

const OTRS_MAP = [
  'cata' => 1,
  'Octavian.Mocanu' => 4,
  'radu_borza' => 471,
];

const VOLUNTEER_LEVELS = [
  Medal::MEDAL_VOLUNTEER_5 => 10000000,
  Medal::MEDAL_VOLUNTEER_4 => 1000000,
  Medal::MEDAL_VOLUNTEER_3 => 100000,
  Medal::MEDAL_VOLUNTEER_2 => 10000,
  Medal::MEDAL_VOLUNTEER_1 => 1000,
];

Log::notice('started');

$opts = getopt('dnov');
$skipDonors = isset($opts['d']);
$skipOtrs = isset($opts['o']);
$skipVolunteers = isset($opts['v']);
$dryRun = isset($opts['n']);

if ($dryRun) {
  print "---- DRY RUN ----\n";
}

// OTRS medals
if (!$skipOtrs) {
  $query = "select users.login, count(*) as count from otrs.article, otrs.article_type, otrs.users " .
         "where article_type_id = article_type.id " .
         "and users.id = article.change_by " .
         "and article_type.name = 'email-external' " .
         "and users.id != 1 " .
         "group by users.id";
  $dbResult = DB::execute($query, PDO::FETCH_ASSOC);
  foreach ($dbResult as $r) {
    if (array_key_exists($r['login'], OTRS_MAP)) {
      $user = User::get_by_id(OTRS_MAP[$r['login']]);
      if ($user && $user->id) {
        if (($r['count'] >= 1000) &&
            (!($user->medalMask & Medal::MEDAL_EMAIL_3))) {
          Log::info("Granting {$user->nick} a MEDAL_EMAIL_3");
          $user->medalMask |= Medal::MEDAL_EMAIL_3;
        } else if (($r['count'] < 1000) &&
                   ($r['count'] >= 500) &&
                   (!($user->medalMask & Medal::MEDAL_EMAIL_2))) {
          Log::info("Granting {$user->nick} a MEDAL_EMAIL_2");
          $user->medalMask |= Medal::MEDAL_EMAIL_2;
        } else if (($r['count'] < 500) &&
                   ($r['count'] >= 100) &&
                   (!($user->medalMask & Medal::MEDAL_EMAIL_1))) {
          Log::info("Granting {$user->nick} a MEDAL_EMAIL_1");
          $user->medalMask |= Medal::MEDAL_EMAIL_1;
        }
        $user->medalMask = Medal::getCanonicalMask($user->medalMask);
        if (!$dryRun) {
          $user->save();
        }
      }
    }
  }
}

// Volunteer medals
if (!$skipVolunteers) {
  $l = VOLUNTEER_LEVELS;
  $minCharsForMedal = end($l);
  $topData = TopEntry::getTopData(TopEntry::SORT_CHARS, SORT_DESC, true);

  foreach ($topData as $e) {
    $user = User::get_by_nick($e->userNick);
    if ($user && $user->id && $e->numChars >= $minCharsForMedal) {
      // determine the current medal level
      reset($l);
      while ($e->numChars < current($l)) {
        next($l);
      }
      $medal = key($l);

      // grant it if the user doesn't have it
      if (!($user->medalMask & $medal)) {
        Log::info('Granting %s a medal for %s', $user->nick, Medal::getName($medal));
        $user->medalMask |= $medal;
        $user->medalMask = Medal::getCanonicalMask($user->medalMask);
        if (!$dryRun) {
          $user->save();
        }
      }
    }
  }
}

Log::notice('finished');
