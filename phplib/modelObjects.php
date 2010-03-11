<?

ADOdb_Active_Record::$_changeNames = false; // Do not pluralize table names

class BaseObject extends ADOdb_Active_Record {
  public function save() {
    if ($this->createDate === null) {
      $this->createDate = $this->modDate = time();
    }
    if (is_string($this->modDate)) {
      $this->modDate = time();
    }
    parent::save();
  }
}

class GuideEntry extends BaseObject {
  public function normalize() {
    $this->correct = text_internalizeDefinition($this->correct);
    $this->wrong = text_internalizeDefinition($this->wrong);
    $this->comments = text_internalizeDefinition($this->comments);
    
    $this->correctHtml = text_htmlizeWithNewlines($this->correct, TRUE);
    $this->wrongHtml = text_htmlizeWithNewlines($this->wrong, TRUE);
    $this->commentsHtml = text_htmlizeWithNewlines($this->comments, TRUE);    
  }
}


class Source extends BaseObject {
  // Static version of load()
  public static function get($where) {
    $obj = new Source();
    $obj->load($where);
    return $obj->id ? $obj : null;
  }
}


class Cookie extends BaseObject {
  public static function get($where) {
    $obj = new Cookie();
    $obj->load($where);
    return $obj->id ? $obj : null;
  }
}


class User extends BaseObject {
  public static function get($where) {
    $obj = new User();
    $obj->load($where);
    return $obj->id ? $obj : null;
  }

