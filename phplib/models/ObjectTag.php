<?php

class ObjectTag extends BaseObject implements DatedObject {
  public static $_table = 'ObjectTag';

  const TYPE_DEFINITION = 1;
  const TYPE_LEXEM = 2;
  const TYPE_MEANING = 3;

  static function getDefinitionTags($definitionId) {
    return self::get_all_by_objectId_objectType($definitionId, self::TYPE_DEFINITION);
  }

  static function getLexemTags($lexemId) {
    return self::get_all_by_objectId_objectType($lexemId, self::TYPE_LEXEM);
  }

  static function getMeaningTags($meaningId) {
    return self::get_all_by_objectId_objectType($meaningId, self::TYPE_MEANING);
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
