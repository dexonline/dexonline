<?php

class FullTextIndex extends BaseObject {
  public static $_table = 'FullTextIndex';

  public static function loadDefinitionIdsForLexems($lexemIds, $sourceId) {
    if (empty($lexemIds)) {
      return array();
    }
    $lexemString = implode(',', $lexemIds);
    $sourceClause = $sourceId ? "and D.sourceId = $sourceId" : '';  
    $query = "select distinct definitionId from FullTextIndex F join Definition D on D.id = F.definitionId " .
      "where lexemId in ($lexemString) $sourceClause order by definitionId";
    return db_getArray($query);
  }

  public static function loadPositionsByLexemIdsDefinitionId($lexemIds, $defId) {
    return db_getArray('select distinct position from FullTextIndex where lexemId in (' . implode(',', $lexemIds) .
                       ") and definitionId = $defId order by position");
  }
}

?>
