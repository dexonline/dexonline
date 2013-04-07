<?php

class Definition extends BaseObject implements DatedObject {
  public static $_table = 'Definition';


  public static function get_by_id($id) {
      if (util_isModerator(PRIV_ADMIN)) {
        return parent::get_by_id($id);
        //return Model::factory('Definition')->where('id',$id)->where_not_equal('status', ST_HIDDEN)->find_one();
      }
      else {
        return Model::factory('Definition')->where('id',$id)->where_not_equal('status', ST_HIDDEN)->find_one();
      }
  }

  public static function loadByLexemId($lexemId) {
    return Model::factory('Definition')->select('Definition.*')->join('LexemDefinitionMap', array('Definition.id', '=', 'definitionId'))
      ->where('LexemDefinitionMap.lexemId', $lexemId)->where_not_equal('status', ST_DELETED)->order_by_asc('sourceId')->find_many();
  }

  public static function countAssociated() {
    // same as select count(distinct definitionId) from LexemDefinitionMap, only faster.
    $r =  Model::factory('Definition')
      ->raw_query('select count(*) as c from (select count(*) from LexemDefinitionMap group by definitionId) as someLabel', null)
      ->find_one();
    return $r->c;
  }

  public static function getListOfWordsFromSources($wordStart, $wordEnd, $sources) {
    return Model::factory('Definition')
      ->select('Definition.*')
      ->join('LexemDefinitionMap', array('Definition.id', '=', 'definitionId'))
      ->where_gte('lexicon', $wordStart)
      ->where_lte('lexicon', $wordEnd)
      ->where_in('sourceId', $sources)
      ->where('status', ST_ACTIVE)
      ->order_by_asc('lexicon')
      ->order_by_asc('sourceId')
      ->find_many();
  }

  // Counts the unassociated definitions in the active or temporary statuses.
  public static function countUnassociated() {
    $all = Model::factory('Definition')->count();
    return $all - self::countAssociated() - self::countByStatus(ST_DELETED);
  }

  public static function countByStatus($status) {
    return Model::factory('Definition')->where('status', $status)->count();
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
    $statusClause = util_isModerator(PRIV_VIEW_HIDDEN) ? sprintf("and D.status in (%d,%d)", ST_ACTIVE, ST_HIDDEN) : sprintf("and D.status = %d", ST_ACTIVE);
    // TODO Using the number constants is not a good practice
    return ORM::for_table('Definition')
      ->raw_query("select distinct D.* from Definition D, LexemDefinitionMap L, Source S " .
                  "where D.id = L.definitionId and L.lexemId in ($lexemIds) and D.sourceId = S.id $statusClause $excludeClause $sourceClause " .
                  "order by S.isOfficial desc, (D.lexicon = '$preferredWord') desc, S.displayOrder, D.lexicon", null)
      ->find_many();
  }

  public static function searchLexemId($lexemId, $exclude_unofficial = false) {
    $excludeClause = $exclude_unofficial ? "and S.isOfficial <> 0 " : '';
    $statusClause = util_isModerator(PRIV_VIEW_HIDDEN) ? sprintf("and D.status in (%d,%d)", ST_ACTIVE, ST_HIDDEN) : sprintf("and D.status = %d", ST_ACTIVE);
    return Model::factory('Definition')
      ->raw_query("select D.* from Definition D, LexemDefinitionMap L, Source S where D.id = L.definitionId " .
                  "and D.sourceId = S.id and L.lexemId = '$lexemId' $excludeClause $statusClause " .
                  "order by S.isOfficial desc, S.displayOrder, D.lexicon", null)
      ->find_many();
  }

  public static function searchFullText($words, $hasDiacritics, $sourceId) {
    $intersection = null;

    $matchingLexems = array();
    foreach ($words as $word) {
      $lexems = Lexem::searchInflectedForms($word, $hasDiacritics);
      $lexemIds = array();
      foreach ($lexems as $lexem) {
        $lexemIds[] = $lexem->id;
      }
      $matchingLexems[] = $lexemIds;
    }

    foreach ($words as $i => $word) {
      // Load all the definitions for any possible lexem for this word.
      $lexemIds = $matchingLexems[$i];
      $defIds = FullTextIndex::loadDefinitionIdsForLexems($lexemIds, $sourceId);
      DebugInfo::resetClock();
      $intersection = ($intersection === null)
        ? $defIds
        : util_intersectArrays($intersection, $defIds);
      DebugInfo::stopClock("Intersected with lexems for $word");
    }
    if (empty($intersection)) { // This can happen when the query is all stopwords or the source selection produces no results
      return array();
    }

    if (count($words) == 1) {
      // For single-word queries, sort the result set by lexicon.
      $objects = Model::factory('Definition')->select('id')->where('status', ST_ACTIVE)->where_in('id', $intersection)->order_by_asc('lexicon')->find_many();
      return array_map(function($def) { return $def->id; }, $objects);
    }

    $shortestInvervals = array();

    DebugInfo::resetClock();
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
    DebugInfo::stopClock("Computed score for every definition");

    return $intersection;
  }

  public static function searchModerator($cuv, $hasDiacritics, $sourceId, $status, $userId, $beginTime, $endTime) {
    $regexp = StringUtil::dexRegexpToMysqlRegexp($cuv);
    $sourceClause = $sourceId ? "and Definition.sourceId = $sourceId" : '';
    $userClause = $userId ? "and Definition.userId = $userId" : '';

    if ($status == ST_DELETED) {
      // Deleted definitions are not associated with any lexem
      $collate = $hasDiacritics ? '' : 'collate utf8_general_ci';
      return Model::factory('Definition')
        ->raw_query("select * from Definition where lexicon $collate $regexp and status = " . ST_DELETED . " and createDate between $beginTime and $endTime " .
                    "$sourceClause $userClause order by lexicon, sourceId limit 500", null)->find_many();
    } else {
      return Model::factory('Definition')
        ->raw_query("select distinct Definition.* from Lexem join LexemDefinitionMap on Lexem.id = LexemDefinitionMap.lexemId " .
                    "join Definition on LexemDefinitionMap.definitionId = Definition.id where formNoAccent $regexp " .
                    "and Definition.status = $status and Definition.createDate >= $beginTime and Definition.createDate <= $endTime " .
                    "$sourceClause $userClause order by lexicon, sourceId limit 500", null)->find_many();
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
        $result[] = Definition::get_by_id($defId);
      } else {
        break;
      }
    }
    return $result;
  }

  public static function getWordCount() {
    $cachedWordCount = FileCache::getWordCount();
    if ($cachedWordCount) {
      return $cachedWordCount;
    }
    $result = self::countByStatus(ST_ACTIVE);
    FileCache::putWordCount($result);
    return $result;
  }

  public static function getWordCountLastMonth() {
    $cachedWordCountLastMonth = FileCache::getWordCountLastMonth();
    if ($cachedWordCountLastMonth) {
      return $cachedWordCountLastMonth;
    }
    $last_month = time() - 30 * 86400;
    $result = Model::factory('Definition')->where('status', ST_ACTIVE)->where_gte('createDate', $last_month)->count();
    FileCache::putWordCountLastMonth($result);
    return $result;
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
