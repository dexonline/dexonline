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
      "from FullTextIndex " .
      "where lexemModelId in ($lexemString) $sourceClause order by definitionId";
    return db_getArray($query);
  }

  // For each defId, build an array of arrays of positions, one array for each lexemModelId
  public static function loadPositionsByLexemIdsDefinitionIds($lmMap, $defIds) {
    $positionMap = array();
    foreach ($lmMap as $lmIds) {
      if (!empty($lmIds)) {
        // Load all positions in all definitions for this LexemModel set
        $query = sprintf('select distinct definitionId, position from FullTextIndex ' .
                         'where lexemModelId in (%s) ' .
                         'and definitionId in (%s) ' .
                         'order by definitionId, position',
                         implode(',', $lmIds), implode(',', $defIds));
        $results = db_getArrayOfRows($query, PDO::FETCH_NUM);
        $results[] = array(-1, -1); // sentinel

        // Now iterate in defId order and collect position arrays
        $i = 0;
        while ($i < count($results) - 1) {
          $defId = $results[$i][0];
          $positions = array(); // collect positions here
          $j = $i;
          while ($results[$j][0] == $defId) { // same defId
            $positions[] = $results[$j][1];
            $j++;
          }
          if (array_key_exists($defId, $positionMap)) {
            $positionMap[$defId][] = $positions;
          } else {
            $positionMap[$defId] = array($positions);
          }
          $i = $j;
        }
      }
    }
    return $positionMap;
  }
}

?>
