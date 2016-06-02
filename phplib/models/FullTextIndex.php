<?php

class FullTextIndex extends BaseObject {
  public static $_table = 'FullTextIndex';

  public static function loadDefinitionIdsForLexems($lexemIds, $sourceId) {
    if (empty($lexemIds)) {
      return [];
    }
    $lexemString = implode(',', $lexemIds);
    if ($sourceId) {
      $query = "select distinct definitionId " .
        "from FullTextIndex F " .
        "join Definition D on D.id = F.definitionId " .
        "where lexemId in ($lexemString) " .
        "and D.sourceId = $sourceId " .
        "order by definitionId";
    } else {
      $query = "select distinct definitionId " .
        "from FullTextIndex " .
        "where lexemId in ($lexemString) " .
        "order by definitionId";
    }
    return db_getArray($query);
  }

  // For each defId, build an array of arrays of positions, one array for each lexemId
  public static function loadPositionsByLexemIdsDefinitionIds($lexemMap, $defIds) {
    $positionMap = [];
    foreach ($lexemMap as $lexemIds) {
      if (!empty($lexemIds)) {
        // Load all positions in all definitions for this Lexem set
        $query = sprintf('select distinct definitionId, position from FullTextIndex ' .
                         'where lexemId in (%s) ' .
                         'and definitionId in (%s) ' .
                         'order by definitionId, position',
                         implode(',', $lexemIds), implode(',', $defIds));
        $results = db_getArrayOfRows($query, PDO::FETCH_NUM);
        $results[] = [-1, -1]; // sentinel

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
