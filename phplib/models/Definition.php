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
  private $footnotes = null;

  /* For admins, returns the definition with the given ID. For regular users,
    return null rather than a hidden definition. */
  static function getByIdNotHidden($id) {
    if (User::can(User::PRIV_ADMIN)) {
      return parent::get_by_id($id);
    } else {
      return Model::factory('Definition')->where('id', $id)->where_not_equal('status', self::ST_HIDDEN)->find_one();
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

  function getFootnotes() {
    if ($this->footnotes === null) {
      $this->footnotes = Model::factory('Footnote')
        ->where('definitionId', $this->id)
        ->order_by_asc('rank')
        ->find_many();
    }
    return $this->footnotes;
  }

  // Single entry point for sanitize() / htmlize() / etc.
  // $flash (boolean): if true, set flash messages for errors and warnings
  // Returns an array of footnotes whose ID field is not set.
  function process($flash = true) {
    $errors = [];
    $warnings = [];

    // sanitize
    list($this->internalRep, $ambiguousAbbreviations) 
      = Str::sanitize($this->internalRep, $this->sourceId, $warnings);

    // htmlize + footnotes
    list($this->htmlRep, $footnotes) 
      = Str::htmlize($this->internalRep, $this->sourceId, false, $errors, $warnings);

    // abbrevReview status
    $this->abbrevReview = count($ambiguousAbbreviations) 
                        ? Definition::ABBREV_AMBIGUOUS 
                        : Definition::ABBREV_REVIEW_COMPLETE;

    // lexicon
    $this->extractLexicon();

    if ($flash) {
      foreach ($warnings as $warning) {
        FlashMessage::add($warning, 'warning');
      }

      foreach ($errors as $error) {
        FlashMessage::add($error);
      }
    }

    return $footnotes;
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
      $diffSize = 1000000000;
      foreach ($candidates as $d) {
        $size = DiffUtil::diffMeasure($d->internalRep, $this->internalRep);
        if ($size < $diffSize) {
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
    $lexemeMap = [];
    $adult = false;

    foreach ($words as $word) {
      // Get all lexemes generating this form
      $lexemes = Model::factory('InflectedForm')
        ->select('lexemeId')
        ->distinct()
        ->where($field, $word)
        ->find_many();
      $lexemeIds = Util::objectProperty($lexemes, 'lexemeId');
      $lexemeMap[] = $lexemeIds;

      // Get the FullTextIndex records for each form. Note that the FTI excludes stop words.
      $defIds = FullTextIndex::loadDefinitionIdsForLexemes($lexemeIds, $sourceId);

      // Determine whether the word is a stop word.
      if (empty($defIds)) {
        $isStopWord = Model::factory('InflectedForm')
          ->table_alias('i')
          ->join('Lexeme', 'i.lexemeId = l.id', 'l')
          ->where("i.{$field}", $word)
          ->where('l.stopWord', 1)
          ->count();
      } else {
        $isStopWord = false;
      }

      // see if any entries for these lexemes are adult
      if (!empty($lexemeIds)) {
        $adult |= Model::factory('Entry')
          ->table_alias('e')
          ->join('EntryLexeme', ['e.id', '=', 'el.entryId'], 'el')
          ->where_in('el.lexemeId', $lexemeIds)
          ->where('e.adult', true)
          ->count();
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
      return [[], $stopWords, $adult];
    }
    if (count($words) == 1) {
      // For single-word queries, let the caller do the sorting.
      return [$intersection, $stopWords, $adult];
    }

    // Now compute a score for every definition
    DebugInfo::resetClock();
    $positionMap = FullTextIndex::loadPositionsByLexemeIdsDefinitionIds($lexemeMap, $intersection);
    $shortestIntervals = [];
    foreach ($intersection as $defId) {
      $shortestIntervals[] = Util::findSnippet($positionMap[$defId]);
    }

    if ($intersection) {
      array_multisort($shortestIntervals, $intersection);
    }
    DebugInfo::stopClock("Computed score for every definition");

    return [$intersection, $stopWords, $adult];
  }

  static function highlight($words, &$definitions) {
    $res = array_fill_keys($words, []);

    foreach ($res as $key => &$words) {
      $forms = Model::factory('InflectedForm')
        ->table_alias('i1')
        ->select('i2.form')
        ->select('i2.formNoAccent')
        ->distinct()
        ->join('Lexeme', ['i1.lexemeId', '=', 'l.id'], 'l')
        ->left_outer_join('InflectedForm', ['i2.lexemeId', '=', 'l.id'], 'i2')
        ->where('l.stopWord', 0)
        ->where('i1.formUtf8General', $key)
        ->find_many();
      foreach ($forms as $f) {
        $words['accented'][] = str_replace(Constant::ACCENTS['marked'], Constant::ACCENTS['accented'], $f->form);
        $words['unaccented'][] = $f->formNoAccent;
        $words['marked'][] = $f->form;
      }

      if (empty($words)) {
        unset($res[$key]);
      }
    }

    foreach ($definitions as $def) {
      $classIndex = 0;
      foreach ($res as &$words) {
        $wordsHighlight = array();

        // we need each variant of searched words as our definition are mixed accented
        $wordsHighlight[] = implode("|", $words['accented']);
        $wordsHighlight[] = implode("|", $words['unaccented']);
        $marked = str_replace("/", "\\/", Str::highlightAccent($words['marked']));
        $wordsHighlight[] = implode("|", $marked);

        // finally get an 'or' string pattern of all variants
        $wordsString = implode("|", $wordsHighlight);
        $pattern = '/[^\p{L}](' . $wordsString . ')[^\p{L}]/iuS';

        preg_match_all($pattern, $def->htmlRep, $match, PREG_OFFSET_CAPTURE);
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

  // Return definitions that are associated with at least two of the lexemes
  static function searchMultipleWords($words, $hasDiacritics, $sourceId) {
    $defCounts = [];
    foreach ($words as $word) {
      $entries = Entry::searchInflectedForms($word, $hasDiacritics);
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

  /**
   * Extracts and returns the term that the definition is *probably* defining.
   * That is, more or less, the first word in the definition, but we have lots of
   * special cases to deal with the formatting.
   */
  function extractLexicon() {
    if (!preg_match('/^[^@]*@([^@,]+)/', $this->internalRep, $matches)) {
      $this->lexicon = '';
      return '';
    }

    $s = $matches[1];
    $s = Str::removeAccents($s);

    $s = preg_replace('# (-|\X)+/$#', '', $s); // strip pronunciations (MDN)
    $s = explode('/', $s)[0]; // ignore everything after other slashes
    $s = preg_split('/\. *[\r\n]/', $s)[0]; // DAS is formatted on multiple lines

    $s = preg_replace('/^[-!*]+/', '', $s);
    $s = str_replace("\\'", "'", $s);
    $s = str_replace(['$', '\\|', '|'], '', $s); // Onomastic uses |
    $s = preg_replace('/[_^]{?[0-9]+}?/', '', $s); // strip homonym numbering and subscripts
    
    $s = preg_replace('/^[-* 0-9).]+/', '', $s); // strip homonyms and asterisks (Scriban)

    if (in_array($this->sourceId, [7, 9, 38, 62])) {
      // Strip 'a ', 'a se ' etc. from verbs
      $s = preg_replace('/^(a se |a \(se\) |a-și |a )/i', '', $s);
    }

    if ($this->sourceId == 9) {
      // parts of expressions are followed by a ': '
      $s = explode(':', $s)[0];

      // throw away inflected forms
      preg_match('/^([-A-ZĂÂÎȘȚÜ^0-9 ]+)( [a-zăâîșț()\\\\~1. ]+)?$/', $s, $matches);
      if ($matches) {
        $s = $matches[1];
      }
    }

    if ($this->sourceId == 71) {
      $s = preg_split('/\. /', $s)[0]; // D. Epitete includes ". Epitete" in the title
    }

    $s = trim($s);
    $s = mb_strtolower($s);

    // remove parentheses preceded by a space
    $s = preg_split('/ [\[\(]/', $s)[0];

    // strip some more characters
    $s = preg_replace('/[-:]+$/', '', $s);
    $s = preg_replace('/ [1i]\.$/', '', $s);
    $s = str_replace(['(', ')', '®', '!', '#', '\\'], '', $s);

    // if there is only one final dot, strip it
    $s = preg_replace("/^([^.]+)\.$/", '$1', $s);

    $this->lexicon = $s;
    return $s;
  }

  function save() {
    $this->modUserId = User::getActiveId();
    return parent::save();
  }
}
