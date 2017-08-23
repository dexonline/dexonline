<?php

class Definition extends BaseObject implements DatedObject {
  public static $_table = 'Definition';

  const ST_ACTIVE = 0;
  const ST_PENDING = 1;
  const ST_DELETED = 2;
  const ST_HIDDEN = 3;

  const ABBREV_NOT_REVIEWED = 0;
  const ABBREV_AMBIGUOUS = 1;
  const ABBREV_REVIEW_COMPLETE = 2;

  public static $STATUS_NAMES = [
    self::ST_ACTIVE  => 'activă',
    self::ST_PENDING => 'temporară',
    self::ST_DELETED => 'ștearsă',
    self::ST_HIDDEN  => 'ascunsă',
  ];

  private $source = null;
  private $entries = null;
  private $entryDefinitions = null;

  /* For admins, returns the definition with the given ID. For regular users,
     return null rather than a hidden definition. */
  static function getByIdNotHidden($id) {
    if (User::can(User::PRIV_ADMIN)) {
      return parent::get_by_id($id);
    } else {
      return Model::factory('Definition')->where('id',$id)->where_not_equal('status', self::ST_HIDDEN)->find_one();
    }
  }

  function getStatusName() {
    return self::$STATUS_NAMES[$this->status];
  }

  function getSource() {
    if ($this->source === null) {
      $this->source = Source::get_by_id($this->sourceId);
    }
    return $this->source;
  }

  function getEntryDefinitions() {
    if ($this->entryDefinitions === null) {
      $this->entryDefinitions = Model::factory('EntryDefinition')
                              ->where('definitionId', $this->id)
                              ->order_by_asc('id')
                              ->find_many();
    }
    return $this->entryDefinitions;
  }

  function getEntries() {
    if ($this->entries === null) {
      $this->entries = [];
      foreach ($this->getEntryDefinitions() as $ed) {
        $this->entries[] = Entry::get_by_id($ed->entryId);
      }
    }
    return $this->entries;
  }

  static function loadByEntryIds($entryIds) {
    if (!count($entryIds)) {
      return [];
    }

    return Model::factory('Definition')
      ->table_alias('d')
      ->select('d.*')
      ->join('EntryDefinition', ['d.id', '=', 'ed.definitionId'], 'ed')
      ->join('Source', ['s.id', '=', 'd.sourceId'], 's')
      ->where_in('ed.entryId', $entryIds)
      ->where_not_equal('status', self::ST_DELETED)
      ->order_by_desc('structurable')
      ->order_by_asc('displayOrder')
      ->find_many();
  }

  // Looks for a similar definition. Optionally sets $diffSize to the number of differences it finds.
  function loadSimilar($entryIds, &$diffSize = null) {
    $result = null;

    // First see if there is a similar source
    $similarSource = SimilarSource::getSimilarSource($this->sourceId);
    if ($similarSource && count($entryIds)) {
      // Load all definitions from $similarSource mapped to any of $entryIds
      $candidates = Model::factory('Definition')
                  ->table_alias('d')
                  ->select('d.*')
                  ->distinct()
                  ->join('EntryDefinition', ['ed.definitionId', '=', 'd.id'], 'ed')
                  ->where_not_equal('d.status', self::ST_DELETED)
                  ->where('d.sourceId', $similarSource->id)
                  ->where_in('ed.entryId', $entryIds)
                  ->find_many();

      // Find the definition with the minimum diff from the original
      $diffSize = 0;
      foreach ($candidates as $d) {
        $size = LDiff::diffMeasure($this->internalRep, $d->internalRep);
        if (!$result || ($size < $diffSize)) {
          $result = $d;
          $diffSize = $size;
        }
      }
    }

    return $result;
  }

  static function getListOfWordsFromSources($wordStart, $wordEnd, $sources) {
    return Model::factory('Definition')
      ->select('Definition.*')
      ->where_gte('lexicon', $wordStart)
      ->where_lte('lexicon', $wordEnd)
      ->where_in('sourceId', $sources)
      ->where('status', self::ST_ACTIVE)
      ->order_by_asc('lexicon')
      ->order_by_asc('sourceId')
      ->find_many();
  }

  static function countUnassociated() {
    // There are three disjoint types of definitions:
    // (1) deleted -- these are never associated with entries
    // (2) not deleted, associated
    // (3) not deleted, not associated
    // We compute (3) as (all definitions) - (1) - (2).
    $all = Model::factory('Definition')->count();
    $deleted = Model::factory('Definition')->where('status', self::ST_DELETED)->count();
    $associated = DB::getSingleValue('select count(distinct definitionId) from EntryDefinition');
    return $all - $deleted - $associated;
  }

