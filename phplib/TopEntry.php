<?php

class TopEntry {
  public $userNick;
  public $numChars;
  public $numDefinitions;
  public $timestamp; // of last submission
  public $days; // since last submission

  private static function getSqlStatement($manual) {
    $bulk = array(array(null, Source::get("shortName = 'MDN'"), '2007-09-15'),
                  array(null, Source::get("shortName = 'Petro-Sedim'"), null),
                  array(null, Source::get("shortName = 'GTA'"), null),
                  array(null, Source::get("shortName = 'DCR2'"), null),
                  array(User::get("nick = 'siveco'"), null, null),
                  array(User::get("nick = 'RACAI'"), null, null),
                  );
    $conditions = array();
    foreach ($bulk as $tuple) {
      $parts = array();
      if ($tuple[0]) {
        $parts[] = "(userId = {$tuple[0]->id})";
      }
      if ($tuple[1]) {
        $parts[] = "(sourceId = {$tuple[1]->id})";
      }
      if ($tuple[2]) {
        $parts[] = "(left(from_unixtime(createDate), 10) = '{$tuple[2]}')";
      }
      $conditions[] = '(' . implode(' and ', $parts) . ')';
    }
    $clause = '(' . implode(' or ', $conditions) . ')';
    if ($manual) {
      $clause = "not {$clause}";
    }

    return "select nick, count(*) as NumDefinitions, sum(length(internalRep)) as NumChars, max(createDate) as Timestamp from Definition, User where userId = User.id and status = 0 and $clause group by nick";
  }

  private static function loadUnsortedTopData($manual) {
    $statement = self::getSqlStatement($manual);
    $dbResult = db_execute($statement);
    $topEntries = array();
    $now = time();

    while (!$dbResult->EOF) {
      $topEntry = new TopEntry();
      $topEntry->userNick = $dbResult->fields['nick'];
      $topEntry->numDefinitions = $dbResult->fields['NumDefinitions'];
      $topEntry->numChars = $dbResult->fields['NumChars'];
      $topEntry->timestamp = $dbResult->fields['Timestamp'];
      $topEntry->days = intval(($now - $topEntry->timestamp) / 86400);
      $topEntries[] = $topEntry;
      $dbResult->MoveNext();
    }

    return $topEntries;
  }

  private static function getUnsortedTopData($manual) {
    $data = fileCache_getTop($manual);
    if (!$data) {
      $data = TopEntry::loadUnsortedTopData($manual);
      fileCache_putTop($data, $manual);
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
    if ($crit == CRIT_CHARS) {
      array_multisort($numChars, SORT_NUMERIC, $ord, $nick, SORT_ASC,
              $topEntries);
    } else if ($crit == CRIT_WORDS) {
      array_multisort($numWords, SORT_NUMERIC, $ord, $nick, SORT_ASC,
              $topEntries);
    } else if ($crit == CRIT_NICK) {
      array_multisort($nick, $ord, $topEntries);
    } else /* $crit == CRIT_DATE */ {
      array_multisort($date, SORT_NUMERIC, $ord, $nick, SORT_ASC, $topEntries);
    }
    
    return $topEntries;
  }
}

?>