<?php

class TopEntry {
  const SORT_CHARS = 1;
  const SORT_WORDS = 2;
  const SORT_NICK = 3;
  const SORT_DATE = 4;

  public $userNick;
  public $numChars;
  public $numDefinitions;
  public $timestamp; // of last submission
  public $days; // since last submission

  private static function getSqlStatement($manual, $lastyear = false) {
/*
 *    nick            source (short)     createDate              1:ratio (3-> 33%, 4 -> 25%)
 */
    $bulk = [
      [null,           "MDN '00",         " = '2007-09-15'",      null],
      [null,           'Petro-Sedim',     null,                   null],
      [null,           'GTA',             null,                   null],
      [null,           'DCR2',            null,                   null],
      [null,           'DOR',             null,                   null],
      ['raduborza',    'DOOM 2',          " > '2013-01-01'",         4],
      [null,           'DRAM',            null,                   null],
      [null,           'DRAM 2015',       null,                   null],
      ['siveco',       null,              null,                   null],
      ['RACAI',        null,              null,                   null],
    ];
    $conditions = [];
    foreach ($bulk as $tuple) {
      $parts = [];
      if ($tuple[0]) {
        $user = User::get_by_nick($tuple[0]);
        $parts[] = "(userId = {$user->id})";
      }
      if ($tuple[1]) {
        $src = Source::get_by_shortName($tuple[1]);
        $parts[] = "(sourceId = {$src->id})";
      }
      if ($tuple[2]) {
        $parts[] = "(left(from_unixtime(createDate), 10)" . $tuple[2] . ")";
      }
      if ($tuple[3]) {
        $parts[] = "(Definition.id%{$tuple[3]}!=0)";
      }
      $conditions[] = '(' . implode(' and ', $parts) . ')';
    }
    $clause = '(' . implode(' or ', $conditions) . ')';
    if ($manual) {
      $clause = "not {$clause}";
    }

    if (User::can(User::PRIV_VIEW_HIDDEN)) {
      $statusClause = sprintf("status in (%d,%d)", Definition::ST_ACTIVE, Definition::ST_HIDDEN);
    } else {
      $statusClause = sprintf("status = %d", Definition::ST_ACTIVE);
    }

    if ($lastyear) {
      $timewindow = 'createDate > unix_timestamp(date_sub(curdate(), interval 1 year))';
    } else {
      $timewindow = '1=1';
    }

    $query = "select nick, count(*) as NumDefinitions, sum(length(internalRep)) as NumChars, max(createDate) as Timestamp 
    from Definition, User 
    where userId = User.id 
    and $statusClause
    and $clause
    and $timewindow 
    group by nick";

    return $query;
  }

  private static function loadUnsortedTopData($manual, $lastyear) {
    $statement = self::getSqlStatement($manual, $lastyear);

    $dbResult = DB::execute($statement);
    $topEntries = [];
    $now = time();

    foreach($dbResult as $row) {
      $topEntry = new TopEntry();
      $topEntry->userNick = $row['nick'];
      $topEntry->numDefinitions = $row['NumDefinitions'];
      $topEntry->numChars = $row['NumChars'];
      $topEntry->timestamp = $row['Timestamp'];
      $topEntry->days = intval(($now - $topEntry->timestamp) / 86400);
      $topEntries[] = $topEntry;
    }

    return $topEntries;
  }

    //for debugging purposes only
  private static function __getUnsortedTopData($manual, $lastyear) {
      return TopEntry::loadUnsortedTopData($manual, $lastyear);
  }

  private static function getUnsortedTopData($manual, $lastyear) {
    $allowHidden = User::can(User::PRIV_VIEW_HIDDEN);
    $data = FileCache::getTop($manual, $allowHidden, $lastyear);

    if (!$data) {
      $data = TopEntry::loadUnsortedTopData($manual, $lastyear);
      FileCache::putTop($data, $manual, $allowHidden, $lastyear);
    }
    return $data;
  }

  /**
   * Returns an array of user stats, sorted according to the given criterion
   * and in the given order. Includes a cache lookup.
   *
   * @param crit  Criterion to sorty by
   * @param ord  Order to sort in (ascending/descending)
   * @param manual If true it is counted only manual contributions
   * @param lastyear If true it is counted only contributions from last year
   */
  static function getTopData($crit, $ord, $manual, $lastyear = false) {
    $topEntries = TopEntry::getUnsortedTopData($manual, $lastyear);

    $nick = [];
    $numWords = [];
    $numChars = [];
    $date = [];
    foreach ($topEntries as $topEntry) {
      $nick[] = $topEntry->userNick;
      $numWords[] = $topEntry->numDefinitions;
      $numChars[] = $topEntry->numChars;
      $date[] = $topEntry->timestamp;
    }

    $ord = (int) $ord;
    if ($crit == self::SORT_CHARS) {
      array_multisort($numChars, SORT_NUMERIC, $ord, $nick, SORT_ASC,
              $topEntries);
    } else if ($crit == self::SORT_WORDS) {
      array_multisort($numWords, SORT_NUMERIC, $ord, $nick, SORT_ASC,
              $topEntries);
    } else if ($crit == self::SORT_NICK) {
      array_multisort($nick, $ord, $topEntries);
    } else /* $crit == self::SORT_DATE */ {
      array_multisort($date, SORT_NUMERIC, $ord, $nick, SORT_ASC, $topEntries);
    }

    return $topEntries;
  }
}