  static function countAmbiguousAbbrevs() {
    return Model::factory('Definition')
      ->where_not_equal('status', self::ST_DELETED)
      ->where('abbrevReview', self::ABBREV_AMBIGUOUS)
      ->count();
  }

  static function loadForEntries(&$entries, $sourceId, $preferredWord) {
    if (!count($entries)) {
      return [];
    }
    $entryIds = Util::objectProperty($entries, 'id');

    // Get the IDs first, then load the definitions. This prevents MySQL
    // from creating temporary tables on disk.
    $query = Model::factory('Definition')
           ->table_alias('d')
           ->select('d.id')
           ->distinct()
           ->join('EntryDefinition', ['d.id', '=', 'ed.definitionId'], 'ed')
           ->join('Source', ['d.sourceId', '=', 's.id'], 's')
           ->where_in('ed.entryId', $entryIds)
           ->where_in('d.status', [self::ST_ACTIVE, self::ST_HIDDEN]);
    if ($sourceId) {
      $query = $query->where('s.id', $sourceId);
    }
    $ids = $query
      ->order_by_desc('s.type')
      ->order_by_expr("d.lexicon = '{$preferredWord}' desc")
      ->order_by_asc('s.displayOrder')
      ->order_by_asc('d.lexicon')
      ->find_array();

    $defs = array_map(function($rec) {
      return self::get_by_id($rec['id']);
    }, $ids);

    return $defs;
  }

  static function searchEntry($entry) {
    return Model::factory('Definition')
      ->table_alias('d')
      ->select('d.*')
      ->join('EntryDefinition', ['d.id', '=', 'ed.definitionId'], 'ed')
      ->join('Source', ['d.sourceId', '=', 's.id'], 's')
      ->where('ed.entryId', $entry->id)
      ->where_in('d.status', [self::ST_ACTIVE, self::ST_HIDDEN])
      ->order_by_desc('s.type')
      ->order_by_asc('s.displayOrder')
      ->order_by_asc('d.lexicon')
      ->find_many();
  }

  static function searchFullText($words, $hasDiacritics, $sourceId) {
    $field = $hasDiacritics ? 'formNoAccent' : 'formUtf8General';
    $intersection = null;
    $stopWords = [];
    $lexemMap = [];

    foreach ($words as $word) {
      // Get all lexems generating this form
      $lexems = Model::factory('InflectedForm')
        ->select('lexemId')
        ->distinct()
        ->where($field, $word)
        ->find_many();
      $lexemIds = Util::objectProperty($lexems, 'lexemId');
      $lexemMap[] = $lexemIds;

      // Get the FullTextIndex records for each form. Note that the FTI excludes stop words.
      $defIds = FullTextIndex::loadDefinitionIdsForLexems($lexemIds, $sourceId);

      // Determine whether the word is a stop word.
      if (empty($defIds)) {
        $isStopWord = Model::factory('InflectedForm')
          ->table_alias('i')
          ->join('Lexem', 'i.lexemId = l.id', 'l')
          ->where("i.{$field}", $word)
          ->where('l.stopWord', 1)
          ->count();
      } else {
        $isStopWord = false;
      }

      if ($isStopWord) {
        $stopWords[] = $word;
      } else {
        $intersection = ($intersection === null)
                      ? $defIds
                      : Util::intersectArrays($intersection, $defIds);
      }
    }

    if (empty($intersection)) { // This can happen when the query is all stopwords or the source selection produces no results
      return [[], $stopWords];
    }
    if (count($words) == 1) {
      // For single-word queries, let the caller do the sorting.
      return [$intersection, $stopWords];
    }

    // Now compute a score for every definition
    DebugInfo::resetClock();
    $positionMap = FullTextIndex::loadPositionsByLexemIdsDefinitionIds($lexemMap, $intersection);
    $shortestIntervals = [];
    foreach ($intersection as $defId) {
      $shortestIntervals[] = Util::findSnippet($positionMap[$defId]);
    }

    if ($intersection) {
      array_multisort($shortestIntervals, $intersection);
    }
    DebugInfo::stopClock("Computed score for every definition");

    return [$intersection, $stopWords];
  }

