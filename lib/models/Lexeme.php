<?php

class Lexeme extends BaseObject implements DatedObject {
  public static $_table = 'Lexeme';

  private $sourceNames = null;         // comma-separated list of source names
  private $modelTypeObject = null;
  private $partOfSpeech = null;
  private $inflectedForms = null;
  private $inflectedFormMap = null;    // mapped by various criteria depending on the caller
  private $fragments = null;           // for compound lexemes
  private $compoundParts = null;
  private $animate = null;
  public $entries = null;

  const ASSOC_ENTRY = [
    1 => 'principal',
    0 => 'variantă',
  ];

  const METHOD_GENERATE = 1;
  const METHOD_LOAD = 2;

  const CAN_DELETE_OK = null;
  const CAN_DELETE_FRAGMENT = 'lexemul nu poate fi șters deoarece este fragment al unui lexem compus';

  function setForm($form) {
    $this->form = $form;
    // keep literal apostrophes, e.g. o\'clock
    $this->formNoAccent = preg_replace("/(?<!\\\\)'/", '', $form);
    $this->formNoAccent = str_replace("\\'", "'", $this->formNoAccent);
    $this->formUtf8General = $this->formNoAccent;
    $this->reverse = Str::reverse($this->formNoAccent);
  }

  static function create($form, $modelType = '', $modelNumber = '', $restriction = '') {
    $l = Model::factory('Lexeme')->create();

    $form = trim($form);
    if (preg_match('/^(.*) \((.*)\)$/', $form, $matches)) {
      $l->setForm($matches[1]);
      $l->description = $matches[2];
    } else {
      $l->setForm($form);
      $l->description = '';
    }

    $l->noAccent = false;
    $l->modelType = $modelType;
    $l->modelNumber = $modelNumber;
    $l->restriction = $restriction;
    $l->notes = '';

    return $l;
  }

  function getModelType() {
    // Preload loads model types in bulk, by lexeme ID. This breaks for some
    // model exponents that don't have an underlying lexeme (see
    // FlexModel::getExponentWithParadigm()).
    if ($this->modelTypeObject === null) {
     $this->modelTypeObject = Preload::getLexemeModelType($this->id)
       ?: ModelType::get_by_code($this->modelType);
    }
    return $this->modelTypeObject;
  }

  function setModelType(string $modelType) {
    $mt = ModelType::get_by_code($modelType);
    $this->modelType = $modelType;
    $this->modelTypeObject = $mt;
    // This duplicates code from Preload.php, where it is executed in bulk.
    if ($modelType == 'I' && !$this->compound) {
      $m = FlexModel::loadCanonicalByTypeNumber($modelType, $this->modelNumber);
      $this->setPartOfSpeech($m->description);
    } else {
      $this->setPartOfSpeech($mt->description);
    }
  }

  function getPartOfSpeech() {
    if ($this->partOfSpeech === null) {
      $this->partOfSpeech = Preload::getLexemePartOfSpeech($this->id);
    }
    return $this->partOfSpeech;
  }

  function setPartOfSpeech(string $partOfSpeech) {
    $this->partOfSpeech = $partOfSpeech;
  }

  function hasModel($type, $number) {
    return ($this->modelType == $type) && ($this->modelNumber == $number);
  }

  function hasParadigm() {
    return $this->modelType != 'T';
  }

  function getFragments() {
    if ($this->fragments === null) {
      $this->fragments = Model::factory('Fragment')
        ->where('lexemeId', $this->id)
        ->order_by_asc('rank')
        ->find_many();
    }
    return $this->fragments;
  }

  function setFragments($fragments) {
    $this->fragments = $fragments;
  }

  function getCompoundParts() {
    if ($this->compoundParts === null) {
      $this->compoundParts = [];
      foreach ($this->getFragments() as $f) {
        $this->compoundParts[] = Lexeme::get_by_id($f->partId);
      }
    }
    return $this->compoundParts;
  }

  function getCompoundsFromPart() {
    return Model::factory('Fragment')
                ->select('Lexeme.*')
                ->join('Lexeme', ['Lexeme.id', '=', 'Fragment.lexemeId'])
                ->group_by('lexemeId')
                ->where('partId', $this->id)
                ->order_by_asc('lexemeId')
                ->find_many();
  }

