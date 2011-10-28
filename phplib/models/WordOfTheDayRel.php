<?php

class WordOfTheDayRel extends BaseObject {
    public function countDefs($refId) {
        $query = "SELECT count(*) from WordOfTheDayRel WHERE refId = $refId" ;
        $count = db_getSingleValue($query);
        return $count;
    }

    public static function getRefId($wotdId) {
        $query = "select refId from WordOfTheDayRel where wotdId=$wotdId AND refId<>0";
        $dbResult = db_execute($query);
        return $dbResult ? $dbResult->fields('refId') : NULL;
    }
}

?>
