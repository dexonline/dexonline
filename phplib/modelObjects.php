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
    $this->correct = text_internalizeDefinition($this->correct, 0);
    $this->wrong = text_internalizeDefinition($this->wrong, 0);
    $this->comments = text_internalizeDefinition($this->comments, 0);
    
    $this->correctHtml = text_htmlizeWithNewlines($this->correct, 0, TRUE);
    $this->wrongHtml = text_htmlizeWithNewlines($this->wrong, 0, TRUE);
    $this->commentsHtml = text_htmlizeWithNewlines($this->comments, 0, TRUE);    
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
    $dbResult = db_execute(sprintf("select distinct D.* from Definition D, LexemDefinitionMap L, Source S " .
                                   "where D.id = L.definitionId and L.lexemId in (%s) and D.sourceId = S.id and D.status = 0 %s %s " .
                                   "order by (D.lexicon = '%s') desc, S.isOfficial desc, D.lexicon, S.displayOrder",
                                   $lexemIds, $excludeClause, $sourceClause, $preferredWord));
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
    $regexp = text_dexRegexpToMysqlRegexp($cuv);
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

  private static function getSqlStatement($manual) {
    $bulk = array(array(null, Source::get("shortName = 'MDN'"), '2007-09-15'),
                  array(null, Source::get("shortName = 'Petro-Sedim'"), null),
                  array(null, Source::get("shortName = 'GTA'"), null),
                  array(User::get("nick = 'siveco'"), null, null),
                  array(User::get("nick = 'RACAI'"), null, null));
    $conditions = array();
    foreach ($bulk as $tuple) {
      $parts = array();
      if ($tuple[0]) {
        $parts[] = "(userId = {$tuple[0]->id})";
      }
      if ($tuple[1]) {
        $parts[] = "(sourceId = {$tuple[1]->id})";
      }
      if ($tuple[2]) {
        $parts[] = "(left(from_unixtime(createDate), 10) = '{$tuple[2]}')";
      }
      $conditions[] = '(' . implode(' and ', $parts) . ')';
    }
    $clause = '(' . implode(' or ', $conditions) . ')';
    if ($manual) {
      $clause = "not {$clause}";
    }

    return "select nick, count(*) as NumDefinitions, sum(length(internalRep)) as NumChars, max(createDate) as Timestamp from Definition, User where userId = User.id and status = 0 and $clause group by nick";
  }

  private static function loadUnsortedTopData($manual) {
    $statement = self::getSqlStatement($manual);
    $dbResult = db_execute($statement);
    $topEntries = array();
    $now = time();

    while (!$dbResult->EOF) {
      $topEntry = new TopEntry();
      $topEntry->userNick = $dbResult->fields['nick'];
      $topEntry->numDefinitions = $dbResult->fields['NumDefinitions'];
      $topEntry->numChars = $dbResult->fields['NumChars'];
      $topEntry->timestamp = $dbResult->fields['Timestamp'];
      $topEntry->days = intval(($now - $topEntry->timestamp) / 86400);
      $topEntries[] = $topEntry;
      $dbResult->MoveNext();
    }

    return $topEntries;
  }

  private static function getUnsortedTopData($manual) {
    $data = fileCache_getTop($manual);
    if (!$data) {
      $data = TopEntry::loadUnsortedTopData($manual);
      fileCache_putTop($data, $manual);
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
  public static function getTopData($crit, $ord, $manual) {
    $topEntries = TopEntry::getUnsortedTopData($manual);
    
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

class ModelType extends BaseObject {
  public static function loadCanonical() {
    return db_find(new ModelType(), 'code = canonical and code != "T" order by code');
  }

  public static function canonicalize($code) {
    if ($code == 'VT') {
      return 'V';
    } else if ($code == 'MF') {
      return 'A';
    } else {
      return $code;
    }
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
    $dbResult = db_execute("select distinct Inflection.* from Model, ModelDescription, Inflection " .
                           "where modelId = Model.id and inflectionId = Inflection.id and modelType = '$modelType' order by inflectionId");
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

class Model extends BaseObject {
  function __construct($modelType = '', $number = '', $description = '', $exponent = '') {
    parent::__construct();
    $this->modelType = $modelType;
    $this->number = $number;
    $this->description = $description;
    $this->exponent = $exponent;
    $this->flag = 0;
  }

  public static function get($where) {
    $obj = new Model();
    $obj->load($where);
    return $obj->id ? $obj : null;
  }

  public static function loadByType($type) {
    $type = ModelType::canonicalize($type);
    return db_find(new Model(), "modelType = '{$type}' order by cast(number as unsigned)");
  }

  public static function loadCanonicalByTypeNumber($type, $number) {
    $type = ModelType::canonicalize($type);
    return Model::get("modelType = '{$type}' and number = '{$number}'");
  }

  public function delete() {
    db_execute("delete from ModelDescription where modelId = '{$this->id}'");
    if ($this->modelType == 'V') {
      $pm = ParticipleModel::loadByVerbModel($this->number);
      $pm->delete();
    }
    parent::delete();
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

  public function __toString() {
    return $this->modelType . $this->number;
  }
}

class ModelDescription extends BaseObject {
  function __construct($other = null) {
    parent::__construct();
    if ($other instanceof ModelDescription) {
      $this->modelId = $other->modelId;
      $this->inflectionId = $other->inflectionId;
      $this->variant = $other->variant;
      $this->applOrder = $other->applOrder;
      $this->transformId = $other->transformId;
      $this->accentShift = $other->accentShift;
      $this->vowel = $other->vowel;
    }
  }
}

class Lexem extends BaseObject {
  function __construct($form = null, $modelType = null, $modelNumber = null, $restriction = '') {
    parent::__construct();
    if ($form) {
      $this->form = $form;
      $this->formNoAccent = str_replace("'", '', $form);
      $this->formUtf8General = $this->formNoAccent;
      $this->reverse = text_reverse($this->formNoAccent);
    }
    $this->description = '';
    $this->tags = '';
    $this->source = '';
    $this->modelType = $modelType;
    $this->modelNumber = $modelNumber;
    $this->restriction = $restriction;
    $this->comment = '';
    $this->isLoc = false;
    $this->noAccent = false;
  }

  public static function get($where) {
    $obj = new Lexem();
    $obj->load($where);
    return $obj->id ? $obj : null;
  }

  public static function loadByExtendedName($extName) {
    $parts = preg_split('/\(/', $extName, 2);
    $name = trim($parts[0]);
    if (count($parts) == 2) {
      $description = trim($parts[1]);
      $description = str_replace(')', '', $description);
    } else {
      $description = '';
    }
    return db_find(new Lexem(), "formNoAccent = '{$name}' and description = '{$description}'");
  }

  // For V1, this loads all lexems in (V1, VT1)
  public static function loadByCanonicalModel($modelType, $modelNumber) {
    $dbResult = db_execute("select Lexem.* from Lexem, ModelType where modelType = code and canonical = '{$modelType}' and modelNumber = '{$modelNumber}' " .
                           "order by formNoAccent");
    return db_getObjects(new Lexem(), $dbResult);
  }

  /**
   * For update.php
   */
  public static function loadNamesByMinModDate($modDate) {
    return db_execute("select D.id, formNoAccent from Definition D force index(modDate), LexemDefinitionMap M, Lexem L " .
                      "where D.id = definitionId and lexemId = L.id and status = 0 and D.modDate >= {$modDate} " .
                      "and sourceId in (select id from Source where canDistribute) order by D.modDate, D.id");
  }

  public static function searchInflectedForms($cuv, $hasDiacritics) {
    $field = $hasDiacritics ? 'formNoAccent' : 'formUtf8General';
    $dbResult = db_execute("select distinct L.* from InflectedForm I, Lexem L where I.lexemId = L.id and I.$field = '$cuv' order by L.formNoAccent");
    return db_getObjects(new Lexem(), $dbResult);
  }

  public static function searchApproximate($cuv, $hasDiacritics) {
    $field = $hasDiacritics ? 'formNoAccent' : 'formUtf8General';
    return db_find(new Lexem(), "dist2($field, '$cuv') order by formNoAccent");
  }

  public static function searchRegexp($regexp, $hasDiacritics, $sourceId) {
    $mysqlRegexp = text_dexRegexpToMysqlRegexp($regexp);
    $field = $hasDiacritics ? 'formNoAccent' : 'formUtf8General';
    if (!$sourceId) {
      return db_find(new Lexem(), "$field $mysqlRegexp order by formNoAccent limit 1000");
    }
    $dbResult = db_execute("select distinct L.* from Lexem L join LexemDefinitionMap on L.id = lexemId join Definition D on definitionId = D.id " .
                           "where $field $mysqlRegexp and sourceId = $sourceId order by formNoAccent limit 1000");
    return db_getObjects(new Lexem(), $dbResult);
  }

  public static function countRegexpMatches($regexp, $hasDiacritics, $sourceId) {
    $mysqlRegexp = text_dexRegexpToMysqlRegexp($regexp);
    $field = $hasDiacritics ? 'formNoAccent' : 'formUtf8General';
    if (!$sourceId) {
      return db_getSingleValue("select count(*) from Lexem where $field $mysqlRegexp");
    }
    return db_getSingleValue("select count(distinct L.id) from Lexem L join LexemDefinitionMap on L.id = lexemId join Definition D on definitionId = D.id " .
                             "where $field $mysqlRegexp and sourceId = $sourceId order by formNoAccent");
  }

  public static function loadUnassociated() {
    return db_find(new Lexem(), "id not in (select lexemId from LexemDefinitionMap) order by formNoAccent");
  }

  public function regenerateParadigm() {
    $ifs = $this->generateParadigm();
    assert(is_array($ifs));

    InflectedForm::deleteByLexemId($this->id);
    foreach($ifs as $if) {
      $if->save();
    }

    if ($this->modelType == 'VT') {
      $model = Model::loadCanonicalByTypeNumber($this->modelType, $this->modelNumber);
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
    $model = Model::get("modelType = 'A' and number = '{$adjectiveModel}'");

    foreach ($ifs as $if) {
      // Load an existing lexem only if it has the same model as $model or T1. Otherwise create a new lexem.
      $lexems = db_find(new Lexem(), "formNoAccent = '{$if->formNoAccent}'");
      $lexem = null;
      foreach ($lexems as $l) {
        if ($l->modelType == 'T' || ($l->modelType == 'A' && $l->modelNumber = $model->number)) {
          $lexem = $l;
        } else if ($this->isLoc && !$l->isLoc) {
          session_setFlash("Lexemul {$l->formNoAccent} ({$l->modelType}{$l->modelNumber}), care nu este în LOC, nu a fost modificat.", 'info');
        }
      }
      if ($lexem) {
        $lexem->modelType = 'A';
        $lexem->modelNumber = $model->number;
        $lexem->restriction = '';
        if ($this->isLoc && !$lexem->isLoc) {
          $lexem->isLoc = $this->isLoc;
          session_setFlash("Lexemul {$lexem->formNoAccent}, care nu era în LOC, a fost inclus automat în LOC.", 'info');
        }
        $lexem->noAccent = false;
        $lexem->save();
      } else {
        $lexem = new Lexem($if->form, 'A', $model->number, '');
        $lexem->isLoc = $this->isLoc;
        $lexem->save();

        // Also associate the new lexem with the same definitions as $this.
        $ldms = db_find(new LexemDefinitionMap(), "lexemId = {$this->id}");
        foreach ($ldms as $ldm) {
          LexemDefinitionMap::associate($lexem->id, $ldm->definitionId);
        }
        session_setFlash("Am creat automat lexemul {$lexem->formNoAccent} (A{$lexem->modelNumber}) și l-am asociat cu toate definițiile verbului.", 'info');
      }
      $lexem->regenerateParadigm();
    }
  }

  public function regenerateLongInfinitive() {
    $infl = Inflection::loadLongInfinitive();
    $ifs = db_find(new InflectedForm(), "lexemId = {$this->id} and inflectionId = {$infl->id}");
    $f107 = Model::get("modelType = 'F' and number = '107'");
    $f113 = Model::get("modelType = 'F' and number = '113'");
    
    foreach ($ifs as $if) {
      $model = text_endsWith($if->formNoAccent, 'are') ? $f113 : $f107;
      
      // Load an existing lexem only if it has one of the models F113, F107 or T1. Otherwise create a new lexem.
      $lexems = db_find(new Lexem(), "formNoAccent = '{$if->formNoAccent}'");
      $lexem = null;
      foreach ($lexems as $l) {
        if ($l->modelType == 'T' || ($l->modelType == 'F' && $l->modelNumber == $model->number)) {
          $lexem = $l;
        } else if ($this->isLoc && !$l->isLoc) {
          session_setFlash("Lexemul {$l->formNoAccent} ({$l->modelType}{$l->modelNumber}), care nu este în LOC, nu a fost modificat.", 'info');
        }
      }
      if ($lexem) {
        $lexem->modelType = 'F';
        $lexem->modelNumber = $model->number;
        $lexem->restriction = '';
        if ($this->isLoc && !$lexem->isLoc) {
          $lexem->isLoc = $this->isLoc;
          session_setFlash("Lexemul {$lexem->formNoAccent}, care nu era în LOC, a fost inclus automat în LOC.", 'info');
        }
        $lexem->noAccent = false;
        $lexem->save();
      } else {
        $lexem = new Lexem($if->form, 'F', $model->number, '');
        $lexem->isLoc = $this->isLoc;
        $lexem->save();

        // Also associate the new lexem with the same definitions as $this.
        $ldms = db_find(new LexemDefinitionMap(), "lexemId = {$this->id}");
        foreach ($ldms as $ldm) {
          LexemDefinitionMap::associate($lexem->id, $ldm->definitionId);
        }
        session_setFlash("Am creat automat lexemul {$lexem->formNoAccent} (F{$lexem->modelNumber}) și l-am asociat cu toate definițiile verbului.", 'info');
      }
      $lexem->regenerateParadigm();
    }
  }

  public function generateInflectedFormWithModel($inflId, $modelId) {
    if (!ConstraintMap::validInflection($inflId, $this->restriction)) {
      return array();
    }
    $ifs = array();
    $mds = db_find(new ModelDescription(), "modelId = '$modelId' and inflectionId = '$inflId' order by variant, applOrder");
 
    $start = 0;
    while ($start < count($mds)) {
      // Identify all the md's that differ only by the applOrder
      $end = $start + 1;
      while ($end < count($mds) && $mds[$end]->applOrder != 0) {
        $end++;
      }
      
      $inflId = $mds[$start]->inflectionId;
      $accentShift = $mds[$start]->accentShift;
      $vowel = $mds[$start]->vowel;
      
      // Apply all the transforms from $start to $end - 1.
      $variant = $mds[$start]->variant;
      
      // Load the transforms
      $transforms = array();
      for ($i = $end - 1; $i >= $start; $i--) {
        $transforms[] = Transform::get("id = " . $mds[$i]->transformId);
      }
      
      $result = text_applyTransforms($this->form, $transforms, $accentShift, $vowel);
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
    $dbResult = db_execute("select distinct inflectionId from ModelDescription where modelId = {$model->id} order by inflectionId");
    $inflIds = db_getArray($dbResult);
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
   * Arguments for participles: 'A', ($adjectiveModel).
   * Arguments for long infinitives: 'F', ('107', '113').
   */
  private function _deleteDependentModels($inflId, $modelType, $modelNumbers) {
    $ifs = db_find(new InflectedForm(), "lexemId = {$this->id} and inflectionId = {$inflId}");
    $ldms = db_find(new LexemDefinitionMap(), "lexemId = {$this->id}");

    $defHash = array();
    foreach($ldms as $ldm) {
      $defHash[$ldm->definitionId] = true;
    }
    
    foreach ($ifs as $if) {
      $lexems = db_find(new Lexem(), "formNoAccent = '{$if->formNoAccent}'");
      foreach ($lexems as $l) {
        if ($l->modelType == 'T' || ($l->modelType == $modelType && in_array($l->modelNumber, $modelNumbers))) {
          $ownDefinitions = false;
          $ldms = db_find(new LexemDefinitionMap(), "lexemId = {$l->id}");
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

  public function delete() {
    if ($this->id) {
      if ($this->modelType == 'VT') {
        $this->deleteParticiple($this->modelNumber);
      }
      if ($this->modelType == 'VT' || $this->modelType == 'V') {
        $this->deleteLongInfinitive();
      }
      LexemDefinitionMap::deleteByLexemId($this->id);
      InflectedForm::deleteByLexemId($this->id);
    }
    parent::delete();
  }

  public function save() {
    $this->formUtf8General = $this->formNoAccent;
    parent::save();
  }  

  public function __toString() {
    return $this->description ? "{$this->formNoAccent} ({$this->description})" : $this->formNoAccent;
  }
}

class LexemDefinitionMap extends BaseObject {
  function __construct($lexemId = null, $definitionId = null) {
    parent::__construct();
    $this->lexemId = $lexemId;
    $this->definitionId = $definitionId;
  }

  public static function associate($lexemId, $definitionId) {
    // The definition and the lexem should exist
    $definition = Definition::get("id = {$definitionId}");
    $lexem = Lexem::get("id = {$lexemId}");
    if (!$definition || !$lexem) {
      return;
    }

    // The association itself should not exist.
    $ldm = new LexemDefinitionMap();
    $ldm->load("lexemId = {$lexemId} and definitionId = {$definitionId}");
    if (!$ldm->id) {
      $ldm = new LexemDefinitionMap($lexemId, $definitionId);
      $ldm->save();
    }
  }

  public static function dissociate($lexemId, $definitionId) {
    $ldm = new LexemDefinitionMap();
    $ldm->load("lexemId = {$lexemId} and definitionId = {$definitionId}");
    if ($ldm->id) {
      $ldm->delete();
    }
    Definition::updateModDate($definitionId);
  }

  public function save() {
    parent::save();
    Definition::updateModDate($this->definitionId);
  }  

  public static function deleteByLexemId($lexemId) {
    $ldms = db_find(new LexemDefinitionMap(), "lexemId = {$lexemId}");
    foreach ($ldms as $ldm) {
      Definition::updateModDate($ldm->definitionId);
      $ldm->delete();
    }
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

  public function save() {
    $this->formUtf8General = $this->formNoAccent;
    parent::save();
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

  public static function changeDatabase($versionName) {
    $lvs = array_reverse(pref_getLocVersions());
    if ($versionName == $lvs[0]->name) {
      $dbInfo = db_splitDsn();
      $dbName = $dbInfo['database'];
    } else {
      $lv = new LocVersion();
      $lv->name = $versionName;
      $dbName = pref_getLocPrefix() . $lv->getDbName();
    }
    db_changeDatabase($dbName);
  }
}

class FullTextIndex extends BaseObject {
  // Takes a comma-separated string of lexem ids
  public static function loadDefinitionIdsForLexems($lexemIds) {
    if (!$lexemIds) {
      return array();
    }
    return db_getArray(db_execute("select distinct definitionId from FullTextIndex where lexemId in ($lexemIds) order by definitionId"));
  }

  public static function loadPositionsByLexemIdsDefinitionId($lexemIds, $defId) {
    return db_getArray(db_execute("select distinct position from FullTextIndex where lexemId in ($lexemIds) and definitionId = $defId order by position"));
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


class AdsLink extends BaseObject {
	public static function getUrlByKey($skey) {
		$al = new AdsLink();
		$al->load("skey = ", $skey);
		return $al->url;
	}
}


class AdsClick extends BaseObject {
	function __construct($skey, $ip) {
		parent::__construct();
		$this->skey = $skey;
		$this->ip = ip2long($ip);
	}

	public static function addClick($skey, $ip) {
		$ac = new AdsClick($skey, $ip);
		$ac->save();
	}
}

class PasswordToken extends BaseObject {
  public static function get($where) {
    $obj = new PasswordToken();
    $obj->load($where);
    return $obj->id ? $obj : null;
  }
}

?>