  // Splits a form into chunks while obeying the lexeme's fragments.
  // In most cases we could just split the form at dashes and spaces.
  // However, in some cases the fragments themselves include delimiters (e.g. 'week-end party').
  // Also returns the real delimiters (skipping those contained inside fragments)
  function getChunks() {
    $chunks = [];
    $delimiters = [];

    // split week-end-party as (week, -, end, ' ', party)
    $parts = preg_split('/([-\s])/', $this->formNoAccent, -1, PREG_SPLIT_DELIM_CAPTURE);
    $lexemes = $this->getCompoundParts();

    // concatenate the chunks (week, -, end) (because the lexeme 'week-end' contains one dash)
    // and (party), with the delimiter ' ' between them
    foreach ($lexemes as $l) {
      $numDelims = preg_match_all('/[-\s]/', $l->formNoAccent);
      $size = 2 * $numDelims + 1;
      $chunk = implode(array_slice($parts, 0, $size));
      $chunks[] = $chunk;
      if (count($parts) > $size) { // there is no delimiter after the final chunk
        $delimiters[] = $parts[$size];
      }
      $parts = array_slice($parts, $size + 1);
    }

    // return ['week-end', 'party'] and [' ']
    return [$chunks, $delimiters];
  }

  function getSources() {
    return Preload::getLexemeSources($this->id);
  }

  function getSourceNames() {
    if ($this->sourceNames === null) {
      $sources = $this->getSources();
      $results = Util::objectProperty($sources, 'shortName');
      $this->sourceNames = implode(', ', $results);
    }
    return $this->sourceNames;
  }

  function getTags() {
    return Preload::getLexemeTags($this->id);
  }

  function setTags(array $tagIds) {
    Preload::setLexemeTags($this->id, $tagIds);
  }

  function getDisplayPronunciations() {
    return Str::htmlize($this->pronunciations)[0];
  }

  function isVerb() {
    return in_array($this->modelType, ['V', 'VT']);
  }

  function isAnimate() {
    if ($this->animate === null) {
      $this->animate = false;
      foreach ($this->getTags() as $t) {
        if (in_array($t->value, Config::TAG_ANIMATE_LEXEME)) {
          $this->animate = true;
        }
      }
    }
    return $this->animate;
  }

  function setAnimate($animate) {
    $this->animate = $animate;
  }

  /* If the list contains any verbs, remove any non-verbs. */
  static function filterVerbs($lexemes) {
    $hasVerb = false;
    foreach ($lexemes as $l) {
      $hasVerb |= $l->isVerb();
    }
    if ($hasVerb) {
      $lexemes = array_filter($lexemes, function($l) {
        return $l->isVerb();
      });
    }
    return $lexemes;
  }

  static function loadByExtendedName($extName) {
    $parts = preg_split('/\(/', $extName, 2);
    $name = addslashes(trim($parts[0]));
    if (count($parts) == 2) {
      $description = addslashes(trim($parts[1]));
      $description = str_replace(')', '', $description);
    } else {
      $description = '';
    }
    return Model::factory('Lexeme')->where('formNoAccent', $name)->where('description', $description)->find_many();
  }

  // For V1, this loads all lexeme models in (V1, VT1)
  static function loadByCanonicalModel($modelType, $modelNumber, $limit = 0) {
    $q = Model::factory('Lexeme')
      ->table_alias('l')
      ->select('l.*')
      ->join('ModelType', 'l.modelType = mt.code', 'mt')
      ->where('mt.canonical', $modelType)
      ->where('l.modelNumber', $modelNumber)
      ->order_by_asc('l.formNoAccent');

    if ($limit) {
      $q = $q->limit($limit);
    }

    return $q->find_many();
  }

  /**
   * Checks if all the $forms are equal, allowing for case differences.
   *   * If there is only one form or multiple equal forms, then returns the
         form, in uppercase if all the forms are in uppercase, in lowercase
         otherwise.
   *   * If there are multiple equal forms, returns false.
   *   * If there are no forms, returns null.
   */
  static function _canonicalizeHelper(array $forms) {
    if (empty ($forms)) {
      return null;
    }

    $forms = array_column($forms, 'formNoAccent');

    rsort($forms); // prefer lowercase
    $candidate = $forms[0];
    $lowerCandidate = mb_strtolower($candidate);

    $i = 1;
    while (($i < count($forms)) &&
           (mb_strtolower($forms[$i]) == $lowerCandidate)) {
      $i++;
    }

    return ($i == count($forms)) ? $candidate : false;
  }

