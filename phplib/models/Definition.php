<?php

class Definition extends BaseObject {
  function __construct() {
    parent::__construct();
    $this->displayed = 0;
    $this->status = ST_PENDING;
  }

  public static function get($where) {
    $obj = new Definition();
    $obj->load($where);
    return $obj->id ? $obj : null;
  }

  public static function loadByLexemId($lexemId) {
    $dbResult = db_execute("select Definition.* from Definition, LexemDefinitionMap where Definition.id = definitionId " .
                           "and LexemDefinitionMap.lexemId = {$lexemId} and status in (0, 1) order by sourceId");
    return db_getObjects(new Definition(), $dbResult);
  }

  public static function countAssociated() {
    // same as select count(distinct definitionId) from LexemDefinitionMap, only faster.
    return db_getSingleValue('select count(*) from (select count(*) from LexemDefinitionMap group by definitionId) as someLabel');
  }

  // Counts the unassociated definitions in the active or temporary statuses.
  public static function countUnassociated() {
    $all = db_getSingleValue("select count(*) from Definition");
    return $all - self::countAssociated() - self::countByStatus(ST_DELETED);
  }

  public static function countByStatus($status) {
    return db_getSingleValue("select count(*) from Definition where status = $status");
  }

  public static function loadForLexems(&$lexems, $sourceId, $preferredWord, $exclude_unofficial = false) {
    if (!count($lexems)) {
      return array();
    }
    $lexemIds = '';
    foreach ($lexems as $lexem) {
      if ($lexemIds) {
        $lexemIds .= ',';
      }
      $lexemIds .= $lexem->id;
    }

    $sourceClause = $sourceId ? "and D.sourceId = $sourceId" : '';
    $excludeClause = $exclude_unofficial ? "and S.isOfficial <> 0 " : '';
    $query = sprintf("select distinct D.* from Definition D, LexemDefinitionMap L, Source S " .
                     "where D.id = L.definitionId and L.lexemId in (%s) and D.sourceId = S.id and D.status = 0 %s %s " .
                     "order by S.isOfficial desc, (D.lexicon = '%s') desc, S.displayOrder, D.lexicon",
                     $lexemIds, $excludeClause, $sourceClause, $preferredWord);
/*echo "<pre>";
print_r($query);
echo "</pre>";*/
    $dbResult = db_execute($query);
    return db_getObjects(new Definition(), $dbResult);
  }

  public static function searchLexemId($lexemId, $exclude_unofficial = false) {
    $excludeClause = $exclude_unofficial ? "and S.isOfficial <> 0 " : '';
    $dbResult = db_execute("select D.* from Definition D, LexemDefinitionMap L, Source S where D.id = L.definitionId " .
                           "and D.sourceId = S.id and L.lexemId = '$lexemId' $excludeClause and D.status = 0 " .
                           "order by S.isOfficial desc, S.displayOrder, D.lexicon");
    return db_getObjects(new Definition(), $dbResult);
  }

  public static function searchFullText($words, $hasDiacritics) {
    $intersection = null;

    $matchingLexems = array();
    foreach ($words as $word) {
      $lexems = Lexem::searchInflectedForms($word, $hasDiacritics);
      $lexemIds = '';
      foreach ($lexems as $lexem) {
        if ($lexemIds) {
          $lexemIds .= ',';
        }
        $lexemIds .= $lexem->id;
      }
      $matchingLexems[] = $lexemIds;
    }

    foreach ($words as $i => $word) {
      // Load all the definitions for any possible lexem for this word.
      $lexemIds = $matchingLexems[$i];
      $defIds = FullTextIndex::loadDefinitionIdsForLexems($lexemIds);
      debug_resetClock();
      $intersection = ($intersection === null)
        ? $defIds
        : util_intersectArrays($intersection, $defIds);
      debug_stopClock("Intersected with lexems for $word");
    }
    if ($intersection === null) { // This can happen when the query is all stopwords
      $intersection = array();
    }

    $shortestInvervals = array();

    debug_resetClock();
    // Now compute a score for every definition
    foreach ($intersection as $defId) {
      // Compute the position matrix (for every word, load all the matching
      // positions)
      $p = array();
      foreach ($matchingLexems as $lexemIds) {
        $p[] = FullTextIndex::loadPositionsByLexemIdsDefinitionId($lexemIds, $defId);
      }
      $shortestIntervals[] = util_findSnippet($p);
    }

    if ($intersection) {
      array_multisort($shortestIntervals, $intersection);
    }
    debug_stopClock("Computed score for every definition");

    return $intersection;
  }

