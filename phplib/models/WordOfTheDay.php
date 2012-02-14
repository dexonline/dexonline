<?php

class WordOfTheDay extends BaseObject {
  public static $_table = 'WordOfTheDay';

  public static function getRSSWotD() {
    return Model::factory('WordOfTheDay')->where_gt('displayDate', '2011-01-01')->where_raw('displayDate < NOW()')
      ->order_by_desc('displayDate')->limit(25)->find_many();
  }

  public static function getArchiveWotD($year, $month) {
    $query = "SELECT displayDate, lexicon, replace(displayDate, '-', '/') linkDate, DAYOFWEEK(displayDate) dayOfWeek, DAYOFMONTH(displayDate) dayOfMonth 
        FROM WordOfTheDay W
        JOIN WordOfTheDayRel R ON W.id=R.wotdId
        JOIN Definition D ON R.refId=D.id AND D.status=0 AND R.refType='Definition'
        WHERE MONTH(displayDate)={$month} AND YEAR(displayDate)={$year}
        ORDER BY displayDate"; //TODO
    $dbRes = db_execute($query);
    $results = array();
    foreach ($dbRes as $row) {
      $wotda = new WotDArchive();
      $wotda->set($row);
      $results[] = $wotda;
    }

    return $results;
  }

  public static function getTodaysWord() {
    return Model::factory('WordOfTheDay')->select('id')->where_raw('displayDate = curdate()')->find_one();
  }

  public static function updateTodaysWord() {
    db_execute('update WordOfTheDay set displayDate=curdate() where displayDate is null order by priority, rand() limit 1');
  }

  public static function getStatus($refId, $refType = 'Definition') {
    $result = Model::factory('WordOfTheDay')->table_alias('W')->select('W.id')->join('WordOfTheDayRel', 'W.id = R.wotdId', 'R')
      ->where('R.refId', $refId)->where('refType', $refType)->find_one();
    return $result ? $result->id : NULL;
  }
}

?>