  /**
   * Tries to canonicalize an inflected form. If there are multiple matches or
   * no matches, then returns null. We don't use MySQL's distinct clause
   * because it doesn't play nice with case differences (urs / Urs etc.)
   */
  static function canonicalize(string $query, bool $hasDiacritics) {
    $field = $hasDiacritics ? 'formNoAccent' : 'formUtf8General';

    // Case 1: a single base form, e.g. 'padurilor' => 'pădure'
    $forms = Model::factory('Lexeme')
      ->table_alias('l')
      ->select('l.formNoAccent')
      ->join('InflectedForm', ['l.id', '=', 'i.lexemeId'], 'i')
      ->where("i.{$field}", $query)
      ->find_array();

    $canonical = self::_canonicalizeHelper($forms);
    if ($canonical !== false) {
      return $canonical;
    }

    // Case 2: multiple base forms, but a single inflected form, e.g. 'sageti'
    // => 'săgeți' (base forms: 'săgeată', 'săget', 'săgeți')
    $forms = Model::factory('InflectedForm')
      ->select('formNoAccent')
      ->where($field, $query)
      ->find_array();
    return self::_canonicalizeHelper($forms);
  }

  static function searchApproximate($cuv) {
    $forms = Levenshtein::closest($cuv);

    $entries = [];
    foreach ($forms as $form) {
      $formEntries = Model::factory('Entry')
        ->table_alias('e')
        ->select('e.*')
        ->join('EntryLexeme', ['e.id', '=', 'el.entryId'], 'el')
        ->join('Lexeme', ['el.lexemeId', '=', 'l.id'], 'l')
        ->where('l.formNoAccent', $form)
        ->find_many();
      foreach ($formEntries as $e) {
        $entries[] = $e;
      }
    }

    $entries = array_unique($entries, SORT_REGULAR);
    return $entries;
  }

  static function searchByPronunciations($cuv) {
    $entries = Model::factory('Entry')
      ->table_alias('e')
      ->select('e.*')
      ->join('EntryLexeme', ['e.id', '=', 'el.entryId'], 'el')
      ->join('Lexeme', ['el.lexemeId', '=', 'l.id'], 'l')
      ->where_raw("FIND_IN_SET(?, REPLACE(l.pronunciations, ' ', ''))", [$cuv])
  //    ->where('l.pronunciations', $cuv)
      ->find_many();

    $entries = array_unique($entries, SORT_REGULAR);
    return $entries;
  }

  static function getRegexpQuery($regexp, $hasDiacritics, $sourceId) {
    $mysqlRegexp = Str::dexRegexpToMysqlRegexp($regexp);
    $field = $hasDiacritics ? 'formNoAccent' : 'formUtf8General';

    if ($sourceId) {
      // Suppress warnings from idiorm's log query function, which uses vsprintf,
      // which trips on extra % signs.
      return @Model::factory('Lexeme')
        ->table_alias('l')
        ->join('EntryLexeme', ['l.id', '=', 'el.lexemeId'], 'el')
        ->join('EntryDefinition', ['el.entryId', '=', 'ed.entryId'], 'ed')
        ->join('Definition', ['ed.definitionId', '=', 'd.id'], 'd')
        ->where_raw("$field $mysqlRegexp")
        ->where('d.sourceId', $sourceId);
    } else {
      // even where there is no sourceId, make sure the lexeme has associated entries
      // (fragments don't)
      return @Model::factory('Lexeme')
        ->table_alias('l')
        ->join('EntryLexeme', ['l.id', '=', 'el.lexemeId'], 'el')
        ->where_raw("$field $mysqlRegexp");
    }
  }

  static function searchRegexp($regexp, $hasDiacritics, $sourceId, $count = false) {
    try {
      $q = self::getRegexpQuery($regexp, $hasDiacritics, $sourceId);
      if ($count) {
        $result = $q
          ->select_expr('count(distinct l.id)', 'count')
          ->find_array();
        $result = $result[0]['count'];
      } else {
        $result = $q
          ->select('l.*')
          ->distinct()
          ->order_by_asc('l.formNoAccent')
          ->limit(1000)
          ->find_many();
      }
    } catch (Exception $e) {
      $result = $count ? 0 : []; // Bad regexp
    }

    return $result;
  }

  /**
   * For every set of lexemes having the same form and no description, load one of them at random.
   */
  static function loadAmbiguous() {
    // The key here is to create a subquery of all the forms appearing at least twice
    // This takes about 0.6s
    $query = 'select * from Lexeme ' .
      'join (select binary form as f from Lexeme group by form having count(*) > 1) dup ' .
      'on form = f ' .
      'where description = "" ' .
      'group by form ' .
      'having count(*) > 1';
    return Model::factory('Lexeme')->raw_query($query)->find_many();
  }