  public function __toString() {
    return $this->nick;
  }
}


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
    $dbResult = db_execute("select Definition.* from Definition, LexemDefinitionMap where Definition.id = LexemDefinitionMap.DefinitionId " .
                           "and LexemDefinitionMap.LexemId = {$lexemId} and status in (0, 1) order by sourceId");
    return db_getObjects(new Definition(), $dbResult);
  }

  public static function countAssociated() {
    // same as select count(distinct DefinitionId) from LexemDefinitionMap, only faster.
    return db_getSingleValue('select count(*) from (select count(*) from LexemDefinitionMap group by DefinitionId) as someLabel');
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
    $dbResult = db_execute(sprintf("select distinct D.* from Definition D, LexemDefinitionMap L, Source S " .
                                   "where D.id = L.DefinitionId and L.LexemId in (%s) and D.sourceId = S.id and D.status = 0 %s %s " .
                                   "order by (D.lexicon = '$preferredWord') desc, S.isOfficial desc, D.lexicon, S.displayOrder",
                                   $lexemIds, $excludeClause, $sourceClause));
    return db_getObjects(new Definition(), $dbResult);
  }

  public static function searchLexemId($lexemId, $exclude_unofficial = false) {
    $excludeClause = $exclude_unofficial ? "and S.isOfficial <> 0 " : '';
    $dbResult = db_execute("select D.* from Definition D, LexemDefinitionMap L, Source S where D.id = L.DefinitionId " .
                           "and D.sourceId = S.id and L.LexemId = '$lexemId' $excludeClause and D.status = 0 " .
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
    $regexp = text_dexRegexpToMysqlRegexp($cuv);
    $sourceClause = $sourceId ? "and Definition.sourceId = $sourceId" : '';
    $userClause = $userId ? "and Definition.userId = $userId" : '';

    if ($status == ST_DELETED) {
      // Deleted definitions are not associated with any lexem
      $collate = $hasDiacritics ? '' : 'collate utf8_general_ci';
      return db_find(new Definition(), "lexicon $collate $regexp and status = " . ST_DELETED . " and createDate between $beginTime and $endTime " .
                     "$sourceClause $userClause order by lexicon, sourceId limit 500");
    } else {
      $field = $hasDiacritics ? 'lexem_neaccentuat' : 'lexem_utf8_general';
      $dbResult = db_execute("select distinct Definition.* from lexems join LexemDefinitionMap on lexem_id = LexemDefinitionMap.LexemId " .
                             "join Definition on LexemDefinitionMap.DefinitionId = Definition.id where $field $regexp " .
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


class Typo extends BaseObject {
}

class SearchResult {
  public $definition;
  public $user;
  public $source;
  public $typos;
  public $comment;
  public $commentAuthor = NULL;

  public static function mapDefinitionArray($definitionArray) {
    $results = array();
    foreach($definitionArray as $definition) {
      $result = new SearchResult();
      $result->definition = $definition;
      $result->user = User::get("id = $definition->userId");
      $result->source = Source::get("id={$definition->sourceId}");
      $result->typos = db_find(new Typo(), "definitionId = {$definition->id}");
      $result->comment = Comment::get("definitionId = {$definition->id} and status = " . ST_ACTIVE);
      if ($result->comment) {
        $result->commentAuthor = User::get("id = {$result->comment->userId}");
      }
      $results[] = $result;
    }
    return $results;
  }
}


class TopEntry {
  public $userNick;
  public $numChars;
  public $numDefinitions;
  public $timestamp; // of last submission
  public $days; // since last submission

  private static function loadUnsortedTopData() {
    $dbResult = db_selectTop();
    $topEntries = array();
    $now = time();

    while ($dbRow = mysql_fetch_assoc($dbResult)) {
      $topEntry = new TopEntry();
      $topEntry->userNick = $dbRow['Nick'];
      $topEntry->numDefinitions = $dbRow['NumDefinitions'];
      $topEntry->numChars = $dbRow['NumChars'];
      $topEntry->timestamp = $dbRow['Timestamp'];
      $topEntry->days = intval(($now - $topEntry->timestamp) / 86400);
      $topEntries[] = $topEntry;
    }

    return $topEntries;
  }

  private static function getUnsortedTopData() {
    $data = fileCache_getTop();
    if (!$data) {
      $data = TopEntry::loadUnsortedTopData();
      fileCache_putTop($data);
    }
    return $data;
  }

  /**
   * Returns an array of user stats, sorted according to the given criterion
   * and in the given order. Includes a cache lookup.
   *
   * @param crit  Criterion to sorty by
   * @param ord  Order to sort in (ascending/descending)
   */
  public static function getTopData($crit, $ord) {
    $topEntries = TopEntry::getUnsortedTopData();
    
    $nick = array();
    $numWords = array();
    $numChars = array();
    $date = array();
    foreach ($topEntries as $topEntry) {
      $nick[] = $topEntry->userNick;
      $numWords[] = $topEntry->numDefinitions;
      $numChars[] = $topEntry->numChars;
      $date[] = $topEntry->timestamp;
    }
    
    $ord = (int) $ord;
    if ($crit == CRIT_CHARS) {
      array_multisort($numChars, SORT_NUMERIC, $ord, $nick, SORT_ASC,
		      $topEntries);
    } else if ($crit == CRIT_WORDS) {
      array_multisort($numWords, SORT_NUMERIC, $ord, $nick, SORT_ASC,
		      $topEntries);
    } else if ($crit == CRIT_NICK) {
      array_multisort($nick, $ord, $topEntries);
    } else /* $crit == CRIT_DATE */ {
      array_multisort($date, SORT_NUMERIC, $ord, $nick, SORT_ASC, $topEntries);
    }
    
    return $topEntries;
  }
}

class Comment extends BaseObject {
  function __construct() {
    parent::__construct();
    $this->status = ST_ACTIVE;
  }

  public static function get($where) {
    $obj = new Comment();
    $obj->load($where);
    return $obj->id ? $obj : null;
  }
}

class RecentLink extends BaseObject {
  public static function get($where) {
    $obj = new RecentLink();
    $obj->load($where);
    return $obj->id ? $obj : null;
  }

  public static function createOrUpdate($text) {
    $userId = session_getUserId();
    $url = $_SERVER['REQUEST_URI'];
    $rl = self::get(sprintf("userId = %s and url = '%s' and text = '%s'", $userId, addslashes($url), addslashes($text)));

    if (!$rl) {
      $rl = new RecentLink();
      $rl->userId = $userId;
      $rl->url = $url;
      $rl->text = $text;
    }

    $rl->visitDate = time();
    $rl->save();
  }

  // Also deletes the ones in excess of MAX_RECENT_LINKS
  public static function loadForUser() {
    $userId = session_getUserId();
    $recentLinks = db_find(new RecentLink(), "userId = {$userId} order by visitDate desc");
    while (count($recentLinks) > MAX_RECENT_LINKS) {
      $deadLink = array_pop($recentLinks);
      $deadLink->delete();
    }
    return $recentLinks;
  }
}

class ModelType {
  public $id;
  public $value;
  public $description;

  public static function load($id) {
    $dbRow = db_getModelTypeById($id);
    if ($dbRow) {
      $result = new ModelType();
      $result->populateFromDbRow($dbRow);
      return $result;
    } else {
      return null;
    }
  }

  public static function loadByValue($value) {
    $dbRow = db_getModelTypeByValue($value);
    if ($dbRow) {
      $result = new ModelType();
      $result->populateFromDbRow($dbRow);
      return $result;
    } else {
      return null;
    }    
  }

  public static function loadAll() {
    $dbResult = db_selectAllModelTypes();
    return ModelType::populateFromDbResult($dbResult);
  }

  public static function loadCanonical() {
    $dbResult = db_selectAllCanonicalModelTypes();
    return ModelType::populateFromDbResult($dbResult);    
  }

  public static function canonicalize($modelType) {
    if ($modelType == 'VT') {
      return 'V';
    } else if ($modelType == 'MF') {
      return 'A';
    } else {
      return $modelType;
    }
  }

  public static function getExtendedSet($modelType) {
    if ($modelType == 'A' || $modelType == 'MF') {
      return array('A', 'MF');
    } else if ($modelType == 'V' || $modelType == 'VT') {
      return array('V', 'VT');
    } else {
      return array($modelType);
    }
  }

  public function getExtendedName() {
    return $this->value . ' (' . $this->description . ')';
  }

  public function countModels() {
    return db_countModelsByModelType($this);
  }

  public function populateFromDbRow($dbRow) {
    $this->id = $dbRow['mt_id']; 
    $this->value = $dbRow['mt_value'];
    $this->description = $dbRow['mt_descr'];
  }

  public static function populateFromDbResult($dbResult) {
    $result = array();
    while ($dbRow = mysql_fetch_assoc($dbResult)) {
      $mt = new ModelType();
      $mt->populateFromDbRow($dbRow);
      $result[] = $mt;
    }
    mysql_free_result($dbResult);
    return $result;
  }

  public function save() {
    if ($this->id) {
      db_updateModelType($this);
    } else {
      db_insertModelType($this);
    }
  }

  public function delete() {
    db_deleteModelType($this);
  }
}


class Inflection extends BaseObject {
  public static function get($where) {
    $obj = new Inflection();
    $obj->load($where);
    return $obj->id ? $obj : null;
  }

  public static function loadInfinitive() {
    return self::get("description like '%infinitiv prezent%'");
  }

  public static function loadParticiple() {
    return self::get("description like '%participiu%'");
  }

  public static function loadLongInfinitive() {
    return self::get("description like '%infinitiv lung%'");
  }

  public static function loadByModelType($modelType) {
    $result = array();
    $dbResult = db_execute("select distinct Inflection.* from models, model_description, Inflection " .
                           "where md_model = model_id and md_infl = Inflection.id and model_type = '$modelType' order by md_infl");
    return db_getObjects(new Inflection(), $dbResult);
  }

  public static function mapById($inflections) {
    $result = array();
    foreach ($inflections as $i) {
      $result[$i->id] = $i;
    }
    return $result;
  }
}

class Model {
  public $id;
  public $modelType;
  public $number;
  public $description;
  public $exponent;
  public $flag = 0;

  public static function create($modelType, $number, $description, $exponent) {
    $m = new Model();
    $m->modelType = $modelType;
    $m->number = $number;
    $m->description = $description;
    $m->exponent = $exponent;
    return $m;
  }

  public function getName() {
    return $this->modelType . $this->number;
  }

  public static function load($id) {
    $dbRow = db_getModelById($id);
    if ($dbRow) {
      $result = new Model();
      $result->populateFromDbRow($dbRow);
      return $result;
    } else {
      return null;
    }    
  }

  public static function loadByType($type) {
    if (!function_exists('splitModelNumber')) {
      function splitModelNumber($s) {
        $i = 0;
        $len = strlen($s);
        while ($i < $len && ctype_digit($s[$i])) {
          $i++;
        }
        return array(substr($s, 0, $i), substr($s, $i));
      }
      
      function cmp($a, $b) {
        // Split $a and $b in numbers and versions
        list ($numA, $restA) = splitModelNumber($a->number);
        list ($numB, $restB) = splitModelNumber($b->number);
        if ($numA == $numB) {
          return strcasecmp($restA, $restB);
        } else {
          return $numA - $numB;
        }
      }
    }

    $type = ModelType::canonicalize($type);
    $dbResult = db_getModelsByType($type);
    $models = Model::populateFromDbResult($dbResult);
    usort($models, "cmp");
    return $models;
  }

  public static function loadByTypeNumber($type, $number) {
    $dbRow = db_getModelByTypeNumber($type, $number);
    if ($dbRow) {
      $result = new Model();
      $result->populateFromDbRow($dbRow);
      return $result;
    } else {
      return null;
    }
  }

  public static function loadCanonicalByTypeNumber($type, $number) {
    $type = ModelType::canonicalize($type);
    return Model::loadByTypeNumber($type, $number);
  }

  public static function loadAll() {
    $dbResult = db_selectAllModels();
    return Model::populateFromDbResult($dbResult);
  }

  public function populateFromDbRow($dbRow) {
    $this->id = $dbRow['model_id']; 
    $this->modelType = $dbRow['model_type']; 
    $this->number = $dbRow['model_no']; 
    $this->description = $dbRow['model_descr'];
    $this->exponent = $dbRow['model_exponent'];
    $this->flag = $dbRow['model_flag'];
  }

  public static function populateFromDbResult($dbResult) {
    $result = array();
    while ($dbRow = mysql_fetch_assoc($dbResult)) {
      $model = new Model();
      $model->populateFromDbRow($dbRow);
      $result[] = $model;
    }
    mysql_free_result($dbResult);
    return $result;
  }

  public function save() {
    if ($this->id) {
      db_updateModel($this);
    } else {
      db_insertModel($this);
    }
  }

  public function delete() {
    ModelDescription::deleteByModel($this->id);
    if ($this->modelType == 'V') {
      $pm = ParticipleModel::loadByVerbModel($this->number);
      $pm->delete();
    }
    db_deleteModel($this);
  }

  /** Returns an array containing the type, number and restrictions **/
  public static function splitName($name) {
    $result = array();
    $len = strlen($name);
    $i = 0;
    while ($i < $len && !ctype_digit($name[$i])) {
      $i++;
    }
    $result[] = substr($name, 0, $i);
    $j = $i;
    while ($j < $len && ctype_digit($name[$j])) {
      $j++;
    }
    $result[] = substr($name, $i, $j - $i);
    $result[] = substr($name, $j);
    return $result;
  }
}

class ModelDescription {
  public $id;
  public $modelId;
  public $inflectionId;
  public $variant;
  public $order;
  public $transformId;
  public $accentShift;
  public $accentedVowel;

  public static function create($modelId, $inflectionId, $variant, $order,
                                $transformId, $accentShift, $accentedVowel) {
    $md = new ModelDescription();
    $md->modelId = $modelId;
    $md->inflectionId = $inflectionId;
    $md->variant = $variant;
    $md->order = $order;
    $md->transformId = $transformId;
    $md->accentShift = $accentShift;
    $md->accentedVowel = $accentedVowel;
    return $md;
  }

  public static function loadByModelId($modelId) {
    $dbResult = db_getModelDescriptionsByModelId($modelId);
    return ModelDescription::populateFromDbResult($dbResult);
  }

  public static function loadByModelIdInflectionId($modelId, $inflId) {
    $dbResult = db_getModelDescriptionsByModelIdInflId($modelId, $inflId);
    return ModelDescription::populateFromDbResult($dbResult);
  }

  public function populateFromDbRow($dbRow) {
    $this->id = $dbRow['md_id'];
    $this->modelId = $dbRow['md_model']; 
    $this->inflectionId = $dbRow['md_infl']; 
    $this->variant = $dbRow['md_variant']; 
    $this->order = $dbRow['md_order']; 
    $this->transformId = $dbRow['md_transf'];
    $this->accentShift = $dbRow['md_accent_shift'];
    $this->accentedVowel = $dbRow['md_vowel'];
  }

  public static function populateFromDbResult($dbResult) {
    $result = array();
    while ($dbRow = mysql_fetch_assoc($dbResult)) {
      $md = new ModelDescription();
      $md->populateFromDbRow($dbRow);
      $result[] = $md;
    }
    mysql_free_result($dbResult);
    return $result;
  }

  public function save() {
    if ($this->id) {
      db_updateModelDescription($this);
    } else {
      db_insertModelDescription($this);
    }
  }

  public static function deleteByModelInflection($modelId, $inflectionId) {
    return db_deleteModelDescriptionsByModelInflection($modelId, $inflectionId);
  }

  public static function deleteByModel($modelId) {
    return db_deleteModelDescriptionsByModel($modelId);
  }
}

class Lexem {
  public $id;
  public $form;
  // Lexem::unaccented is mapped to two fields in the database,
  // lexem_neaccentuat and lexem_utf8_general. These fields will absolutely
  // always have identical values, but they are stored with different
  // collations to speed up searches with/without diacritics.
  public $unaccented;
  public $reverse;
  public $description;
  public $modelType;
  public $modelNumber;
  public $restriction;
  public $parseInfo = '';
  public $comment = '';
  public $isLoc = FALSE;
  public $noAccent = FALSE;
  public $createDate;
  public $modDate;

  public function getExtendedName() {
    if ($this->description) {
      return $this->unaccented . ' (' . $this->description . ')';
    } else {
      return $this->unaccented;
    }
  }

  public static function create($form, $modelType, $modelNumber,
                                $restriction) {
    $l = new Lexem();
    $l->form = $form;
    $l->unaccented = str_replace("'", '', $l->form);
    $l->reverse = text_reverse($l->unaccented);
    $l->modelType = $modelType;
    $l->modelNumber = $modelNumber;
    $l->restriction = $restriction;
    return $l;
  }

  public static function load($id) {
    $dbRow = db_getLexemById($id);
    return Lexem::createFromDbRow($dbRow);
  }

  public static function loadByDefinitionId($definitionId) {
    $dbResult = db_getLexemsByDefinitionId($definitionId);
    return Lexem::populateFromDbResult($dbResult);
  }

  public static function loadByUnaccented($unaccented) {
    $dbResult = db_getLexemsByUnaccented($unaccented);
    return Lexem::populateFromDbResult($dbResult);
  }

  public static function loadByForm($form) {
    $dbResult = db_getLexemsByForm($form);
    return Lexem::populateFromDbResult($dbResult);
  }

  public static function loadByPartialUnaccented($name) {
    $dbResult = db_getLexemsByPartialUnaccented($name);
    return Lexem::populateFromDbResult($dbResult);
  }

  public static function loadByUnaccentedPartialDescription($name,
                                                            $description) {
    $dbResult = db_getLexemsByUnaccentedPartialDescription($name,
                                                           $description);
    return Lexem::populateFromDbResult($dbResult);
  }

  public static function loadByUnaccentedDescription($unaccented,
                                                     $description) {
    $dbResult = db_getLexemsByUnaccentedDescription($unaccented, $description);
    return Lexem::populateFromDbResult($dbResult);
  }

  public static function loadByExtendedName($extName) {
    $parts = split('\(', $extName, 2);
    $name = trim($parts[0]);
    if (count($parts) == 2) {
      $description = trim($parts[1]);
      $description = str_replace(')', '', $description);
    } else {
      $description = '';
    }
    return Lexem::loadByUnaccentedDescription($name, $description);
  }

  public function loadHomonyms() {
    $dbResult = db_getLexemHomonyms($this);
    return Lexem::populateFromDbResult($dbResult);
  }

  /**
   * Load all lexems having the same form as one of the given lexems, but exclude the given lexems.
   **/
  public function loadSetHomonyms($lexems) {
    if (count($lexems) == 0) {
      return array();
    }
    $names = array();
    $ids = array();
    foreach ($lexems as $l) {
      $names[] = "'{$l->unaccented}'";
      $ids[] = "'{$l->id}'";
    }
    // Write the query right here -- we're converting it to ADOdb soon anyway.
    $query = sprintf("select * from lexems where lexem_neaccentuat in (%s) and lexem_id not in (%s)", join(',', $names), join(',', $ids));
    $dbResult = logged_query($query);
    return Lexem::populateFromDbResult($dbResult);    
  }

  public function loadSuggestions($limit) {
    $query = $this->reverse;
    $lo = 0;
    $hi = mb_strlen($query);
    $result = array();

    while ($lo <= $hi) {
      $mid = (int)(($lo + $hi) / 2);
      $partial = mb_substr($query, 0, $mid);
      $lexems = Lexem::loadByReverseSuffix($partial, $this->id, $limit);
      if (count($lexems)) {
        $result = $lexems;
        $lo = $mid + 1;
      } else {
        $hi = $mid - 1;
      }
    }
    return $result;
  }

  public static function loadByReverseSuffix($suffix, $excludeLexemId,
					     $limit) {
    $dbResult = db_getLexemsByReverseSuffix($suffix, $excludeLexemId, $limit);
    return Lexem::populateFromDbResult($dbResult);
  }

  public static function loadTemporaryBySuffix($reverseSuffix) {
    $dbResult = db_selectTemporaryLexemsBySuffix($reverseSuffix);
    return Lexem::populateFromDbResult($dbResult);
  }

  public static function loadByModel($modelType, $modelNumber) {
    $dbResult = db_getLexemsByModel($modelType, $modelNumber);
    return Lexem::populateFromDbResult($dbResult);    
  }

  // For V1, this loads all lexems in (V1, VT1)
  public static function loadByCanonicalModel($modelType, $modelNumber) {
    $dbResult = db_getLexemsByCanonicalModel($modelType, $modelNumber);
    return Lexem::populateFromDbResult($dbResult);    
  }

  public static function loadByCanonicalModelSuffix($modelType,
                                                    $modelNumber, $suffix) {
    $dbRow = db_getLexemByCanonicalModelSuffix($modelType, $modelNumber,
                                               $suffix);
    return Lexem::createFromDbRow($dbRow);
  }

  public static function loadByUnaccentedCanonicalModel($unaccented,
                                                        $modelType,
                                                        $modelNumber) {
    $dbRow = db_getLexemByUnaccentedCanonicalModel($unaccented, $modelType,
                                                   $modelNumber);
    return Lexem::createFromDbRow($dbRow);
  }

  /**
   * For update.php
   */
  public static function loadNamesByMinModDate($modDate) {
    return db_getLexemsByMinModDate($modDate);
  }

  public static function createFromDbRow($dbRow) {
    if (!$dbRow) {
      return NULL;
    }
    $l = new Lexem();
    $l->id = $dbRow['lexem_id']; 
    $l->form = $dbRow['lexem_forma']; 
    $l->unaccented = $dbRow['lexem_neaccentuat']; 
    $l->reverse = $dbRow['lexem_invers']; 
    $l->description = $dbRow['lexem_descr']; 
    $l->modelType = $dbRow['lexem_model_type']; 
    $l->modelNumber = $dbRow['lexem_model_no']; 
    $l->restriction = $dbRow['lexem_restriction'];
    $l->parseInfo = $dbRow['lexem_parse_info'];
    $l->comment = $dbRow['lexem_comment'];
    $l->isLoc = $dbRow['lexem_is_loc'];
    $l->noAccent = $dbRow['lexem_no_accent'];
    $l->createDate = $dbRow['CreateDate'];
    $l->modDate = $dbRow['ModDate'];
    return $l;
  }

  public static function populateFromDbResult($dbResult) {
    $result = array();
    while ($dbRow = mysql_fetch_assoc($dbResult)) {
      $result[] = Lexem::createFromDbRow($dbRow);
    }
    mysql_free_result($dbResult);
    return $result;
  }

  public function validate() {
    if (!$this->form) {
      return 'Forma nu poate fi vidă.';
    }
    $numAccents = mb_substr_count($this->form, "'");
    // Note: we allow multiple accents for lexems like hárcea-párcea
    if ($numAccents && $this->noAccent) {
      return 'Ați indicat că lexemul nu necesită accent, dar forma ' .
        'conține un accent.';
    } else if (!$numAccents && !$this->noAccent) {
      return 'Adăugați un accent sau bifați câmpul "Nu necesită accent".';
    }
    return null;
  }

  // Returns an error message or NULL if there are no errors.
  public static function validateRestriction($modelType, $restriction) {
    $hasS = false;
    $hasP = false;
    for ($i = 0; $i < mb_strlen($restriction); $i++) {
      $char = text_getCharAt($restriction, $i);
      if ($char == 'T' || $char == 'U' || $char == 'I') {
        if ($modelType != 'V' && $modelType != 'VT') {
          return "Restricția <b>$char</b> se aplică numai verbelor";
        }
      } else if ($char == 'S') {
        $hasS = true;
      } else if ($char == 'P') {
        $hasP = true;
      } else {
        return "Restricția <b>$char</b> este incorectă.";
      }
    }

    if ($hasS && $hasP) {
      return "Restricțiile <b>S</b> și <b>P</b> nu pot coexista.";
    }
    return NULL;
  }

  public static function searchLexems($cuv, $hasDiacritics) {
    $dbResult = db_searchLexems($cuv, $hasDiacritics);
    return Lexem::populateFromDbResult($dbResult);
  }

  public static function searchInflectedForms($cuv, $hasDiacritics) {
    $dbResult = db_searchInflectedForms($cuv, $hasDiacritics);
    return Lexem::populateFromDbResult($dbResult);
  }

  public static function searchApproximate($cuv, $hasDiacritics) {
    $dbResult = db_searchApproximate($cuv, $hasDiacritics);
    return Lexem::populateFromDbResult($dbResult);
  }

  public static function searchRegexp($regexp, $hasDiacritics, $sourceId = NULL) {
    $mysqlRegexp = text_dexRegexpToMysqlRegexp($regexp);
    $dbResult = db_searchRegexp($mysqlRegexp, $hasDiacritics, $sourceId);
    return Lexem::populateFromDbResult($dbResult);
  }

  public static function countRegexpMatches($regexp, $hasDiacritics, $sourceId = NULL) {
    $mysqlRegexp = text_dexRegexpToMysqlRegexp($regexp);
    return db_countRegexpMatches($mysqlRegexp, $hasDiacritics, $sourceId);
  }

  public static function countAll() {
    return db_countLexems();
  }

  public static function countAssociated() {
    return db_countAssociatedLexems();
  }

  public static function countUnassociated() {
    return Lexem::countAll() - Lexem::countAssociated();
  }

  public static function loadUnassociated() {
    $dbResult = db_getUnassociatedLexems();
    return Lexem::populateFromDbResult($dbResult);
  }

  public static function countTemporary() {
    return db_countTemporaryLexems();
  }

  public static function loadTemporary() {
    $dbResult = db_getTemporaryLexems();
    return Lexem::populateFromDbResult($dbResult);
  }

  public static function loadTemporaryFromSource($sourceId) {
    $dbResult = db_getTemporaryLexemsFromSource($sourceId);
    return Lexem::populateFromDbResult($dbResult);
  }

  public static function countHavingComments() {
    return db_countLexemsWithComments();
  }

  public static function loadHavingComments() {
    $dbResult = db_getLexemsWithComments();
    return Lexem::populateFromDbResult($dbResult);
  }

  public static function countWithoutAccents() {
    return db_countLexemsWithoutAccents();
  }

  public static function loadWithoutAccents() {
    $dbResult = db_getLexemsWithoutAccents();
    return Lexem::populateFromDbResult($dbResult);
  }

  public static function countAmbiguous() {
    return db_countAmbiguousLexems();
  }

  public static function loadAmbiguous() {
    $dbResult = db_getAmbiguousLexems();
    return Lexem::populateFromDbResult($dbResult);
  }

  public static function loadRandomWithoutAccents($count) {
    $dbResult = db_getRandomLexemsWithoutAccents($count);
    return Lexem::populateFromDbResult($dbResult);    
  }

  // Assumes that $participleNumber is the correct participle (adjective)
  // model for $modelNumber.
  public static function loadParticiplesForVerbModel($modelNumber, $participleNumber) {
    $infl = Inflection::loadParticiple();
    $dbResult = db_getParticiplesForVerbModel($modelNumber, $participleNumber, $infl->id);
    return Lexem::populateFromDbResult($dbResult);    
  }

  public function regenerateParadigm() {
    $ifs = $this->generateParadigm();
    assert(is_array($ifs));

    InflectedForm::deleteByLexemId($this->id);
    foreach($ifs as $if) {
      $if->save();
    }

    if ($this->modelType == 'VT') {
      $model = Model::loadCanonicalByTypeNumber($this->modelType,
						$this->modelNumber);
      $pm = ParticipleModel::loadByVerbModel($model->number);
      $this->regeneratePastParticiple($pm->adjectiveModel);
    }
    if ($this->modelType == 'V' || $this->modelType == 'VT') {
      $this->regenerateLongInfinitive();
    }
  }

  public function regeneratePastParticiple($adjectiveModel) {
    $infl = Inflection::loadParticiple();
    $ifs = db_find(new InflectedForm(), "lexemId = {$this->id} and inflectionId = {$infl->id}");
    $model = Model::loadByTypeNumber('A', $adjectiveModel);
    
    foreach ($ifs as $if) {
      // Load an existing lexem only if it has the same model as $model or
      // $temporaryModel. Otherwise create a new lexem.
      $lexems = Lexem::loadByUnaccented($if->formNoAccent);
      $lexem = null;
      foreach ($lexems as $l) {
        if ($l->modelType == 'T' || ($l->modelType == 'A' && $l->modelNumber = $model->number)) {
          $lexem = $l;
        }
      }
      if ($lexem) {
        $lexem->modelType = 'A';
        $lexem->modelNumber = $model->number;
        $lexem->restriction = '';
        $lexem->isLoc = $this->isLoc;
        $lexem->noAccent = false;
        $lexem->save();
      } else {
        $lexem = Lexem::create($if->form, 'A', $model->number, '');
        $lexem->isLoc = $this->isLoc;
        $lexem->save();
        $lexem->id = db_getLastInsertedId();

        // Also associate the new lexem with the same definitions as $this.
        $ldms = LexemDefinitionMap::loadByLexemId($this->id);
        foreach ($ldms as $ldm) {
          LexemDefinitionMap::associate($lexem->id, $ldm->definitionId);
        }
      }
      $lexem->regenerateParadigm();
    }
  }

  public function regenerateLongInfinitive() {
    $infl = Inflection::loadLongInfinitive();
    $ifs = db_find(new InflectedForm(), "lexemId = {$this->id} and inflectionId = {$infl->id}");
    $f107 = Model::loadByTypeNumber('F', 107);
    $f113 = Model::loadByTypeNumber('F', 113);
    
    foreach ($ifs as $if) {
      $model = text_endsWith($if->formNoAccent, 'are') ? $f113 : $f107;
      
      // Load an existing lexem only if it has one of the models F113, F107
      // or T1. Otherwise create a new lexem.
      $lexems = Lexem::loadByUnaccented($if->formNoAccent);
      $lexem = null;
      foreach ($lexems as $l) {
        if ($l->modelType == 'T' ||
            ($l->modelType == 'F' && $l->modelNumber == $model->number)) {
          $lexem = $l;
        }
      }
      if ($lexem) {
        $lexem->modelType = 'F';
        $lexem->modelNumber = $model->number;
        $lexem->restriction = '';
        $lexem->isLoc = $this->isLoc;
        $lexem->noAccent = false;
        $lexem->save();
      } else {
        $lexem = Lexem::create($if->form, 'F', $model->number, '');
        $lexem->isLoc = $this->isLoc;
        $lexem->save();
        $lexem->id = db_getLastInsertedId();

        // Also associate the new lexem with the same definitions as $this.
        $ldms = LexemDefinitionMap::loadByLexemId($this->id);
        foreach ($ldms as $ldm) {
          LexemDefinitionMap::associate($lexem->id, $ldm->definitionId);
        }
      }
      $lexem->regenerateParadigm();
    }
  }

  public function generateInflectedFormWithModel($inflId, $modelId) {
    if (!ConstraintMap::validInflection($inflId, $this->restriction)) {
      return array();
    }
    $ifs = array();
    // These will be sorted by variant and order
    $mds = ModelDescription::loadByModelIdInflectionId($modelId, $inflId);
 
    $start = 0;
    while ($start < count($mds)) {
      // Identify all the md's that differ only by the order
      $end = $start + 1;
      while ($end < count($mds) && $mds[$end]->order != 0) {
        $end++;
      }
      
      $inflId = $mds[$start]->inflectionId;
      $accentShift = $mds[$start]->accentShift;
      $vowel = $mds[$start]->accentedVowel;
      
      // Apply all the transforms from $start to $end - 1.
      $variant = $mds[$start]->variant;
      
      // Load the transforms
      $transforms = array();
      for ($i = $end - 1; $i >= $start; $i--) {
        $transforms[] = Transform::get("id = " . $mds[$i]->transformId);
      }
      
      $result = text_applyTransforms($this->form, $transforms,
                                     $accentShift, $vowel);
      if (!$result) {
        return null;
      }
      $ifs[] = new InflectedForm($result, $this->id, $inflId, $variant);
      $start = $end;
    }
    
    return $ifs;
  }
  
  public function generateParadigm() {
    $model = Model::loadCanonicalByTypeNumber($this->modelType, $this->modelNumber);
    // Select inflection IDs for this model
    $dbResult = db_execute("select distinct md_infl from model_description where md_model = {$model->id} order by md_infl");
    $inflIds = db_getScalarArray($dbResult);
    $ifs = array();
    foreach ($inflIds as $inflId) {
      $if = $this->generateInflectedFormWithModel($inflId, $model->id);
      if ($if === null) {
        return $inflId;
      }
      $ifs = array_merge($ifs, $if);
    }
    return $ifs;
  }

  /**
   * Called when the model type of a lexem changes from VT to something else.
   * Only deletes participles that do not have their own definitions.
   */
  public function deleteParticiple($oldModelNumber) {
    $infl = Inflection::loadParticiple();
    $pm = ParticipleModel::loadByVerbModel($oldModelNumber);
    $this->_deleteDependentModels($infl->id, 'A', array($pm->adjectiveModel));
  }

  /**
   * Called when the model type of a lexem changes from V/VT to something else.
   * Only deletes long infinitives that do not have their own definitions.
   */
  public function deleteLongInfinitive() {
    $infl = Inflection::loadLongInfinitive();
    $this->_deleteDependentModels($infl->id, 'F', array('107', '113'));
  }

  /**
   * Delete lexems that do not have their own definitions.
   * Arguments for participles: 'A', $adjectiveModel.
   * Arguments for long infinitives: 'F', ('107', '113').
   */
  private function _deleteDependentModels($inflId, $modelType, $modelNumbers) {
    $ifs = db_find(new InflectedForm(), "lexemId = {$this->id} and inflectionId = {$inflId}");
    $ldms = LexemDefinitionMap::loadByLexemId($this->id);

    $defHash = array();
    foreach($ldms as $ldm) {
      $defHash[$ldm->definitionId] = true;
    }
    
    foreach ($ifs as $if) {
      // Delete lexems of model T1 or A{$pm}
      $lexems = Lexem::loadByUnaccented($if->formNoAccent);
      foreach ($lexems as $l) {
        if ($l->modelType == 'T' ||
            ($l->modelType == $modelType &&
             in_array($l->modelNumber, $modelNumbers))) {
          $ownDefinitions = false;
          $ldms = LexemDefinitionMap::loadByLexemId($l->id);
          foreach ($ldms as $ldm) {
            if (!array_key_exists($ldm->definitionId, $defHash)) {
              $ownDefinitions = true;
            }
          }

          if (!$ownDefinitions) {
            $l->delete();
          }
        }
      }
    }
  }

  public function cloneLexem() {
    $clone = Lexem::create($this->form, 'T', 1, '');
    $clone->parseInfo = $this->parseInfo;
    $clone->comment = $this->comment;
    $clone->description = ($this->description)
      ? "CLONĂ " . $this->description
      : "CLONĂ";
    $clone->noAccent = $this->noAccent;
    $clone->save();
    $clone->id = db_getLastInsertedId();
    
    // Clone the definition list
    $ldms = LexemDefinitionMap::loadByLexemId($this->id);
    foreach ($ldms as $ldm) {
      LexemDefinitionMap::associate($clone->id, $ldm->definitionId);
    }

    $clone->regenerateParadigm();
    return $clone;
  }

  public function save() {
    $this->modDate = time();
    if ($this->id) {
      db_updateLexem($this);
    } else {
      $this->createDate = time();
      db_insertLexem($this);
    }
  }

  public function updateModDate() {
    return db_updateLexemModDate($this->id, time());
  }

  public function delete() {
    if ($this->id) {
      LexemDefinitionMap::deleteByLexemId($this->id);
      InflectedForm::deleteByLexemId($this->id);
      if ($this->modelType == 'VT') {
        $this->deleteParticiple($this->modelNumber);
      }
      if ($this->modelType == 'VT' || $this->modelType == 'V') {
        $this->deleteLongInfinitive();
      }
      db_deleteLexem($this);
    }
  }
}

class LexemDefinitionMap {
  public $id;
  public $lexemId;
  public $definitionId;

  public static function create($lexemId, $definitionId) {
    $ldm = new LexemDefinitionMap();
    $ldm->lexemId = $lexemId;
    $ldm->definitionId = $definitionId;
    return $ldm;
  }

  public static function load($lexemId, $definitionId) {
    $dbRow = db_getLexemDefinitionMapByLexemIdDefinitionId($lexemId,
                                                           $definitionId);
    if ($dbRow) {
      $result = new LexemDefinitionMap();
      $result->populateFromDbRow($dbRow);
      return $result;
    } else {
      return null;
    }
  }

  public static function loadByLexemId($lexemId) {
    $dbResult = db_getLexemDefinitionMapsByLexemId($lexemId);
    return LexemDefinitionMap::populateFromDbResult($dbResult);
  }

  public static function loadByDefinitionId($definitionId) {
    $dbResult = db_getLexemDefinitionMapsByDefinitionId($definitionId);
    return LexemDefinitionMap::populateFromDbResult($dbResult);
  }

  public function populateFromDbRow($dbRow) {
    $this->id = $dbRow['Id']; 
    $this->lexemId = $dbRow['LexemId'];
    $this->definitionId = $dbRow['DefinitionId'];
  }

  public static function populateFromDbResult($dbResult) {
    $result = array();
    while ($dbRow = mysql_fetch_assoc($dbResult)) {
      $ldm = new LexemDefinitionMap();
      $ldm->populateFromDbRow($dbRow);
      $result[] = $ldm;
    }
    mysql_free_result($dbResult);
    return $result;
  }

  public static function associate($lexemId, $definitionId) {
    // The definition and the lexem should exist
    $definition = Definition::get("id = {$definitionId}");
    $lexem = Lexem::load($lexemId);
    if (!$definition || !$lexem) {
      return;
    }

    // The association itself should not exist.
    $ldm = LexemDefinitionMap::load($lexemId, $definitionId);
    if (!$ldm) {
      $ldm = LexemDefinitionMap::create($lexemId, $definitionId);
      $ldm->save();
    }
  }

  public static function dissociate($lexemId, $definitionId) {
    db_deleteLexemDefinitionMapByLexemIdDefinitionId($lexemId, $definitionId);
    Definition::updateModDate($definitionId);
  }

  public function save() {
    if ($this->id) {
      db_updateLexemDefinitionMap($this);
    } else {
      db_insertLexemDefinitionMap($this);
    }
    Definition::updateModDate($this->definitionId);
  }  

  public static function deleteByDefinitionId($definitionId) {
    db_deleteLexemDefinitionMapsByDefinitionId($definitionId);
  }

  public static function deleteByLexemId($lexemId) {
    $ldms = LexemDefinitionMap::loadByLexemId($lexemId);
    foreach ($ldms as $ldm) {
      Definition::updateModDate($ldm->definitionId);
    }
    db_deleteLexemDefinitionMapsByLexemId($lexemId);
  }
}

class Transform extends BaseObject {
  function __construct($from = null, $to = null) {
    parent::__construct();
    $this->transfFrom = $from;
    $this->transfTo = $to;
  }

  public static function get($where) {
    $obj = new Transform();
    $obj->load($where);
    return $obj->id ? $obj : null;
  }

  public static function createOrLoad($from, $to) {
    $t = self::get("transfFrom = '{$from}' and transfTo = '{$to}'");
    if (!$t) {
      $t = new Transform($from, $to);
      $t->save();
    }
    return $t;
  }

  public function __toString() {
    $from = $this->transfFrom ? $this->transfFrom : 'nil';
    $to = $this->transfTo ? $this->transfTo : 'nil';
    return "($from=>$to)";
  }
}

class InflectedForm extends BaseObject {
  function __construct($form = null, $lexemId = null, $inflectionId = null, $variant = null) {
    parent::__construct();
    $this->form = $form;
    $this->formNoAccent = str_replace("'", '', $form);
    $this->formUtf8General = $this->formNoAccent;
    $this->lexemId = $lexemId;
    $this->inflectionId = $inflectionId;
    $this->variant = $variant;
  }

  public static function loadByLexemId($lexemId) {
    return db_find(new InflectedForm(), "lexemId = {$lexemId} order by inflectionId, variant");
  }

  public static function loadByLexemIdMapByInflectionId($lexemId) {
    return self::mapByInflectionId(self::loadByLexemId($lexemId));
  }

  public static function mapByInflectionId($ifs) {
    $result = array();
    foreach ($ifs as $if) {
      if (array_key_exists($if->inflectionId, $result)) {
        // The inflected forms are already sorted by variant
        $result[$if->inflectionId][] = $if;
      } else {
        $result[$if->inflectionId] = array($if);
      }
    }
    return $result;
  }

  public static function deleteByLexemId($lexemId) {
    $ifs = db_find(new InflectedForm(), "lexemId = {$lexemId}");
    foreach ($ifs as $if) {
      $if->delete();
    }
  }
}

class ParticipleModel extends BaseObject {
  public static function get($where) {
    $obj = new ParticipleModel();
    $obj->load($where);
    return $obj->id ? $obj : null;
  }

  public static function loadByVerbModel($verbModel) {
    $verbModel = addslashes($verbModel);
    return self::get("verbModel = '{$verbModel}'");
  }
}

class ConstraintMap extends BaseObject {

  /**
   * Given a restriction like 'PT', and an inflection, returns true iff the inflection ID is valid under all the restrictions.
   */
  public static function validInflection($inflId, $restr) {
    if (!$restr) {
      return true;
    }
    $numAllowed = db_getSingleValue("select count(*) from ConstraintMap where locate(code, '$restr') > 0 and inflectionId = $inflId");
    return ($numAllowed == mb_strlen($restr));
  }
}

class LocVersion {
  public $name;
  public $freezeTimestamp;

  public function getDate() {
    if (!$this->freezeTimestamp) {
      return 'curentă';
    } else {
      return date('m/d/Y', $this->freezeTimestamp);
    }
  }

  public function getDbName() {
    return str_replace('.', '_', $this->name);
  }
}

class FullTextIndex extends BaseObject {
  // Takes a comma-separated string of lexem ids
  public static function loadDefinitionIdsForLexems($lexemIds) {
    if (!$lexemIds) {
      return array();
    }
    return db_getScalarArray(db_execute("select distinct DefinitionId from FullTextIndex where LexemId in ($lexemIds) order by DefinitionId"));
  }

  public static function loadPositionsByLexemIdsDefinitionId($lexemIds, $defId) {
    return db_getScalarArray(db_execute("select distinct Position from FullTextIndex where LexemId in ($lexemIds) and DefinitionId = $defId order by Position"));
  }
}


class Variable extends BaseObject {
  public static function peek($name, $default = null) {
    $v = new Variable();
    $v->load("name = '$name'");
    return $v->name ? $v->value : $default;
  }

  public static function poke($name, $value) {
    $v = new Variable();
    $v->load("name = '$name'");
    if (!$v->name) {
      $v->name = $name;
    }
    $v->value = $value;
    $v->save();
  }
}

?>
