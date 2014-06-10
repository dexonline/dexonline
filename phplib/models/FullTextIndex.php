<?php

class FullTextIndex extends BaseObject {
  public static $_table = 'FullTextIndex';

  public static function loadDefinitionIdsForLexemModels($lexemModelIds, $sourceId) {
    if (empty($lexemModelIds)) {
      return array();
    }
    $lexemString = implode(',', $lexemModelIds);
    $sourceClause = $sourceId ? "and D.sourceId = $sourceId" : '';  
    $query = "select distinct definitionId " .
      "from FullTextIndex F " .
      "join Definition D on D.id = F.definitionId " .
      "where F.lexemModelId in ($lexemString) $sourceClause order by F.definitionId";
    var_dump($query);
    return db_getArray($query);
  }

  public static function loadPositionsByLexemIdsDefinitionId($lexemIds, $defId) {
    return db_getArray('select distinct position from FullTextIndex where lexemId in (' . implode(',', $lexemIds) .
                       ") and definitionId = $defId order by position");
  }
}

?>