  /**
   * Load main lexemes from structured entries that are missing tags with isPos=true.
   */
  static function loadWithoutPos() {
    // lexeme IDs that do have a part of speech tag
    $subquery = [
      'select ot.objectId',
      'from ObjectTag ot',
      'join Tag t on ot.tagId = t.id',
      'where ot.objectType = %d',
      'and t.isPos',
    ];
    $subquery = sprintf(implode(' ', $subquery), ObjectTag::TYPE_LEXEME);

    // main lexemes from structured entries not in the ID set above
    $query = [
      'select distinct l.* from Lexeme l',
      'join EntryLexeme el on l.id = el.lexemeId',
      'join Entry e on el.entryId = e.id',
      'where el.main',
      'and e.structStatus in (%s)',
      'and l.id not in (%s)',
    ];
    $query = sprintf(implode(' ', $query),
                     implode(',', Entry::PRINTABLE_STATUSES),
                     $subquery);
    return Model::factory('Lexeme')->raw_query($query)->find_many();
  }

  static function countStaleParadigms() {
    return Model::factory('Lexeme')
      ->where('staleParadigm', true)
      ->count();
  }

  static function getStaleParadigms($limit = 10) {
    return Model::factory('Lexeme')
      ->where('staleParadigm', true)
      ->limit($limit)
      ->find_many();
  }

  /**
   * Counts lexemes not associated with any entries.
   **/
  static function countUnassociated() {
    return count(self::getUnassociated());
  }

  /**
   * Returns lexemes not associated with any entries. Lexemes can be associated directly or they can
   * be fragments of associated lexemes.
   **/
  static function getUnassociated() {
    $direct = 'select lexemeId as id from EntryLexeme';
    $fragments = 'select partId as id from Fragment';
    $subquery = "$direct union $fragments";
    $query = 'select l.* ' .
      'from Lexeme l ' .
      "left outer join ($subquery) used on l.id = used.id " .
      'where used.id is null';

    return Model::factory('Lexeme')->raw_query($query)->find_many();
  }

  /**
   * Returns an array of InflectedForms. These can be loaded from the disk ($method = METHOD_LOAD)
   * or generated on the fly ($method = METHOD_GENERATE).
   * Throws ParadigmException for METHOD_GENERATE if any inflection cannot be generated.
   **/
  function getInflectedForms($method) {
    return ($method == self::METHOD_LOAD)
      ? $this->loadInflectedForms()
      : $this->generateInflectedForms();
  }

  function loadInflectedForms() {
    if ($this->inflectedForms === null) {
      $this->inflectedForms = Preload::getLexemeInflectedForms($this->id);
    }
    return $this->inflectedForms;
  }

  // throws ParadigmException if any inflection cannot be generated
  function generateInflectedForms() {
    if ($this->inflectedForms === null) {

      $this->inflectedForms = [];

      if ($this->compound) {

        // generate forms for compound lexemes
        $inflections = Model::factory('Inflection')
          ->table_alias('i')
          ->select('i.*')
          ->join('ModelType', ['i.modelType', '=', 'mt.canonical'], 'mt')
          ->where('mt.code', $this->modelType)
          ->order_by_asc('i.rank')
          ->find_many();
        foreach ($inflections as $inflId => $i) {
          $ifs = $this->generateCompoundForms($i);
          $this->inflectedForms = array_merge($this->inflectedForms, $ifs);
        }

      } else {

        // generate forms for simple lexemes

        $model = FlexModel::loadCanonicalByTypeNumber($this->modelType, $this->modelNumber);
        $inflIds = DB::getArray("select distinct inflectionId from ModelDescription " .
                                "where modelId = {$model->id} order by inflectionId");

        foreach ($inflIds as $inflId) {
          $ifs = $this->generateInflectedFormWithModel($this->form, $inflId, $model->id);
          $this->inflectedForms = array_merge($this->inflectedForms, $ifs);
        }

      }

      $this->appendElidedForms();
    }

    return $this->inflectedForms;
  }

  function appendElidedForms() {
    // collect map of (inflectionId, variant) for all ModelDescriptions that
    // support apocope
    $mds = Model::factory('ModelDescription')
      ->table_alias('md')
      ->select('md.inflectionId')
      ->select('md.variant')
      ->join('Model', ['md.modelId', '=', 'm.id'], 'm')
      ->join('ModelType', ['m.modelType', '=', 'mt.canonical'], 'mt')
      ->where('m.number', $this->modelNumber)
      ->where('mt.code', $this->modelType)
      ->where('md.hasApocope', true)
      ->find_array();
    $map = [];
    foreach ($mds as $md) {
      $map[$md['inflectionId']][$md['variant']] = true;
    }

    // clone inflected forms that support apocope
    if ($this->hasApocope) {
      $forms = [];
      foreach ($this->inflectedForms as $if) {
        if (isset($map[$if->inflectionId][$if->variant])) {
          $forms[] = $if->createApocope();
        }
      }
      $this->inflectedForms = array_merge($this->inflectedForms, $forms);
    }

    // clone inflected forms starting with [']?î
    if ($this->hasApheresis) {
      $forms = [];
      foreach ($this->inflectedForms as $if) {
        if (Str::startsWith($if->formNoAccent, 'î')) {
          $forms[] = $if->createApheresis();
        }
      }
      $this->inflectedForms = array_merge($this->inflectedForms, $forms);
    }
  }

