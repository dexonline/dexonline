<?php

class ObjectTag extends BaseObject implements DatedObject {
  public static $_table = 'ObjectTag';

  const TYPE_DEFINITION = 1;
  const TYPE_LEXEME = 2;
  const TYPE_MEANING = 3;
  const TYPE_SOURCE = 4;
  const TYPE_DEFINITION_VERSION = 5;
  const TYPE_ENTRY = 6;
  const TYPE_TREE = 7;

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

  static function getLexemeTags($lexemeId) {
    return self::getAllByIdType($lexemeId, self::TYPE_LEXEME);
  }

  static function getMeaningTags($meaningId) {
    return self::getAllByIdType($meaningId, self::TYPE_MEANING);
  }

  static function getSourceTags($sourceId) {
    return self::getAllByIdType($sourceId, self::TYPE_SOURCE);
  }

  static function getEntryTags($entryId) {
    return self::getAllByIdType($entryId, self::TYPE_ENTRY);
  }

  static function getTreeTags($treeId) {
    return self::getAllByIdType($treeId, self::TYPE_TREE);
  }

  // Loads the actual tags, not the ObjectTags
  static function getTags($objectId, $objectType) {
    return Model::factory('Tag')
      ->table_alias('t')
      ->select('t.*')
      ->join('ObjectTag', ['t.id', '=', 'ot.tagId'], 'ot')
      ->where('ot.objectId', $objectId)
      ->where('ot.objectType', $objectType)
      ->order_by_asc('ot.id')
      ->find_many();
  }

 /**
  * Creates a new association if not already exists
  *
  * @param integer $objectType one of self::TYPE_xxxxx constants
  * @param integer $objectId ID of destination Object
  * @param integer $tagId ID of Tag
  */
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

  static function dissociate($objectType, $objectId, $tagId) {
    ObjectTag::delete_all_by_objectType_objectId_tagId($objectType, $objectId, $tagId);
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
