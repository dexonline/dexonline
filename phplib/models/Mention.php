<?php

/**
 * A Mention is a reference from within a Meaning.internalRep, either to
 * another Meaning or to a Tree.
 **/

class Mention extends BaseObject implements DatedObject {
  public static $_table = 'Mention';

  const TYPE_MEANING = 1;
  const TYPE_TREE = 2;

  static function getAllByIdType($objectId, $objectType) {
    return Model::factory('Meaning')
      ->where('objectId', $objectId)
      ->where('objectType', $objectType)
      ->order_by_asc('id')
      ->find_many();
  }

  static function getMeaningMentions($meaningId) {
    return self::getAllByIdType($meaningId, self::TYPE_MEANING);
  }

  static function getTreeMentions($meaningId) {
    return self::getAllByIdType($meaningId, self::TYPE_TREE);
  }

  // Deletes the old mentions and adds the new mentions
  static function wipeAndRecreate($meaningId, $objectType, $objectIds) {
    self::delete_all_by_meaningId_objectType($meaningId, $objectType);

    foreach ($objectIds as $oId) {
      $m = Model::factory('Mention')->create();
      $m->meaningId = $meaningId;
      $m->objectId = $oId;
      $m->objectType = $objectType;
      $m->save();
    }
  }
}

?>