  // for METHOD_GENERATE, throws ParadigmException if any inflection cannot be generated
  function getInflectedFormMap($method) {
    if ($this->inflectedFormMap === null) {
      $ifs = $this->getInflectedForms($method);
      $this->inflectedFormMap = InflectedForm::mapByInflectionRank($ifs);
    }
    return $this->inflectedFormMap;
  }

  function loadInflectedFormMap() {
    return $this->getInflectedFormMap(self::METHOD_LOAD);
  }

  // throws ParadigmException if any inflection cannot be generated
  function generateInflectedFormMap() {
    return $this->getInflectedFormMap(self::METHOD_GENERATE);
  }

  // throws ParadigmException if the given inflection cannot be generated
  function generateCompoundForms($infl) {
    if (!ConstraintMap::validInflectionCached($infl->id, $this->restriction) ||
        ($infl->animate && !$this->isAnimate())) {
      return [];
    }

    $fragments = $this->getFragments();
    $parts = $this->getCompoundParts();  // lexemes
    list($chunks, $delimiters) = $this->getChunks();

    if (count($chunks) != count($fragments)) {
      throw new ParadigmException(
        $infl->id,
        sprintf("Lexemul este compus din %d părți, dar ați indicat %d fragmente.",
                count($chunks), count($fragments))
      );
    }

    $forms = [];
    $lastFragmentInfo = null;

    foreach ($parts as $i => $p) {
      $frag = $fragments[$i];
      $chunk = $chunks[$i];

      if ($frag->declension == Fragment::DEC_INVARIABLE) {
        $if = InflectedForm::getByLexemeChunk($p, $chunk, $infl);
      } else {
        // Decide what inflection to use for the part from the part's model type and declension.
        // Generate it on the fly (this works even if the part normally restricts that inflection).
        $partInfl = Fragment::getInflection($infl, $p->modelType, $frag->declension);
        $model = FlexModel::loadCanonicalByTypeNumber($p->modelType, $p->modelNumber);
        $ifs = $p->generateInflectedFormWithModel($p->form, $partInfl->id, $model->id, false);
        $if = count($ifs) ? $ifs[0] : InflectedForm::create('?');
        if ($i == count($parts) - 1) {
          $lastFragmentInfo = [
            'lexeme' => $p,
            'model' => $model,
            'inflection' => $partInfl,
          ];
        };
      }

      $f = $if->form;

      if ($frag->capitalized) {
        $f = Str::capitalizeWithAccent($f);
      }

      if (!$frag->accented) {
        // keep literal apostrophes, e.g. o\'clock
        $f = preg_replace("/(?<!\\\\)'/", '', $f);
      }

      $forms[] = $f;
    }

    $f = implode(Util::interleaveArrays($forms, $delimiters));
    $if = InflectedForm::create($f, $this->id, $infl->id, 0, true);
    $results = [ $if ];

    // check if we need to add an apocope form
    if ($this->hasApocope &&
        $lastFragmentInfo &&
        $lastFragmentInfo['lexeme']->hasApocope) {
      $md = Model::factory('ModelDescription')
        ->where('modelId', $lastFragmentInfo['model']->id)
        ->where('inflectionId', $lastFragmentInfo['inflection']->id)
        ->where('variant', 0)
        ->where('hasApocope', true)
        ->find_one();
      if ($md) {
        $results[] = $if->createApocope();
      }
    }

    return $results;
  }

