<?php
/**
 * This script grants code, e-mail and volunteer medals.
 **/

require_once __DIR__ . '/../phplib/Core.php';

$OTRS_MAP = [
  'cata' => 1,
  'Octavian.Mocanu' => 4,
  'radu_borza' => 471,
];

Log::notice('started');

$dryRun = false;

foreach ($argv as $i => $arg) {
  if ($i) {
    switch ($arg) {
    case '-n': $dryRun = true; break;
    default: print "Unknown flag $arg -- ignored\n"; exit;
    }
  }
}

if ($dryRun) {
  print "---- DRY RUN ----\n";
}

// OTRS medals
$query = "select users.login, count(*) as count from otrs.article, otrs.article_type, otrs.users " .
  "where article_type_id = article_type.id " .
  "and users.id = article.change_by " .
  "and article_type.name = 'email-external' " .
  "and users.id != 1 " .
  "group by users.id";
$dbResult = DB::execute($query, PDO::FETCH_ASSOC);
foreach ($dbResult as $r) {
  if (array_key_exists($r['login'], $OTRS_MAP)) {
    $user = User::get_by_id($OTRS_MAP[$r['login']]);
    if ($user && $user->id) {
      if (($r['count'] >= 1000) && (!($user->medalMask & Medal::MEDAL_EMAIL_3))) {
        Log::info("Granting {$user->nick} a MEDAL_EMAIL_3");
        $user->medalMask |= Medal::MEDAL_EMAIL_3;
      } else if (($r['count'] < 1000) && ($r['count'] >= 500) && (!($user->medalMask & Medal::MEDAL_EMAIL_2))) {
        Log::info("Granting {$user->nick} a MEDAL_EMAIL_2");
        $user->medalMask |= Medal::MEDAL_EMAIL_2;
      } else if (($r['count'] < 500) && ($r['count'] >= 100) && (!($user->medalMask & Medal::MEDAL_EMAIL_1))) {
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

// Volunteer medals
$topData = TopEntry::getTopData(TopEntry::SORT_CHARS, SORT_DESC, true);
foreach ($topData as $e) {
  $user = User::get_by_nick($e->userNick);
  if ($user && $user->id) {
    if (($e->numChars >= 10000000) && (!($user->medalMask & Medal::MEDAL_VOLUNTEER_5))) {
      Log::info("Granting {$user->nick} a MEDAL_VOLUNTEER_5");
      $user->medalMask |= Medal::MEDAL_VOLUNTEER_5;
    } else if (($e->numChars >= 1000000) && ($e->numChars < 10000000) && (!($user->medalMask & Medal::MEDAL_VOLUNTEER_4))) {
      Log::info("Granting {$user->nick} a MEDAL_VOLUNTEER_4");
      $user->medalMask |= Medal::MEDAL_VOLUNTEER_4;
    } else if (($e->numChars >= 100000) && ($e->numChars < 1000000) && (!($user->medalMask & Medal::MEDAL_VOLUNTEER_3))) {
      Log::info("Granting {$user->nick} a MEDAL_VOLUNTEER_3");
      $user->medalMask |= Medal::MEDAL_VOLUNTEER_3;
    } else if (($e->numChars >= 10000) && ($e->numChars < 100000) && (!($user->medalMask & Medal::MEDAL_VOLUNTEER_2))) {
      Log::info("Granting {$user->nick} a MEDAL_VOLUNTEER_2");
      $user->medalMask |= Medal::MEDAL_VOLUNTEER_2;
    } else if (($e->numChars >= 1000) && ($e->numChars < 10000) && (!($user->medalMask & Medal::MEDAL_VOLUNTEER_1))) {
      Log::info("Granting {$user->nick} a MEDAL_VOLUNTEER_1");
      $user->medalMask |= Medal::MEDAL_VOLUNTEER_1;
    }
    $user->medalMask = Medal::getCanonicalMask($user->medalMask);
    if (!$dryRun) {
      $user->save();
    }
  }
}

Log::notice('finished');