  public static function searchModerator($cuv, $hasDiacritics, $sourceId, $status, $userId, $beginTime, $endTime) {
    $regexp = StringUtil::dexRegexpToMysqlRegexp($cuv);
    $sourceClause = $sourceId ? "and Definition.sourceId = $sourceId" : '';
    $userClause = $userId ? "and Definition.userId = $userId" : '';

    if ($status == ST_DELETED) {
      // Deleted definitions are not associated with any lexem
      $collate = $hasDiacritics ? '' : 'collate utf8_general_ci';
      return db_find(new Definition(), "lexicon $collate $regexp and status = " . ST_DELETED . " and createDate between $beginTime and $endTime " .
                     "$sourceClause $userClause order by lexicon, sourceId limit 500");
    } else {
      $field = $hasDiacritics ? 'formNoAccent' : 'formUtf8General';
      $dbResult = db_execute("select distinct Definition.* from Lexem join LexemDefinitionMap on Lexem.id = LexemDefinitionMap.lexemId " .
                             "join Definition on LexemDefinitionMap.definitionId = Definition.id where $field $regexp " .
                             "and Definition.status = $status and Definition.createDate >= $beginTime and Definition.createDate <= $endTime " .
                             "$sourceClause $userClause order by lexicon, sourceId limit 500");
      return db_getObjects(new Definition(), $dbResult);
    }
  }

  // Return definitions that are associated with at least two of the lexems
  public static function searchMultipleWords($words, $hasDiacritics, $sourceId, $exclude_unofficial) {
    $defCounts = array();
    foreach ($words as $word) {
      $lexems = Lexem::searchInflectedForms($word, $hasDiacritics);
      if (count($lexems)) {
        $definitions = self::loadForLexems($lexems, $sourceId, $word, $exclude_unofficial);
        foreach ($definitions as $def) {
          $defCounts[$def->id] = array_key_exists($def->id, $defCounts) ? $defCounts[$def->id] + 1 : 1;
        }
      }
    }
    arsort($defCounts);

    $result = array();
    foreach ($defCounts as $defId => $cnt) {
      if ($cnt >= 2) {
        $result[] = self::get("id = {$defId}");
      } else {
        break;
      }
    }
    return $result;
  }

  public static function getWordCount() {
    $cachedWordCount = fileCache_getWordCount();
    if ($cachedWordCount) {
      return $cachedWordCount;
    }
    $result = self::countByStatus(ST_ACTIVE);
    fileCache_putWordCount($result);
    return $result;
  }

  public static function getWordCountLastMonth() {
    $cachedWordCountLastMonth = fileCache_getWordCountLastMonth();
    if ($cachedWordCountLastMonth) {
      return $cachedWordCountLastMonth;
    }
    $last_month = time() - 30 * 86400;
    $result = db_getSingleValue("select count(*) from Definition where createDate >= $last_month and status = " . ST_ACTIVE);
    fileCache_putWordCountLastMonth($result);
    return $result;
  }

  public function incrementDisplayCount(&$definitions) {
    if (count($definitions)) {
      $ids = array();
      foreach($definitions as $def) {
        $ids[] = $def->id;
      }
      $idString = implode(',', $ids);
      db_execute("update Definition set displayed = displayed + 1 where id in ({$idString})");
    }
  }

  public static function updateModDate($defId) {
    $modDate = time();
    return db_execute("update Definition set modDate = '$modDate' where id = '$defId'");
  }

  public function save() {
    $this->modUserId = session_getUserId();
    return parent::save();
  }
}

?>
