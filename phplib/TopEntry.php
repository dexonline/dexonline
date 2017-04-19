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

  private static function getSqlStatement($manual) {
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
    $conditions = array();
    foreach ($bulk as $tuple) {
      $parts = array();
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

    $query = "select nick, count(*) as NumDefinitions, sum(length(internalRep)) as NumChars, max(createDate) as Timestamp 
    from Definition, User 
    where userId = User.id 
    and $statusClause
    and $clause group by nick";

    return $query;
  }

  private static function loadUnsortedTopData($manual) {
    $statement = self::getSqlStatement($manual);
    
    $dbResult = DB::execute($statement);
    $topEntries = array();
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
  private static function __getUnsortedTopData($manual) {
      return TopEntry::loadUnsortedTopData($manual);
  }

  private static function getUnsortedTopData($manual) {
    $allowHidden = User::can(User::PRIV_VIEW_HIDDEN);
    $data = FileCache::getTop($manual, $allowHidden);
    if (!$data) {
      $data = TopEntry::loadUnsortedTopData($manual);
      FileCache::putTop($data, $manual, $allowHidden);
    }
    return $data;
  }

  /**
   * Returns an array of user stats, sorted according to the given criterion
   * and in the given order. Includes a cache lookup.
   *
   * @param crit  Criterion to sorty by
   * @param ord  Order to sort in (ascending/descending)
   */
  public static function getTopData($crit, $ord, $manual) {
    $topEntries = TopEntry::getUnsortedTopData($manual);

    $nick = array();
    $numWords = array();
    $numChars = array();
    $date = array();
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

?>
