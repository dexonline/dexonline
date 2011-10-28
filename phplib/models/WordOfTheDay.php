<?php

class WordOfTheDay extends BaseObject {
    public static function get($where) {
        $obj = new WordOfTheDay();
        $obj = load($where);
        return $obj->id ? $obj : null;
    }

    public static function getRSSWotD() {
        $query = "SELECT * FROM WordOfTheDay WHERE displayDate > '2011-01-01' AND displayDate < NOW() ORDER BY displayDate DESC LIMIT 25";
        $dbResult = db_execute($query);
        return db_getObjects(new WordOfTheDay(), $dbResult);
    }

/*
    public static function getArchiveWotD() {
        $query = "SELECT displayDate, lexicon, replace(displayDate, '-', '/') linkDate FROM WordOfTheDay W
        JOIN WordOfTheDayRel R ON W.id=R.wotdId
        JOIN Definition D ON R.refId=D.id AND D.status=0 AND R.refType='Definition'
        WHERE displayDate BETWEEN DATE_ADD(LAST_DAY(DATE_SUB(NOW(), INTERVAL 2 MONTH)), INTERVAL 1 DAY) AND  NOW()
        ORDER BY displayDate DESC";

        $dbRes = db_execute($query);
        $dbResult = db_getObjects(new WotDArchive(), $dbRes);

        return $dbResult;
    }
*/

    public static function getArchiveWotD($year, $month) {
        $query = "SELECT displayDate, lexicon, replace(displayDate, '-', '/') linkDate, DAYOFWEEK(displayDate) dayOfWeek, DAYOFMONTH(displayDate) dayOfMonth 
        FROM WordOfTheDay W
        JOIN WordOfTheDayRel R ON W.id=R.wotdId
        JOIN Definition D ON R.refId=D.id AND D.status=0 AND R.refType='Definition'
        WHERE MONTH(displayDate)={$month} AND YEAR(displayDate)={$year}
        ORDER BY displayDate"; //TODO
        $dbRes = db_execute($query);
        $dbResult = db_getObjects(new WotDArchive(), $dbRes);

        return $dbResult;
    }

    public function getTodaysWord() {
        $query = "select id from WordOfTheDay where displayDate=curdate()";
        $dbResult = db_execute($query);
        return $dbResult ? $dbResult->fields('id') : NULL;
    }

    public function getOldWotD($date) {
        $query = "select id from WordOfTheDay where displayDate='$date'";
        $dbResult = db_execute($query);
        return $dbResult ? $dbResult->fields('id') : NULL;
    }

    public function updateTodaysWord() {
        $query = "update WordOfTheDay set displayDate=CURDATE() where displayDate is null order by priority, rand() limit 1";
        db_execute($query);
    }

    public static function getStatus($refId, $refType = 'Definition') {
        $query = "SELECT W.id from WordOfTheDay W JOIN WordOfTheDayRel R ON W.id=R.wotdId WHERE R.refId = $refId AND refType='$refType'";
        $dbResult = db_execute($query);
        return $dbResult ? $dbResult->fields('id') : NULL;
    }

    public function save($userId = NULL) {
        $this->userId = $userId ? $userId : session_getUserId();
        parent::save();

        $obj = new WordOfTheDayRel();
        $obj->refId = $this->defId;
        if ($this->refType == null){
             $obj->refType = 'Definition';
        } else {
             $obj->refType = $this->refType;
        }

        $obj->wotdId = $this->id;
        $obj->save();

        return $obj;
    }

}

?>
