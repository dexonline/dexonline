<?php

class FullTextIndex extends BaseObject {
  // Takes a comma-separated string of lexem ids
  public static function loadDefinitionIdsForLexems($lexemIds) {
    if (!$lexemIds) {
      return array();
    }
    return db_getArray(db_execute("select distinct definitionId from FullTextIndex where lexemId in ($lexemIds) order by definitionId"));
  }

  public static function loadPositionsByLexemIdsDefinitionId($lexemIds, $defId) {
    return db_getArray(db_execute("select distinct position from FullTextIndex where lexemId in ($lexemIds) and definitionId = $defId order by position"));
  }
}

?>
