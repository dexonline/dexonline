<?php

class UserWordBookmarkDisplayObject {
  public static function getByUser($userId) {
    $result = array();
    $query = "SELECT UWB.id as id, UWB.userId as userId, UWB.definitionId as definitionId, UWB.createDate as createDate, ".
      "D.htmlRep as htmlRep, D.lexicon as lexicon, D.sourceId as sourceId, D.userId as definitionUserId " .
      "FROM UserWordBookmark UWB, Definition D WHERE UWB.userId = $userId AND UWB.definitionId = D.id ORDER BY lexicon";
    $dbRes = db_getArrayOfRows($query);

    if(count($dbRes) > 0) {
      $defIds = [];
      $definitionUserIds = [];
      $sourceIds = [];

      foreach ($dbRes as $res) {
        $defIds[] = $res['definitionId'];
        $definitionUserIds[] = $res['definitionUserId'];
        $sourceIds[] = $res['sourceId'];
      }

      $userMap = self::mapById(Model::factory('User')->where_in('id', array_unique($definitionUserIds))->find_many());
      $sourceMap = self::mapById(Model::factory('Source')->where_in('id', array_unique($sourceIds))->find_many());
      $commentsMap = self::mapByDefinitionId(Model::factory('Comment')->where_in('definitionId', $defIds)->where('status', Definition::ST_ACTIVE)->find_many());

      foreach ($dbRes as $res) {
        $obj = new UserWordBookmarkDisplayObject();
        $obj->id = $res['id'];
        $obj->comment = null;
        $obj->userId = $res['userId'];
        $obj->definitionId = $res['definitionId'];
        $obj->htmlRep = $res['htmlRep'];
        $obj->user = $userMap[$res['definitionUserId']];
        $obj->source = $sourceMap[$res['sourceId']];
        $obj->lexicon = $res['lexicon'];
        $obj->tags = Tag::loadByDefinitionId($res['definitionId']);
        $obj->createDate = $res['createDate'];
        if(array_key_exists($res['definitionId'], $commentsMap)) {
          $obj->comment = $commentsMap[$res['definitionId']];
          $obj->commentAuthor = User::get_by_id($obj->comment->userId);
        }
        
        $result[] = $obj;
      }
    }

    return $result; 
  }

  private static function mapByDefinitionId($objects) {
    $result = [];
    foreach ($objects as $o) {
      $result[$o->definitionId] = $o;
    }
    return $result;
  }

  private static function mapById($objects) {
    $result = [];
    foreach ($objects as $o) {
      $result[$o->id] = $o;
    }
    return $result;
  }
}

?>
