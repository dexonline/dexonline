<?php

class UserWordBookmarkDisplayObject {
  public static function getByUser($userId) {
    $result = array();
    $query = "SELECT UWB.id as id, UWB.userId as userId, UWB.definitionId as definitionId, UWB.createDate as createDate, ".
      "D.htmlRep as htmlRep, D.lexicon as lexicon " .
      "FROM UserWordBookmark UWB, Definition D WHERE UWB.userId = $userId AND UWB.definitionId = D.id ORDER BY lexicon";
    $dbRes = db_execute($query);
    foreach ($dbRes as $res) {
      $obj = new UserWordBookmarkDisplayObject();
      $obj->id = $res['id'];
      $obj->userId = $res['userId'];
      $obj->definitionId = $res['definitionId'];
      $obj->createDate = $res['createDate'];
      $obj->html = $res['htmlRep'];
      $obj->lexicon = $res['lexicon'];
      $result[] = $obj;
    }
    return $result; 
  }
}

?>
