<?php

class FullTextIndex extends BaseObject {
  public static $_table = 'FullTextIndex';

  static function loadDefinitionIdsForLexemes($lexemeIds, $sourceId) {
    if (empty($lexemeIds)) {
      return [];
    }
    $lexemeString = implode(',', $lexemeIds);
    if ($sourceId) {
      $query = "select distinct definitionId " .
        "from FullTextIndex F " .
        "join Definition D on D.id = F.definitionId " .
        "where lexemeId in ($lexemeString) " .
        "and D.sourceId = $sourceId " .
        "order by definitionId";
    } else {
      $query = "select distinct definitionId " .
        "from FullTextIndex " .
        "where lexemeId in ($lexemeString) " .
        "order by definitionId";
    }
    return DB::getArray($query);
  }

  // For each defId, build an array of arrays of positions, one array for each lexemeId
  static function loadPositionsByLexemeIdsDefinitionIds($lexemeMap, $defIds) {
    $positionMap = [];
    foreach ($lexemeMap as $lexemeIds) {
      if (!empty($lexemeIds)) {
        // Load all positions in all definitions for this Lexeme set
        $query = sprintf('select distinct definitionId, position from FullTextIndex ' .
                         'where lexemeId in (%s) ' .
                         'and definitionId in (%s) ' .
                         'order by definitionId, position',
                         implode(',', $lexemeIds), implode(',', $defIds));
        $results = DB::getArrayOfRows($query, PDO::FETCH_NUM);
        $results[] = [-1, -1]; // sentinel

        // Now iterate in defId order and collect position arrays
        $i = 0;
        while ($i < count($results) - 1) {
          $defId = $results[$i][0];
          $positions = []; // collect positions here
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
