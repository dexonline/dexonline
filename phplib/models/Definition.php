<?php

class Definition extends BaseObject implements DatedObject {
  public static $_table = 'Definition';

  public static function get_by_id($id) {
    if (util_isModerator(PRIV_ADMIN)) {
      return parent::get_by_id($id);
    } else {
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
      ->raw_query('select count(*) as c from (select count(*) from LexemDefinitionMap group by definitionId) as someLabel')
      ->find_one();
    return $r->c;
  }

  // Looks for a similar definition. Optionally sets $diffSize to the number of differences it finds.
  function loadSimilar($lexemIds, &$diffSize = null) {
    $result = null;

    // First see if there is a similar source
    $similarSource = SimilarSource::getSimilarSource($this->sourceId);
    if ($similarSource && count($lexemIds)) {
      // Load all definitions from $similarSource mapped to any of $lexemIds
      $candidates = Model::factory('Definition')
        ->table_alias('d')
        ->select('d.*')
        ->distinct()
        ->join('LexemDefinitionMap', 'ldm.definitionId = d.id', 'ldm')
        ->where_not_equal('d.status', ST_DELETED)
        ->where('d.sourceId', $similarSource->id)
        ->where_in('ldm.lexemId', $lexemIds)
        ->find_many();

      // Find the definition with the minimum diff from the original
      $minDiff = 0;
      foreach ($candidates as $d) {
        $diff = SimpleDiff::textDiff($this->internalRep, $d->internalRep);
        $diffSize = 0;
        foreach ($diff as $item) {
          if (is_array($item)) {
            $diffSize += count($item['d']) + count($item['i']);
          }
        }
        if (!$result || ($diffSize < $minDiff)) {
          $result = $d;
          $minDiff = $diffSize;
        }
      }
    }

    return $result;
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

  public static function countUnassociated() {
    // There are three disjoint types of definitions:
    // (1) deleted -- these are never associated with lexems
    // (2) not deleted, associated
    // (3) not deleted, not associated
    // We compute (3) as (all definitions) - (1) - (2).
    $all = Model::factory('Definition')->count();
    $deleted = Model::factory('Definition')->where('status', ST_DELETED)->count();
    $associated = db_getSingleValue('select count(distinct definitionId) from LexemDefinitionMap');
    return $all - $deleted - $associated;
  }

  public static function countAmbiguousAbbrevs() {
    return Model::factory('Definition')
      ->where_not_equal('status', ST_DELETED)
      ->where('abbrevReview', ABBREV_AMBIGUOUS)
      ->count();
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
                  "order by S.isOfficial desc, (D.lexicon = '$preferredWord') desc, S.displayOrder, D.lexicon")
      ->find_many();
  }

  public static function searchLexemId($lexemId, $exclude_unofficial = false) {
    $excludeClause = $exclude_unofficial ? "and S.isOfficial <> 0 " : '';
    $statusClause = util_isModerator(PRIV_VIEW_HIDDEN) ? sprintf("and D.status in (%d,%d)", ST_ACTIVE, ST_HIDDEN) : sprintf("and D.status = %d", ST_ACTIVE);
    return Model::factory('Definition')
      ->raw_query("select D.* from Definition D, LexemDefinitionMap L, Source S where D.id = L.definitionId " .
                  "and D.sourceId = S.id and L.lexemId = '$lexemId' $excludeClause $statusClause " .
                  "order by S.isOfficial desc, S.displayOrder, D.lexicon")
      ->find_many();
  }

  public static function searchFullText($words, $hasDiacritics, $sourceId) {
    $field = $hasDiacritics ? 'formNoAccent' : 'formUtf8General';
    $intersection = null;
    $stopWords = array();
    $lmMap = array();

    foreach ($words as $word) {
      // Get all LexemModels generating this form
      $lms = Model::factory('LexemModel')
        ->table_alias('L')
        ->select('L.id')
        ->distinct()
        ->join('InflectedForm', 'I.lexemModelId = L.id', 'I')
        ->where("I.{$field}", $word)
        ->find_many();
      $lmIds = util_objectProperty($lms, 'id');
      $lmMap[] = $lmIds;

      // Get the FullTextIndex records for each LexemModels. Note that the FTI excludes stop words.
      $defIds = FullTextIndex::loadDefinitionIdsForLexemModels($lmIds, $sourceId);

      // Determine whether the word is a stop word.
      if (empty($defIds)) {
        $isStopWord = Model::factory('InflectedForm')
          ->table_alias('I')
          ->join('LexemModel', 'I.lexemModelId = LM.id', 'LM')
          ->join('Lexem', 'LM.lexemId = L.id', 'L')
          ->where("I.{$field}", $word)
          ->where('L.stopWord', 1)
          ->count();
      } else {
        $isStopWord = false;
      }

      if ($isStopWord) {
        $stopWords[] = $word;
      } else {
        $intersection = ($intersection === null)
          ? $defIds
          : util_intersectArrays($intersection, $defIds);
      }
    }

    if (empty($intersection)) { // This can happen when the query is all stopwords or the source selection produces no results
      return array(array(), $stopWords);
    }
    if (count($words) == 1) {
      // For single-word queries, skip the ordering part.
      // We could sort the definitions by lexicon, but it is very expensive.
      return array($intersection, $stopWords);
    }

    // Now compute a score for every definition
    DebugInfo::resetClock();
    $positionMap = FullTextIndex::loadPositionsByLexemIdsDefinitionIds($lmMap, $intersection);
    $shortestIntervals = array();
    foreach ($intersection as $defId) {
      $shortestIntervals[] = util_findSnippet($positionMap[$defId]);
    }

    if ($intersection) {
      array_multisort($shortestIntervals, $intersection);
    }
    DebugInfo::stopClock("Computed score for every definition");

    return array($intersection, $stopWords);
  }

  public static function highlight($words, &$definitions) {
    $res = array_fill_keys($words, array());

    foreach ($res as $key => &$words) {
      $var = sprintf("select distinct i2.formNoAccent  
        from InflectedForm i1, LexemModel lm1, Lexem l, LexemModel lm2, InflectedForm i2
        where i1.lexemModelId = lm1.id and
        lm1.lexemId = l.id and
        l.id = lm2.lexemId and
        lm2.id = i2.lexemModelId and
        not l.stopWord and
        i1.formUtf8General = '%s'", $key);

      $query = db_getArray($var);

      foreach ($query as $q) {
        array_push($words, $q);
      }

      $words = array_unique($words);

      if (empty($words)) {
        unset($res[$key]);
      }
    }

    foreach ($definitions as $def) {
      $classIndex = 0;
      foreach ($res as &$words) {
        $wordsString = implode("|", $words);

        preg_match_all('/[^a-zăâîșț<\/]('. $wordsString .')[^a-zăâîșț>]/iS', $def->htmlRep, $match, PREG_OFFSET_CAPTURE);
        $revMatch = array_reverse($match[1]);

        foreach ($revMatch as $m) {
          $def->htmlRep = substr_replace($def->htmlRep,
                                         "<span class=\"fth fth{$classIndex}\">{$m[0]}</span>",
                                         $m[1], strlen($m[0]));
        }
        $classIndex = ($classIndex + 1) % 5; // keep the number of colors in sync with common.css
      }
    }
  }

  public static function searchModerator($cuv, $hasDiacritics, $sourceId, $status, $userId, $beginTime, $endTime, $page, $resultsPerPage) {
    $regexp = StringUtil::dexRegexpToMysqlRegexp($cuv);
    $sourceClause = $sourceId ? "and Definition.sourceId = $sourceId" : '';
    $userClause = $userId ? "and Definition.userId = $userId" : '';
    $offset = ($page - 1) * $resultsPerPage;

    if ($status == ST_DELETED) {
      // Deleted definitions are not associated with any lexem
      $collate = $hasDiacritics ? '' : 'collate utf8_general_ci';
      return Model::factory('Definition')
        ->raw_query("select * from Definition where lexicon $collate $regexp and status = " . ST_DELETED . " and createDate between $beginTime and $endTime " .
                    "$sourceClause $userClause order by lexicon, sourceId limit $offset, $resultsPerPage")->find_many();
    } else {
      $query = "select distinct Definition.* from Lexem join LexemDefinitionMap on Lexem.id = LexemDefinitionMap.lexemId " .
        "join Definition on LexemDefinitionMap.definitionId = Definition.id where formNoAccent $regexp " .
        "and Definition.status = $status and Definition.createDate >= $beginTime and Definition.createDate <= $endTime " .
        "$sourceClause $userClause order by lexicon, sourceId limit $offset, $resultsPerPage";
      return Model::factory('Definition')->raw_query($query)->find_many();
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
    $result = Model::factory('Definition')->where('status', ST_ACTIVE)->count();
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
