<?php

class FullTextIndex extends BaseObject {
  public static $_table = 'FullTextIndex';

  public static function loadDefinitionIdsForLexems($lexemIds) {
    if (empty($lexemIds)) {
      return array();
    }
    $query = 'select distinct definitionId from FullTextIndex where lexemId in (' . implode(',', $lexemIds) . ') order by definitionId';
    return db_getArray($query);
  }

  public static function loadPositionsByLexemIdsDefinitionId($lexemIds, $defId) {
    return db_getArray('select distinct position from FullTextIndex where lexemId in (' . implode(',', $lexemIds) .
                       ") and definitionId = $defId order by position");
  }
}

?>