  // We may ignore restrictions when the lexeme is part of a compound lexeme
  // throws ParadigmException if the given inflection cannot be generated
  function generateInflectedFormWithModel($form, $inflId, $modelId, $obeyRestrictions = true) {
    $inflection = Inflection::loadByIdCached($inflId);
    if ($inflection->animate && !$this->isAnimate()) {
      // animate inflections, like the vocative, require the lexeme to be animate
      return [];
    }

    $ifs = [];
    $mds = ModelDescription::loadForInflectionCached($modelId, $inflId);

    $start = 0;
    while ($start < count($mds)) {
      $variant = $mds[$start]->variant;
      $recommended = $mds[$start]->recommended;

      // Identify all the md's that differ only by the applOrder
      $end = $start + 1;
      while ($end < count($mds) && $mds[$end]->applOrder != 0) {
        $end++;
      }

      if (!$obeyRestrictions ||
          ConstraintMap::validInflectionCached($inflId, $this->restriction, $variant)) {
        $inflId = $mds[$start]->inflectionId;
        $accentShift = $mds[$start]->accentShift;
        $vowel = $mds[$start]->vowel;

        // Load and apply all the transforms from $start to $end - 1.
        $transforms = [];
        for ($i = $end - 1; $i >= $start; $i--) {
          $transforms[] = Transform::loadByIdCached($mds[$i]->transformId);
        }

        $result = FlexStr::applyTransforms($form, $transforms, $accentShift, $vowel);
        if (!$result) {
          throw new ParadigmException($inflId, 'Nu pot genera forma.');
        }
        $ifs[] = InflectedForm::create($result, $this->id, $inflId, $variant, $recommended);
      }

      $start = $end;
    }

    return $ifs;
  }

  /**
   * Deletes the lexeme's old inflected forms, if they exist, then saves the new ones.
   * Clears the staleParadigm bit and saves the lexeme.
   * Throws ParadigmException if any inflection cannot be generated.
   **/
  function regenerateParadigm() {
    if ($this->id) {
      // do NOT delete them one by one -- it's very slow
      DB::execute("delete from InflectedForm where lexemeId = {$this->id}");
    }

    $ifs = $this->generateInflectedForms();
    InflectedForm::batchInsert($ifs, $this->id);

    // only if no exception was thrown
    $this->staleParadigm = false;
    $this->save();
  }

  // apply tags required by harmonization rules
  function harmonizeTags() {
    $hts = Model::factory('HarmonizeTag')
      ->where('modelType', $this->modelType)
      ->where_in('modelNumber', ['', $this->modelNumber])
      ->find_many();
    foreach ($hts as $ht) {
      // apply the tag only if the lexeme doesn't have the tag or any descendant tag
      $descendantIds = Tag::getDescendantIds($ht->tagId);
      $ot = Model::factory('ObjectTag')
        ->where('objectId', $this->id)
        ->where('objectType', ObjectTag::TYPE_LEXEME)
        ->where_in('tagId', $descendantIds)
        ->find_one();
      if (!$ot) {
        ObjectTag::associate(ObjectTag::TYPE_LEXEME, $this->id, $ht->tagId);
      }
    }
  }

  /**
   * Returns an inflection suitable for exemplifying the paradigm:
   *   - the indicative present first person for verbs;
   *   - the feminine for adjectives and pronouns;
   *   - the plural for nouns;
   *   - null for invariables or if the above is not defined.
   */
  function getSampleInflectedForm() {
    $canonical = ModelType::canonicalize($this->modelType);
    $map = $this->loadInflectedFormMap();

    switch ($canonical) {
      case 'A':
        return $map[9][0] ?? null;
      case 'F':
      case 'M':
      case 'N':
        return $map[3][0] ?? null;
      case 'P':
        return $map[5][0] ?? null;
      case 'V':
        return $map[9][0] ?? null;
      default:
        return null;
    }
  }

  // change the model given the tags, according to harmonization rules
  function harmonizeModel($tagIds) {
    if (empty($tagIds)) {
      return;
    }

    $tagIdsWithAncestors = [];
    foreach ($tagIds as $tagId) {
      $ancestorIds = Tag::getAncestorIds($tagId);
      $tagIdsWithAncestors = array_merge($tagIdsWithAncestors, $ancestorIds);
    }

    $hm = Model::factory('HarmonizeModel')
      ->where('modelType', $this->modelType)
      ->where_in('modelNumber', ['', $this->modelNumber])
      ->where_in('tagId', $tagIdsWithAncestors)
      ->find_one();

    if ($hm) {
      $this->modelType = $hm->newModelType;
      if ($hm->newModelNumber) {
        $this->modelNumber = $hm->newModelNumber;
      }
    }
  }

  function regenerateDependentLexemes() {
    if ($this->modelType == 'VT') {
      $infl = Inflection::loadParticiple();

      $pm = ParticipleModel::get_by_verbModel($this->modelNumber);
      $number = $pm->adjectiveModel;

      $this->_regenerateDependentLexemesHelper($infl, 'A', 'PT', $number);
    }
    if ($this->isVerb()) {
      $infl = Inflection::loadLongInfinitive();

      // there could be several forms - just load the first one
      $longInfinitive = InflectedForm::get_by_lexemeId_inflectionId($this->id, $infl->id);
      $are = $longInfinitive && Str::endsWith($longInfinitive->formNoAccent, 'are');
      $number = $are ? 113 : 107;

      $this->_regenerateDependentLexemesHelper($infl, 'F', 'IL', $number);
    }
  }
  /**
   * Finds greatest homonym/homograph index number
   *
   * @param none
   * @return integer
   */
  function getNextHomonymNumber() {
    $l = Lexeme::get_all_by_formNoAccent($this->formNoAccent);
    $numbers = Util::objectProperty($l, 'number');

    if (!Util::isIncrementalSequence($numbers)) {
      FlashMessage::add("Inconsecvență în numerotarea omonimelor/omografelor.", 'warning');
    }

    $num = max($numbers);

    return ++$num;
  }

