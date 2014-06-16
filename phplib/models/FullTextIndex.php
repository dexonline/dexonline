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
    return db_getArray($query);
  }

  public static function loadPositionsByLexemIdsDefinitionId($lexemModelIds, $defId) {
    $query = sprintf('select distinct position from FullTextIndex ' .
                     'where lexemModelId in (%s) ' .
                     'and definitionId = %d ' .
                     'order by position',
                     implode(',', $lexemModelIds), $defId);
    return db_getArray($query);
  }
}

?>