  static function highlight($words, &$definitions) {
    $res = array_fill_keys($words, []);

    foreach ($res as $key => &$words) {
      $forms = Model::factory('InflectedForm')
             ->table_alias('i1')
             ->select('i2.formNoAccent')
             ->distinct()
             ->join('Lexem', ['i1.lexemId', '=', 'l.id'], 'l')
             ->left_outer_join('InflectedForm', ['i2.lexemId', '=', 'l.id'], 'i2')
             ->where('l.stopWord', 0)
             ->where('i1.formUtf8General', $key)
             ->find_many();
      foreach ($forms as $f) {
        $words[] = $f->formNoAccent;
      }

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
        $classIndex = ($classIndex + 1) % 5; // keep the number of colors in sync with search.css
      }
    }
  }

  static function searchModerator($cuv, $hasDiacritics, $sourceId, $status, $userId,
                                         $beginTime, $endTime, $page, $resultsPerPage) {
    $regexp = StringUtil::dexRegexpToMysqlRegexp($cuv);
    $sourceClause = $sourceId ? "and Definition.sourceId = $sourceId" : '';
    $userClause = $userId ? "and Definition.userId = $userId" : '';
    $offset = ($page - 1) * $resultsPerPage;

    if ($status == self::ST_DELETED) {
      // Deleted definitions are not associated with any lexem
      $collate = $hasDiacritics ? '' : 'collate utf8_general_ci';
      return Model::factory('Definition')
        ->raw_query("select * from Definition where lexicon $collate $regexp and status = " . self::ST_DELETED . " and createDate between $beginTime and $endTime " .
                    "$sourceClause $userClause order by lexicon, sourceId limit $offset, $resultsPerPage")->find_many();
    } else {
      $q = Model::factory('Definition')
         ->table_alias('d')
         ->select('d.*')
         ->distinct()
         ->join('EntryDefinition', ['ed.definitionId', '=', 'd.id'], 'ed')
         ->join('EntryLexem', ['el.entryId', '=', 'ed.entryId'], 'el')
         ->join('Lexem', ['el.lexemId', '=', 'l.id'], 'l')
         ->where_raw("l.formNoAccent  $regexp")
         ->where('d.status', $status)
         ->where_gte('d.createDate', $beginTime)
         ->where_lte('d.createDate', $endTime);

      if ($sourceId) {
        $q = $q->where('d.sourceId', $sourceId);
      }
      if ($userId) {
        $q = $q->where('d.userId', $userId);
      }

      return $q
        ->order_by_asc('lexicon')
        ->order_by_asc('sourceId')
        ->limit($resultsPerPage)
        ->offset($offset)
        ->find_many();
    }
  }

  // Return definitions that are associated with at least two of the lexems
  static function searchMultipleWords($words, $hasDiacritics, $oldOrthography, $sourceId) {
    $defCounts = [];
    foreach ($words as $word) {
      $entries = Entry::searchInflectedForms($word, $hasDiacritics, $oldOrthography);
      if (count($entries)) {
        $definitions = self::loadForEntries($entries, $sourceId, $word);
        foreach ($definitions as $def) {
          $defCounts[$def->id] = array_key_exists($def->id, $defCounts) ? $defCounts[$def->id] + 1 : 1;
        }
      }
    }
    arsort($defCounts);

    $result = [];
    foreach ($defCounts as $defId => $cnt) {
      if ($cnt >= 2) {
        $d = Definition::get_by_id($defId);
        if ($d) { // Hidden definitions might return null
          $result[] = $d;
        }
      }
    }
    return $result;
  }

  static function getWordCount() {
    $cachedWordCount = FileCache::getWordCount();
    if ($cachedWordCount) {
      return $cachedWordCount;
    }
    $result = Model::factory('Definition')->where('status', self::ST_ACTIVE)->count();
    FileCache::putWordCount($result);
    return $result;
  }

  static function getWordCountLastMonth() {
    $cachedWordCountLastMonth = FileCache::getWordCountLastMonth();
    if ($cachedWordCountLastMonth) {
      return $cachedWordCountLastMonth;
    }
    $last_month = time() - 30 * 86400;
    $result = Model::factory('Definition')->where('status', self::ST_ACTIVE)->where_gte('createDate', $last_month)->count();
    FileCache::putWordCountLastMonth($result);
    return $result;
  }

  function save() {
    $this->modUserId = User::getActiveId();
    return parent::save();
  }
}

?>