  private function _regenerateDependentLexemesHelper($infl, $genericType, $dedicatedType, $number) {
    // process only non-apheresis forms (do not create a PT lexeme for nzăpezit)
    $ifs = InflectedForm::get_all_by_lexemeId_inflectionId_apheresis(
      $this->id, $infl->id, false);

    foreach ($ifs as $if) {
      // look for an existing lexeme
      $l = Model::factory('Lexeme')
        ->where('formNoAccent', $if->formNoAccent)
        ->where_in('modelType', [$genericType, $dedicatedType])
        ->where('modelNumber', $number)
        ->find_one();

      if (!$l) {
        // if a lexeme exists with this form, but a different model, give a warning
        $existing = Lexeme::get_by_formNoAccent($if->formNoAccent);
        if ($existing) {
          FlashMessage::addTemplate('lexemeExists.tpl', [ 'lexeme' => $existing ], 'warning');
        }

        $l = Lexeme::create($if->form, $dedicatedType, $number, '');
        $l->deepSave();

        $entry = Entry::createAndSave($if->formNoAccent);
        EntryLexeme::associate($entry->id, $l->id);

        // copy trees and structure information from one of the lexeme's entries
        $infEntries = $this->getEntries();
        if (!empty($infEntries)) {
          $infEntry = $infEntries[0];
          TreeEntry::copy($infEntry->id, $entry->id, 2);
          $entry->structStatus = $infEntry->structStatus;
          $entry->structuristId = $infEntry->structuristId;
          $entry->save();
        }

        $l->harmonizeTags();

        // Also associate the new entry with the same definitions as $this.
        foreach ($this->getEntries() as $e) {
          foreach ($e->getDefinitions() as $d) {
            EntryDefinition::associate($entry->id, $d->id);
          }
        }
        FlashMessage::addTemplate('dependentLexemeCreated.tpl', [ 'lexeme' => $l ], 'info');
      }
    }
  }

  /**
   * Called when the lexeme is deleted or its model type changes to a non-VT.
   * Only deletes PT participles, not A participles.
   */
  function deleteParticiple() {
    if ($this->isVerb()) {
      $infl = Inflection::loadParticiple();
      $pm = ParticipleModel::get_by_verbModel($this->modelNumber);
      $this->_deleteDependentLexemes($infl->id, ['VT'], 'PT', [$pm->adjectiveModel]);
    }
  }

  /**
   * Called when the lexeme is deleted or its model type changes to a non-verb.
   * Only deletes IL long infinitives, not F long infinitives.
   */
  function deleteLongInfinitive() {
    $infl = Inflection::loadLongInfinitive();
    $this->_deleteDependentLexemes($infl->id, ['V', 'VT'], 'IL', ['107', '113']);
  }

  // deletes dependent lexemes and their entries
  private function _deleteDependentLexemes($inflId, $mainTypes, $depType, $modelNumbers) {
    // Iterate through all the forms of the desired inflection (participle / long infinitive)
    $ifs = InflectedForm::get_all_by_lexemeId_inflectionId($this->id, $inflId);
    foreach ($ifs as $if) {
      // Examine all lexemes having one of the above forms and model
      $lexemes = Model::factory('Lexeme')
        ->where('formNoAccent', $if->formNoAccent)
        ->where('modelType', $depType)
        ->where_in('modelNumber', $modelNumbers)
        ->find_many();
      foreach ($lexemes as $l) {
        // Check if any other lexeme still generates this form.
        $others = Model::factory('Lexeme')
          ->table_alias('l')
          ->join('InflectedForm', ['l.id', '=', 'i.lexemeId'], 'i')
          ->where('i.formNoAccent', $if->formNoAccent)
          ->where('i.inflectionId', $inflId)
          ->where_in('l.modelType', $mainTypes)
          ->where_not_equal('l.id', $this->id)
          ->count();

        if ($others) {
          FlashMessage::add(
            "Am păstrat lexemul dependent {$l} deoarece alte lexeme îl generează.",
            'info');
        } else {
          FlashMessage::add("Am șters automat lexemul {$l} și toate intrările asociate.", 'info');
          $entries = Model::factory('Entry')
            ->table_alias('e')
            ->select('e.*')
            ->join('EntryLexeme', ['e.id', '=', 'el.entryId'], 'el')
            ->where('el.lexemeId', $l->id)
            ->find_many();
          foreach ($entries as $e) {
            $e->delete();
          }
          $l->delete();
        }
      }
    }
  }

