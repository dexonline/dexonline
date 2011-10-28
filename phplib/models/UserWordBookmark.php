<?php

class UserWordBookmark extends BaseObject {
  public static function getStatus($userId, $definitionId) {
    $query = "SELECT id FROM UserWordBookmark WHERE userId = $userId AND definitionId = $definitionId";
    $dbResult = db_execute($query);
    return $dbResult ? $dbResult->fields('id') : NULL;
  }

  public static function loadByUserIdAndDefinitionId($userId, $definitionId) {
    $query = "SELECT * FROM UserWordBookmark WHERE userId = $userId AND definitionId = $definitionId";
    $dbResult = db_execute($query);
    return db_getObjects(new UserWordBookmark(), $dbResult);
  }
}

?>
