<?php

class ObjectTag extends BaseObject implements DatedObject {
  public static $_table = 'ObjectTag';

  const TYPE_DEFINITION = 1;
  const TYPE_LEXEM = 2;
  const TYPE_MEANING = 3;
  const TYPE_SOURCE = 4;
  const TYPE_DEFINITION_VERSION = 5;

  static function getAllByIdType($objectId, $objectType) {
    return Model::factory('ObjectTag')
      ->where('objectId', $objectId)
      ->where('objectType', $objectType)
      ->order_by_asc('id')
      ->find_many();
  }

  static function getDefinitionTags($definitionId) {
    return self::getAllByIdType($definitionId, self::TYPE_DEFINITION);
  }

  static function getLexemTags($lexemId) {
    return self::getAllByIdType($lexemId, self::TYPE_LEXEM);
  }

  static function getMeaningTags($meaningId) {
    return self::getAllByIdType($meaningId, self::TYPE_MEANING);
  }

  static function getSourceTags($sourceId) {
    return self::getAllByIdType($sourceId, self::TYPE_SOURCE);
  }

  // Loads the actual tags, not the ObjectTags
  static function getDefinitionVersionTags($dvId) {
    return Model::factory('Tag')
      ->table_alias('t')
      ->select('t.*')
      ->join('ObjectTag', ['t.id', '=', 'ot.tagId'], 'ot')
      ->where('ot.objectType', ObjectTag::TYPE_DEFINITION_VERSION)
      ->where('ot.objectId', $dvId)
      ->order_by_asc('ot.id')
      ->find_many();
  }

  static function associate($objectType, $objectId, $tagId) {
    // The association should not already exist
    if (!self::get_by_objectType_objectId_tagId($objectType, $objectId, $tagId)) {
      $ot = Model::factory('ObjectTag')->create();
      $ot->objectType = $objectType;
      $ot->objectId = $objectId;
      $ot->tagId = $tagId;
      $ot->save();
    }
  }

  // Deletes the old tags and adds the new tags.
  static function wipeAndRecreate($objectId, $objectType, $tagIds) {
    self::delete_all_by_objectId_objectType($objectId, $objectType);

    foreach ($tagIds as $tagId) {
      $ot = Model::factory('ObjectTag')->create();
      $ot->objectId = $objectId;
      $ot->objectType = $objectType;
      $ot->tagId = $tagId;
      $ot->save();
    }
  }
}

?>
