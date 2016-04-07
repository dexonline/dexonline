<?php
/**
 * This script grants code, e-mail and volunteer medals.
 **/

require_once __DIR__ . '/../phplib/util.php';
define('CODE_AUTHORS_FILE', __DIR__ . '/../docs/codeAuthors.conf');

/* Map of SVN usernames to User.id */
$SVN_MAP = array('alex.grigoras' => 38493,
                 'cata' => 1,
                 'grigoroiualex' => 38357,
                 'mihai17' => 38028,
                 'radu' => 471,
                 'sonia' => 38239,
                 'vially' => 37587);

$OTRS_MAP = array('cata' => 1,
                  'Octavian.Mocanu' => 4,
                  'radu_borza' => 471);

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

// Code medals: code totals reside in $CODE_AUTHORS_FILE
$ini = parse_ini_file(CODE_AUTHORS_FILE, true);
if ($ini) {
  foreach ($ini['authors'] as $svnName => $lines) {
    if (array_key_exists($svnName, $SVN_MAP)) {
      $user = User::get_by_id($SVN_MAP[$svnName]);
      if ($user && $user->id) {
        if (($lines >= 10000) && (!($user->medalMask & Medal::MEDAL_PROGRAMMER_3))) {
          Log::info("Granting {$user->nick} a MEDAL_PROGRAMMER_3");
          $user->medalMask |= Medal::MEDAL_PROGRAMMER_3;
        } else if (($lines < 10000) && ($lines >= 1000) && (!($user->medalMask & Medal::MEDAL_PROGRAMMER_2))) {
          Log::info("Granting {$user->nick} a MEDAL_PROGRAMMER_2");
          $user->medalMask |= Medal::MEDAL_PROGRAMMER_2;
        } else if (($lines < 1000) && ($lines >= 100) && (!($user->medalMask & Medal::MEDAL_PROGRAMMER_1))) {
          Log::info("Granting {$user->nick} a MEDAL_PROGRAMMER_1");
          $user->medalMask |= Medal::MEDAL_PROGRAMMER_1;
        }
        $user->medalMask = Medal::getCanonicalMask($user->medalMask);
        if (!$dryRun) {
          $user->save();
        }
      }
    }
  }
}

// OTRS medals
$query = "select users.login, count(*) as count from otrs.article, otrs.article_type, otrs.users " .
  "where article_type_id = article_type.id " .
  "and users.id = article.change_by " .
  "and article_type.name = 'email-external' " .
  "and users.id != 1 " .
  "group by users.id";
$dbResult = db_execute($query, PDO::FETCH_ASSOC);
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
$topData = TopEntry::getTopData(CRIT_CHARS, SORT_DESC, true);
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

/*********************************************************************/

?>