  // returns one of the CAN_DELETE_* constants
  function canDelete() {
    // cannot delete lexemes which are fragments of compound lexemes
    if (Fragment::get_by_partId($this->id)) {
      return self::CAN_DELETE_FRAGMENT;
    }

    return self::CAN_DELETE_OK;
  }

  function delete() {
    if ($this->id) {
      if ($this->modelType == 'VT') {
        $this->deleteParticiple();
      }
      if ($this->isVerb()) {
        $this->deleteLongInfinitive();
      }
      InflectedForm::delete_all_by_lexemeId($this->id);
      EntryLexeme::delete_all_by_lexemeId($this->id);
      LexemeSource::delete_all_by_lexemeId($this->id);
      ObjectTag::delete_all_by_objectId_objectType($this->id, ObjectTag::TYPE_LEXEME);
      Fragment::delete_all_by_lexemeId($this->id);
      Fragment::delete_all_by_partId($this->id);
      // delete_all_by_lexemeId doesn't work for FullTextIndex because it doesn't have an ID column
      Model::factory('FullTextIndex')->where('lexemeId', $this->id)->delete_many();
    }
    Log::warning("Deleted lexeme {$this->id} ({$this->formNoAccent})");
    parent::delete();
  }

  function save() {
    $this->formUtf8General = $this->formNoAccent;
    $this->reverse = Str::reverse($this->formNoAccent);
    $this->consistentAccent =
      preg_match("/(^|[^\\\\])'./", $this->form) ^
      $this->noAccent;
    // It is important for empty fields to be null, not "".
    // This allows queries for records with non-null values to run faster.
    if (!$this->number) {
      $this->number = null;
    }
    parent::save();
  }

  /**
   * Saves a lexeme and its dependants.
   *
   * WARNING: This DELETES existing DB fragments and tags. Make sure to set
   * them on the in-memory object before calling this function.
   **/
  function deepSave() {
    $this->staleParadigm = false;
    $this->save();

    Fragment::delete_all_by_lexemeId($this->id);
    DB::execute("delete from InflectedForm where lexemeId = {$this->id}");

    $tagIds = Util::objectProperty($this->getTags(), 'id');
    ObjectTag::wipeAndRecreate($this->id, ObjectTag::TYPE_LEXEME, $tagIds);

    foreach ($this->getFragments() as $f) {
      $f->lexemeId = $this->id;
      $f->save();
    }
    foreach ($this->generateInflectedForms() as $if) {
      $if->lexemeId = $this->id;
      $if->save();
    }
  }

  function __toString() {
    return $this->description ? "{$this->formNoAccent} ({$this->description})" : $this->formNoAccent;
  }
  /**
   * First parameter, $cloneEntries, is an multidimensional array received by cloneModal with
   * a maximum 2 elements 0 and 1 (checkboxes from modal), each of type array with value 'on'.
   * Values 'off' are not sent from checkboxes.
   *
   * @param array $cloneEntries
   * @param boolean $cloneInflectedForms
   * @param boolean $cloneTags
   * @param boolean $cloneSources
   * @return ORMWrapper
   */
  function _clone($cloneEntries, $cloneInflectedForms, $cloneTags, $cloneSources ) {
    $clone = $this->parisClone();
    $clone->number = $this->getNextHomonymNumber();
    if (!$cloneInflectedForms) {
      $clone->modelType = 'T';
      $clone->modelNumber = '1';
    }
    $clone->save();

    foreach ($cloneEntries as $key => $ignored) {
      EntryLexeme::copy($this->id, $clone->id, 2, ['main' => $key]);
    }

    if ($cloneInflectedForms) {
      if ($this->compound) {
        Fragment::copy($this->getFragments(), $clone->id);
      }
      InflectedForm::copy($this->getInflectedForms(self::METHOD_LOAD), $clone->id);
    }

    if ($cloneTags) {
      foreach ($this->getTags() as $tag) {
        ObjectTag::associate(ObjectTag::TYPE_LEXEME, $clone->id, $tag->id);
      }
    }

    if ($cloneSources) {
      LexemeSource::copy($this->id, $clone->id, 1);
    }
    return $clone;
  }

}
