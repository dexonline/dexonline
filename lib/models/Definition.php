<?php

class Definition extends BaseObject implements DatedObject {

  public static $_table = 'Definition';

  const ST_ACTIVE = 0;
  const ST_PENDING = 1;
  const ST_DELETED = 2;
  const ST_HIDDEN = 3;

  const STATUS_NAMES = [
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
      return Model::factory('Definition')
        ->where('id', $id)
        ->where_not_equal('status', self::ST_HIDDEN)
        ->find_one();
    }
  }

  function getStatusName() {
    return self::STATUS_NAMES[$this->status];
  }

  function getSource() {
    if ($this->source === null) {
      $this->source = Source::get_by_id($this->sourceId);
    }
    return $this->source;
  }

  function setFootnotes($footnotes) {
    $this->footnotes = $footnotes;
  }

  function getFootnotes() {
    return $this->footnotes;
  }

  // Single entry point for sanitizing / extracting lexicon / counting ambigious abbreviations.
  function process($flash = false) {
    $warnings = [];
    $errors = [];

    // sanitize
    list($this->internalRep, $ambiguousAbbreviations)
      = Str::sanitize($this->internalRep, $this->sourceId, $warnings);

    // ambiguous abbreviations
    $this->hasAmbiguousAbbreviations = !empty($ambiguousAbbreviations);

    // lexicon
    $this->extractLexicon();

    // pass the definition through the parser
    $this->parse($warnings, $errors);

    if ($flash) {
      FlashMessage::bulkAdd($warnings, 'warning');
      FlashMessage::bulkAdd($errors);
    }
  }

  function parse(&$warnings = [], &$errors = []) {
    // certain tags explicitly say "don't try to parse this definition"
    $ignoredTagIds = Config::PARSER_TAGS_TO_IGNORE;
    $hasIgnoredTags = Model::factory('ObjectTag')
      ->where('objectId', $this->id)
      ->where('objectType', ObjectTag::TYPE_DEFINITION)
      ->where_in('tagId', $ignoredTagIds)
      ->find_one();

    if (!$hasIgnoredTags) {
      // see if we have a parser for this definition's source
      $parser = ParserFactory::getParser($this);
      if ($parser) {
        try {
          $this->internalRep = $parser->parse($this, $warnings);
        } catch (Exception $e) {
          $pos = $e->getMessage();

          if (!User::can(User::PRIV_ADMIN) &&
              in_array($this->status,
                       [ Definition::ST_ACTIVE, Definition::ST_HIDDEN ])) {
            $errors[] = [ 'parsingError.tpl', [] ];
          } else {
            $warnings[] = [ 'parsingWarning.tpl', [] ];
          }
          $this->internalRep = Str::insert(
            $this->internalRep,
            Constant::PARSING_ERROR_MARKER,
            $pos);
        }
      }
    }
  }

  // sets the volume and page fields under certain conditions
  function setVolumeAndPage() {
    if ($this->volume && $this->page) {
      return; // do not change existing values
    }

    $pi = PageIndex::lookup($this->lexicon, $this->sourceId);
    if ($pi) {
      $this->volume = $pi->volume;
      $this->page = $pi->page;
    }
  }

  function updateRareGlyphs() {
    $common = $this->getSource()->commonGlyphs ?? '';

    //  do nothing if the source has no common glyphs defined
    if ($common) {
      $common .= Source::BASE_GLYPHS;
      $commonMap = array_fill_keys(Str::unicodeExplode($common), true);

      $rareMap = [];
      // exclude footnotes and hidden comments
      $rep = preg_replace("/(\{\{.*\}\})|(▶.*◀)/U", '', $this->internalRep);
      foreach (Str::unicodeExplode($rep) as $glyph) {
        if (!isset($commonMap[$glyph])) {
          $rareMap[$glyph] = true;
        }
      }

      $this->rareGlyphs = implode(array_keys($rareMap));
    } else {
      $this->rareGlyphs = '';
    }
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
      ->where('hasAmbiguousAbbreviations', true)
      ->count();
  }

  static function loadMissingRareGlyphsTags($sourceId = null) {
    $join = sprintf('(d.id = ot.objectId) and (ot.objectType = %d) and (ot.tagId = %d)',
                    ObjectTag::TYPE_DEFINITION,
                    Config::TAG_ID_RARE_GLYPHS);
    $query = Model::factory('Definition')
      ->table_alias('d')
      ->select('d.*')
      ->left_outer_join('ObjectTag', $join, 'ot')
      ->where_not_equal('d.rareGlyphs', '')
      ->where_null('ot.id');

    if ($sourceId) {
      $query = $query->where('d.sourceId', $sourceId);
    }

    return $query->find_many();
  }

  static function loadTypos($sourceId = null) {

    $query = Model::factory('Definition')
      ->table_alias('d')
      ->select('d.*')
      ->join('Typo', [ 't.definitionId', '=', 'd.id'], 't')
      ->order_by_asc('d.lexicon');

    if ($sourceId) {
      $query = $query->where('d.sourceId', $sourceId);
    }

    return $query->find_many();
  }

  static function loadUnneededRareGlyphsTags() {
    return Model::factory('Definition')
      ->table_alias('d')
      ->select('d.*')
      ->join('ObjectTag', [ 'd.id', '=', 'ot.objectId'], 'ot')
      ->where('ot.objectType', ObjectTag::TYPE_DEFINITION)
      ->where('ot.tagId', Config::TAG_ID_RARE_GLYPHS)
      ->where('d.rareGlyphs', '')
      ->find_many();
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
      ->order_by_asc('s.sourceTypeId')
      ->order_by_expr(sprintf("d.lexicon = '%s' desc", addslashes($preferredWord)))
      ->order_by_asc('s.displayOrder')
      ->order_by_asc('d.lexicon')
      ->find_array();

    $defs = array_map(function($rec) {
      return self::get_by_id($rec['id']);
    }, $ids);

    return $defs;
  }

  static function searchEntry($entry) {
    $shortDesc = addslashes($entry->getShortDescription());
    return Model::factory('Definition')
      ->table_alias('d')
      ->select('d.*')
      ->join('EntryDefinition', ['d.id', '=', 'ed.definitionId'], 'ed')
      ->join('Source', ['d.sourceId', '=', 's.id'], 's')
      ->where('ed.entryId', $entry->id)
      ->where_in('d.status', [self::ST_ACTIVE, self::ST_HIDDEN])
      ->order_by_asc('s.sourceTypeId')
      ->order_by_expr("d.lexicon = '{$shortDesc}' desc")
      ->order_by_asc('s.displayOrder')
      ->order_by_asc('d.lexicon')
      ->find_many();
  }

  // Modifies $words to remove stop words. Returns a tuple of:
  // * an array matching definition IDs
  // * an array of stop words
  // * a boolean indicating whether any words are adult
  static function searchFullText(&$words, $hasDiacritics, $sourceId) {
    $field = $hasDiacritics ? 'formNoAccent' : 'formUtf8General';
    $intersection = null;
    $stopWords = [];
    $lexemeMap = [];
    $adult = false;

    foreach ($words as $key => $word) {
      $isStopWord = InflectedForm::isStopWord($field, $word);
      if ($isStopWord) {
        $stopWords[] = $word;
        unset($words[$key]);
      } else {

        // Get all lexemes generating this form
        $lexemes = Model::factory('InflectedForm')
                 ->select('lexemeId')
                 ->distinct()
                 ->where($field, $word)
                 ->find_many();
        $lexemeIds = Util::objectProperty($lexemes, 'lexemeId');
        $lexemeMap[] = $lexemeIds;

        // Get the definition IDs for all lexemes
        $defIds = FullTextIndex::loadDefinitionIdsForLexemes($lexemeIds, $sourceId);

        // see if any entries for these lexemes are adult
        if (!empty($lexemeIds)) {
          $adult |= Model::factory('Entry')
                  ->table_alias('e')
                  ->join('EntryLexeme', ['e.id', '=', 'el.entryId'], 'el')
                  ->where_in('el.lexemeId', $lexemeIds)
                  ->where('e.adult', true)
                  ->count();
        }

        $intersection = ($intersection === null)
                      ? $defIds
                      : Util::intersectArrays($intersection, $defIds);
      }
    }

    if (empty($intersection)) {
      // This can happen when the query is all stopwords or the source selection produces no results
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
        // Queries hang if $key is passed as an int, e.g. "where i1.formUtf8General = 0".
        // This can happen if the query includes a term like 0.
        ->where('i1.formUtf8General', (string)$key)
        ->find_many();
      foreach ($forms as $f) {
        // catch accents in both forms ('a and á) as both can be present in
        // internalRep, for historical reasons
        $accented = str_replace(Constant::ACCENTS['marked'],
                                Constant::ACCENTS['accented'],
                                $f->form);
        array_push($words, $f->formNoAccent, $f->form, $accented);
      }

      if (empty($words)) {
        unset($res[$key]);
      }
    }

    foreach ($definitions as $def) {
      $classIndex = 0;
      foreach ($res as &$words) {
        $wordsString = implode('|', $words);
        $pattern = '/[^\p{L}](' . $wordsString . ')[^\p{L}]/iuS';

        preg_match_all($pattern, $def->internalRep, $match, PREG_OFFSET_CAPTURE);
        $revMatch = array_reverse($match[1]);

        foreach ($revMatch as $m) {
          $replacement = sprintf('{c%s|%sc}', $m[0], $classIndex);

          $def->internalRep = substr_replace(
            $def->internalRep,
            $replacement,
            $m[1],
            strlen($m[0])
          );
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
    $defs = array_diff( $defCounts, [1] );

    $result = [];
    if (!empty($defs)) {
      foreach ($defCounts as $defId => $cnt) {
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
    // First strip non-abbreviation marker and parser error marker
    // sometimes forced into lexicon fields
    $s = str_replace(['##', Constant::PARSING_ERROR_MARKER], '', $this->internalRep);

    $s = preg_replace('/▶(.*?)◀/sU', '', $s); // strip hidden comments
    $s = preg_replace('/\{{2}(.+)\}{2}/U', '', $s); // strip possible comments

    // Look for possible lexicon (once the minor cleaning is done)
    if (!preg_match('/^(?:\P{L})+(\X+?)[@,]/u', $s, $matches)) {
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

    // strip homonym superscripts/subscripts, practically everything in super/sub
    $s = preg_replace('/[_^]{?[0-9®]+}?/', '', $s);

    $s = preg_replace('/^[-* 0-9).]+/', '', $s); // strip homonyms and asterisks (Scriban)

    if (in_array($this->sourceId, [7, 9, 38, 62])) {
      // Strip 'a ', 'a se ' etc. from verbs
      $s = preg_replace('/^(a \(?se\)? |a-și |a )/i', '', $s);
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
    $s = str_replace(['(', ')', '®', '!', '#', '\\', '"'], '', $s);

    // if there is only one final dot, strip it
    $s = preg_replace("/^([^.]+)\.$/", '$1', $s);

    $this->lexicon = $s;
    return $s;
  }

  /**
   * Counts all marked abbreviations with #...# in specified source
   *
   * @param   string  $abbrev         short representation of abbreviation
   * @param   int     $sourceId       source to search
   * @param   int     $caseSensitive  binary compare option
   * @return  int
   */
  static function countAbbrevs($abbrev, $sourceId, $caseSensitive = false) {
    $cs = $caseSensitive ? ' ' : ' binary ';
    return Model::factory('Definition')
      ->where_raw('internalRep like' . $cs . '?', '%#' . $abbrev . '#%')
      ->where_in('status', [Definition::ST_ACTIVE, Definition::ST_HIDDEN])
      ->where('sourceID', $sourceId)
      ->count();
  }

  /**
   * @return  bool  <p>$success from parent</p>
   */
  function save() {
    $this->modUserId = User::getActiveId();
    Typo::delete_all_by_definitionId($this->id);
    return parent::save();
  }

}
