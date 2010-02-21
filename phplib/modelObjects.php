<?

class GuideEntry {
  public $id;
  public $correct;
  public $correctHtml;
  public $wrong;
  public $wrongHtml;
  public $comments;
  public $commentsHtml;
  public $status = ST_ACTIVE;
  public $createDate;
  public $modDate;

  public static function load($id) {
    $result = new GuideEntry();
    $dbRow = db_getGuideEntryById($id);
    $result->populateFromDbRow($dbRow);
    return $result;
  }

  public static function loadAllActive() {
    $dbResult = db_selectAllActiveGuideEntries();
    $guideEntryArray = array();

    while ($dbRow = mysql_fetch_assoc($dbResult)) {
      $guideEntry = new GuideEntry();
      $guideEntry->populateFromDbRow($dbRow);
      $guideEntryArray[] = $guideEntry;
    }

    return $guideEntryArray;
  }

  private function populateFromDbRow($dbRow) {
    $this->id = $dbRow['Id'];
    $this->correct = $dbRow['Correct'];
    $this->correctHtml = $dbRow['CorrectHtml'];
    $this->wrong = $dbRow['Wrong'];
    $this->wrongHtml = $dbRow['WrongHtml'];
    $this->comments = $dbRow['Comments'];
    $this->commentsHtml = $dbRow['CommentsHtml'];
    $this->status = $dbRow['Status'];
    $this->createDate = $dbRow['CreateDate'];
    $this->modDate = $dbRow['ModDate'];
  }

  public function normalizeAndSave() {
    $this->normalize();
    $this->save();
  }

  public function normalize() {
    $this->correct = text_internalizeDefinition($this->correct);
    $this->wrong = text_internalizeDefinition($this->wrong);
    $this->comments = text_internalizeDefinition($this->comments);
    
    $this->correctHtml = text_htmlizeWithNewlines($this->correct, TRUE);
    $this->wrongHtml = text_htmlizeWithNewlines($this->wrong, TRUE);
    $this->commentsHtml = text_htmlizeWithNewlines($this->comments, TRUE);    
  }

  public function save() {
    $this->modDate = time();
    if ($this->id) {
      db_updateGuideEntry($this);
    } else {
      $this->createDate = time();
      db_insertGuideEntry($this);
    }
  }
}


class Source {
  public $id;
  public $shortName;
  public $name;
  public $author;
  public $publisher;
  public $year;
  public $canContribute;
  public $canModerate;
  public $isOfficial;
  public $displayOrder;

  public static function load($id) {
    $dbRow = db_getSourceById($id);
    $result = new Source();
    $result->populateFromDbRow($dbRow);
    return $result;
  }

  public static function loadAllContribSources() {
    $dbResult = db_selectAllContribSources();
    return Source::populateFromDbResult($dbResult);
  }

  public static function loadAllModeratorSources() {
    $dbResult = db_selectAllModeratorSources();
    return Source::populateFromDbResult($dbResult);
  }

  public static function loadAllSources() {
    $dbResult = db_selectAllSources();
    return Source::populateFromDbResult($dbResult);
  }

  public static function loadUnofficial() {
    $dbRow = db_getSourceByShortName('Neoficial');
    $result = new Source();
    $result->populateFromDbRow($dbRow);
    return $result;
  }

  public static function populateFromDbResult($dbResult) {
    $result = array();
    while ($dbRow = mysql_fetch_assoc($dbResult)) {
      $source = new Source();
      $source->populateFromDbRow($dbRow);
      $result[] = $source;
    }
    mysql_free_result($dbResult);
    return $result;
  }

  public function populateFromDbRow($dbRow) {
    $this->id = $dbRow['Id']; 
    $this->shortName = $dbRow['ShortName'];
    $this->name = $dbRow['Name'];
    $this->author = $dbRow['Author'];
    $this->publisher = $dbRow['Publisher'];
    $this->year = $dbRow['Year'];
    $this->canContribute = $dbRow['CanContribute'];
    $this->canModerate = $dbRow['CanModerate'];
    $this->isOfficial = $dbRow['IsOfficial'];
    $this->displayOrder = $dbRow['DisplayOrder'];
  }
}


class Cookie {
  public $id;
  public $cookieString;
  public $userId;
  public $createDate;

  public static function loadByCookieString($cookieString) {
    $result = new Cookie();
    $dbRow = db_getCookieByCookieString($cookieString);
    $result->populateFromDbRow($dbRow);
    return $result;
  }

  public function save() {
    assert(!$this->id);
    $this->createDate = time();    
    db_insertCookie($this);
  }

  public function delete() {
    db_deleteCookie($this);
  }

  public function populateFromDbRow($dbRow) {
    $this->id = $dbRow['Id'];
    $this->cookieString = $dbRow['CookieString'];
    $this->userId = $dbRow['UserId'];
    $this->createDate = $dbRow['CreateDate'];
  }
}


class User {
  public $id;
  public $nick;
  public $name;
  public $email;
  public $emailVisible = 0;
  public $password;
  public $moderator = 0;
  public $prefs = '';

  public static function load($id) {
    $dbRow = db_getUserById($id);
    return User::loadFromDbRow($dbRow);
  }

  public static function loadByNickEmailPassword($nickOrEmail, $password) {
    $dbRow = db_getUserByNickEmailPassword($nickOrEmail, $password);
    return User::loadFromDbRow($dbRow);
  }

  public static function loadByNick($nick) {
    $dbRow = db_getUserByNick($nick);
    return User::loadFromDbRow($dbRow);
  }

  public static function loadByEmail($email) {
    $dbRow = db_getUserByEmail($email);
    return User::loadFromDbRow($dbRow);
  }

  public static function loadByCookieString($cookieString) {
    $dbRow = db_getUserByCookieString($cookieString);
    return User::loadFromDbRow($dbRow);
  }

  private static function loadFromDbRow($dbRow) {
    if (!$dbRow) {
      return null;
    }
    $result = new User();
    $result->populateFromDbRow($dbRow);
    return $result;
  }

  private function populateFromDbRow($dbRow) {
    $this->id = $dbRow['Id'];
    $this->nick = $dbRow['Nick'];
    $this->name = $dbRow['Name'];
    $this->email = $dbRow['Email'];
    $this->emailVisible = $dbRow['EmailVisible'];
    $this->password = $dbRow['Password'];
    $this->moderator = $dbRow['Moderator'];
    $this->prefs = $dbRow['Preferences'];
  }

  public function save() {
    if ($this->id) {
      db_updateUser($this);
    } else {
      db_insertUser($this);
    }
  }

  public function __toString() {
    return $this->nick;
  }
}


class Definition {
  public $id;
  public $userId;
  public $sourceId;
  public $lexicon;
  public $displayed = 0;
  public $internalRep;
  public $htmlRep;
  public $status = ST_PENDING;
  public $createDate;
  public $modDate;

  public static function load($id) {
    return Definition::createFromDbRow(db_getDefinitionById($id));
  }

  public static function loadByIds($ids) {
    if (count($ids) == 0) {
      return array();
    }
    $idString = implode(',', $ids);
    $dbResult = db_getDefinitionsByIds($idString);
    return Definition::populateFromDbResult($dbResult);
  }

  public static function loadByLexemId($lexemId) {
    $dbResult = db_getDefinitionsByLexemId($lexemId);
    return Definition::populateFromDbResult($dbResult);
  }

  public static function loadUnassociated() {
    $dbResult = db_getUnassociatedDefinitions();
    return Definition::populateFromDbResult($dbResult);
  }

  public static function countAll() {
    return db_countDefinitions();
  }

  public static function countAssociated() {
    return db_countAssociatedDefinitions();
  }

  // Counts the unassociated definitions in the active or temporary statuses.
  public static function countUnassociated() {
    return Definition::countAll() - Definition::countAssociated() - 
      Definition::countByStatus(ST_DELETED);
  }

  public static function countByStatus($status) {
    return db_countDefinitionsByStatus($status);
  }

  public static function countActiveSince($minCreateDate) {
    return db_countRecentDefinitions($minCreateDate);
  }

  public static function countHavingTypos() {
    return db_countDefinitionsHavingTypos();
  }

  public static function loadApproximateMatches($word, $collation, $sourceId) {
    $dbResult = db_getApproximateActiveDefinitions($word, $collation,
                                                   $sourceId);
    return Definition::populateFromDbResult($dbResult);
  }

  public static function loadDictResults($word, $sourceId) {
    $dbResult = db_selectDefinitionsForDict($word, $sourceId);
    return Definition::populateFromDbResult($dbResult);
  }

  public static function loadDefinitionsHavingTypos() {
    $dbResult = db_selectDefinitionsHavingTypos();
    return Definition::populateFromDbResult($dbResult);
  }

  // Returns a MySQL result set
  public static function loadByMinModDate($modDate) {
    return db_getDefinitionsByMinModDate($modDate);
  }

  public static function loadForLexems($lexems, $sourceId, $preferredWord, $exclude_unofficial = false) {
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
    $dbResult = db_selectDefinitionsForLexemIds($lexemIds, $sourceId,
                                                $preferredWord, $exclude_unofficial);
    return Definition::populateFromDbResult($dbResult);
  }

  public static function searchDefId($defId) {
    return Definition::createFromDbRow(db_searchDefId($defId));
  }

  public static function searchLexemId($lexemId, $exclude_unofficial = false) {
    $dbResult = db_searchLexemId($lexemId, $exclude_unofficial);
    return Definition::populateFromDbResult($dbResult);
  }

  public static function searchFullText($words, $hasDiacritics) {
    $intersection = null;

    $matchingLexems = array();
    foreach ($words as $word) {
      $lexems = Lexem::searchWordlists($word, $hasDiacritics);
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
        $p[] = FullTextIndex::loadPositionsByLexemIdsDefinitionId($lexemIds,
                                                                  $defId);
      }
      $shortestIntervals[] = util_findSnippet($p);
    }

    if ($intersection) {
      array_multisort($shortestIntervals, $intersection);
    }
    debug_stopClock("Computed score for every definition");

    return $intersection;
  }

  // Only returns the definition ID's
  public static function searchModerator($cuv, $hasDiacritics, $sourceId,
                                         $status, $userId, $beginTime,
                                         $endTime) {
    $regexp = text_dexRegexpToMysqlRegexp($cuv);
    if ($status == ST_DELETED) {
      // Deleted definitions are not associated with any lexem
      $dbResult = db_searchDeleted($regexp, $hasDiacritics, $sourceId, $userId,
                                   $beginTime, $endTime);
    } else {
      $dbResult = db_searchModerator($regexp, $hasDiacritics, $sourceId,
                                     $status, $userId, $beginTime, $endTime);
    }
    return Definition::populateFromDbResult($dbResult);
  }

  // Return definitions that are associateed with at least two of the lexems
  public static function searchMultipleWords($words, $hasDiacritics, $sourceId, $exclude_unofficial) {
    $defCounts = array();
    foreach ($words as $word) {
      $lexems = Lexem::searchWordlists($word, $hasDiacritics);
      if (count($lexems)) {
        $definitions = Definition::loadForLexems($lexems, $sourceId, $word, $exclude_unofficial);
        foreach ($definitions as $def) {
          $defCounts[$def->id] = array_key_exists($def->id, $defCounts) ? $defCounts[$def->id] + 1 : 1;
        }
      }
    }
    arsort($defCounts);

    $result = array();
    foreach ($defCounts as $defId => $cnt) {
      if ($cnt >= 2) {
        $result[] = Definition::load($defId);
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
    // MySQL takes much longer to count the active definitions.
    $all = Definition::countAll();
    $pending = Definition::countByStatus(ST_PENDING);
    $deleted = Definition::countByStatus(ST_DELETED);
    $active = $all - $pending - $deleted;
    fileCache_putWordCount($active);
    return $active;
  }

  public static function getWordCountLastMonth() {
    $cachedWordCountLastMonth = fileCache_getWordCountLastMonth();
    if ($cachedWordCountLastMonth) {
      return $cachedWordCountLastMonth;
    }
    $last_month = time() - 30 * 86400;
    $result = Definition::countActiveSince($last_month);
    fileCache_putWordCountLastMonth($result);
    return $result;
  }

  public static function populateFromDbResult($dbResult) {
    $definitionArray = array();

    while ($dbRow = mysql_fetch_assoc($dbResult)) {
      $definitionArray[] = Definition::createFromDbRow($dbRow);
    }

    mysql_free_result($dbResult);
    return $definitionArray;
  }

  public static function createFromDbRow($dbRow) {
    if (!$dbRow) {
      return null;
    }
    $d = new Definition();
    $d->id = $dbRow['Id'];
    $d->userId = $dbRow['UserId'];
    $d->sourceId = $dbRow['SourceId'];
    $d->displayed = $dbRow['Displayed'];
    $d->lexicon = $dbRow['Lexicon'];
    $d->internalRep = $dbRow['InternalRep'];
    $d->htmlRep = $dbRow['HtmlRep'];
    $d->status = $dbRow['Status'];
    $d->createDate = $dbRow['CreateDate'];
    $d->modDate = $dbRow['ModDate'];
    return $d;
  }

  public function saveDisplayedValue() {
    db_updateDefinitionDisplayed($this);
  }

  public static function updateModDate($defId) {
    return db_updateDefinitionModDate($defId, time());
  }

  public function save() {
    $this->modDate = time();
    if ($this->id) {
      db_updateDefinition($this);
    } else {
      $this->createDate = time();
      db_insertDefinition($this);
    }
  }
}


class Typo {
  public $id;
  public $definitionId;
  public $problem;

  public static function load($id) {
    $result = new Typo();
    $dbRow = db_getTypoById($id);
    $result->populateFromDbRow($dbRow);
    return $result;
  }

  public static function loadByDefinitionId($definitionId) {
    $dbResult = db_getTyposByDefinitionId($definitionId);
    return Typo::populateFromDbResult($dbResult);
  }

  public static function populateFromDbResult($dbResult) {
    $result = array();
    while ($dbRow = mysql_fetch_assoc($dbResult)) {
      $typo = new Typo();
      $typo->populateFromDbRow($dbRow);
      $result[] = $typo;
    }
    mysql_free_result($dbResult);
    return $result;
  }

  private function populateFromDbRow($dbRow) {
    $this->id = $dbRow['Id'];
    $this->definitionId = $dbRow['DefinitionId'];
    $this->problem = $dbRow['Problem'];
  }

  public function save() {
    db_insertTypo($this);
  }

  public function delete() {
    db_deleteTypo($this);
  }

  public static function deleteAllByDefinitionId($definitionId) {
    db_deleteTyposByDefinitionId($definitionId);
  }
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
      $result->user = User::load($definition->userId);
      $result->source = Source::load($definition->sourceId);
      $result->typos = Typo::loadByDefinitionId($definition->id);
      $result->comment = Comment::loadByDefinitionId($definition->id);
      if ($result->comment) {
        $result->commentAuthor = User::load($result->comment->userId);
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

class Comment {
  public $id;
  public $definitionId;
  public $userId;
  public $status = ST_ACTIVE;
  public $contents;
  public $htmlContents;

  public static function load($id) {
    $result = new Comment();
    $dbRow = db_getCommentById($id);
    $result->populateFromDbRow($dbRow);
    return $result;
  }

  public static function loadByDefinitionId($definitionId) {
    $dbRow = db_getCommentByDefinitionId($definitionId);
    if ($dbRow) {
      $result = new Comment();
      $result->populateFromDbRow($dbRow);
      return $result;
    } else {
      return NULL;
    }
  }

  private function populateFromDbRow($dbRow) {
    $this->id = $dbRow['Id'];
    $this->definitionId = $dbRow['DefinitionId'];
    $this->userId = $dbRow['UserId'];
    $this->status = $dbRow['Status'];
    $this->contents = $dbRow['Contents'];
    $this->htmlContents = $dbRow['HtmlContents'];
  }

  public function normalizeAndSave() {
    $this->normalize();
    $this->save();
  }

  public function normalize() {
    $this->contents = text_internalizeDefinition($this->contents);
    $this->htmlContents = text_htmlizeWithNewlines($this->contents, TRUE);    
  }

  public function save() {
    if ($this->id) {
      db_updateComment($this);
    } else {
      db_insertComment($this);
    }
  }
}

class RecentLink {
  public $id;
  public $userId;
  public $visitDate;
  public $url;
  public $text;

  public static function load($id) {
    $result = new RecentLink();
    $dbRow = db_getRecentLinkById($id);
    $result->populateFromDbRow($dbRow);
    return $result;
  }

  public static function loadByUserIdUrlText($userId, $url, $text) {
    $dbRow = db_getRecentLinkByUserIdUrlText($userId, $url, $text);
    if ($dbRow) {
      $result = new RecentLink();
      $result->populateFromDbRow($dbRow);
      return $result;
    } else {
      return null;
    }
  }  

  public static function createOrUpdate($text) {
    $userId = session_getUserId();
    $url = $_SERVER['REQUEST_URI'];
    $rl = RecentLink::loadByUserIdUrlText($userId, $url, $text);

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
    $dbResult = db_getRecentLinksByUserId($userId);
    $recentLinks = RecentLink::populateFromDbResult($dbResult);
    while (count($recentLinks) > MAX_RECENT_LINKS) {
      $deadLink = array_pop($recentLinks);
      $deadLink->delete();
    }
    return $recentLinks;
  }

  public function populateFromDbRow($dbRow) {
    $this->id = $dbRow['Id']; 
    $this->userId = $dbRow['UserId'];
    $this->visitDate = $dbRow['VisitDate'];
    $this->url = $dbRow['Url'];
    $this->text = $dbRow['Text'];
  }

  public static function populateFromDbResult($dbResult) {
    $result = array();
    while ($dbRow = mysql_fetch_assoc($dbResult)) {
      $obj = new RecentLink();
      $obj->populateFromDbRow($dbRow);
      $result[] = $obj;
    }
    mysql_free_result($dbResult);
    return $result;
  }

  public function delete() {
    db_deleteRecentLink($this);
  }

  public function save() {
    if ($this->id) {
      db_updateRecentLink($this);
    } else {
      db_insertRecentLink($this);
    }
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


class Inflection {
  public $id;
  public $description;

  public static function create($description) {
    $i = new Inflection();
    $i->description = $description;
    return $i;
  }

  public static function load($id) {
    $dbRow = db_getInflectionById($id);
    return Inflection::createFromDbRow($dbRow);
  }

  public static function loadInfinitive() {
    $dbRow = db_getInfinitiveInflection();
    return Inflection::createFromDbRow($dbRow);
  }

  public static function loadParticiple() {
    $dbRow = db_getParticipleInflection();
    return Inflection::createFromDbRow($dbRow);
  }

  public static function loadLongInfinitive() {
    $dbRow = db_getLongInfinitiveInflection();
    return Inflection::createFromDbRow($dbRow);
  }

  public static function loadAll() {
    $dbResult = db_selectAllInflections();
    return Inflection::populateFromDbResult($dbResult);
  }

  public static function loadForModel($modelId) {
    return db_getInflectionsForModelId($modelId);
  }

  public static function loadByModelType($modelType) {
    $dbResult = db_getInflectionsByModelType($modelType);
    return Inflection::populateFromDbResult($dbResult);
  }

  public static function loadAllMapByInflectionId() {
    $inflections = Inflection::loadAll();
    return mapByInflectionId($inflections);
  }

  public static function mapByInflectionId($inflectionList) {
    $result = array();
    foreach ($inflectionList as $i) {
      $result[$i->id] = $i;
    }
    return $result;
  }

  public static function createFromDbRow($dbRow) {
    if (!$dbRow) {
      return NULL;
    }
    $i = new Inflection();
    $i->id = $dbRow['infl_id']; 
    $i->description = $dbRow['infl_descr'];
    return $i;
  }

  public static function populateFromDbResult($dbResult) {
    $result = array();
    while ($dbRow = mysql_fetch_assoc($dbResult)) {
      $result[] = Inflection::createFromDbRow($dbRow);
    }
    mysql_free_result($dbResult);
    return $result;
  }

  public function save() {
    if ($this->id) {
      db_updateInflection($this);
    } else {
      db_insertInflection($this);
    }
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

  public static function searchWordlists($cuv, $hasDiacritics) {
    $dbResult = db_searchWordlists($cuv, $hasDiacritics);
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
  public static function loadParticiplesForVerbModel($modelNumber,
                                                     $participleNumber) {
    $infl = Inflection::loadParticiple();
    $dbResult = db_getParticiplesForVerbModel($modelNumber, $participleNumber,
                                              $infl->id);
    return Lexem::populateFromDbResult($dbResult);    
  }

  public function regenerateParadigm() {
    $wordLists = $this->generateParadigm();
    assert(is_array($wordLists));

    WordList::deleteByLexemId($this->id);
    foreach($wordLists as $wl) {
      $wl->save();
    }

    if ($this->modelType == 'VT') {
      $model = Model::loadCanonicalByTypeNumber($this->modelType,
						$this->modelNumber);
      $pm = ParticipleModel::loadByVerbModel($model->number);
      $this->regeneratePastParticiple($pm->participleModel);
    }
    if ($this->modelType == 'V' || $this->modelType == 'VT') {
      $this->regenerateLongInfinitive();
    }
  }

  public function regeneratePastParticiple($participleModel) {
    $infl = Inflection::loadParticiple();
    $wordlist = WordList::loadByLexemIdInflectionId($this->id, $infl->id);
    $model = Model::loadByTypeNumber('A', $participleModel);
    
    foreach ($wordlist as $wl) {
      // Load an existing lexem only if it has the same model as $model or
      // $temporaryModel. Otherwise create a new lexem.
      $lexems = Lexem::loadByUnaccented($wl->unaccented);
      $lexem = null;
      foreach ($lexems as $l) {
        if ($l->modelType == 'T' ||
            ($l->modelType == 'A' && $l->modelNumber = $model->number)) {
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
        $lexem = Lexem::create($wl->form, 'A', $model->number, '');
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
    $wordlist = WordList::loadByLexemIdInflectionId($this->id, $infl->id);
    $f107 = Model::loadByTypeNumber('F', 107);
    $f113 = Model::loadByTypeNumber('F', 113);
    
    foreach ($wordlist as $wl) {
      $model = text_endsWith($wl->unaccented, 'are') ? $f113 : $f107;
      
      // Load an existing lexem only if it has one of the models F113, F107
      // or T1. Otherwise create a new lexem.
      $lexems = Lexem::loadByUnaccented($wl->unaccented);
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
        $lexem = Lexem::create($wl->form, 'F', $model->number, '');
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

  public function generateInflectedForm($inflId) {
    $model = Model::loadCanonicalByTypeNumber($this->modelType,
                                              $this->modelNumber);    
    return generateInflectedFormWithModel($inflId, $model->id);
  }

  public function generateInflectedFormWithModel($inflId, $modelId) {
    if (!Constraint::validInflection($inflId, $this->restriction)) {
      return array();
    }
    $wordLists = array();
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
        $transforms[] = Transform::load($mds[$i]->transformId);
      }
      
      $result = text_applyTransforms($this->form, $transforms,
                                     $accentShift, $vowel);
      if (!$result) {
        return null;
      }
      $wordLists[] = WordList::create($result, $this->id, $inflId, $variant);
      $start = $end;
    }
    
    return $wordLists;
  }
  
  public function generateParadigm() {
    $model = Model::loadCanonicalByTypeNumber($this->modelType,
                                              $this->modelNumber);
    $inflIds = Inflection::loadForModel($model->id);
    $wordLists = array();
    foreach ($inflIds as $inflId) {
      $wl = $this->generateInflectedFormWithModel($inflId, $model->id);
      if ($wl === null) {
        return $inflId;
      }
      $wordLists = array_merge($wordLists, $wl);
    }
    return $wordLists;
  }

  /**
   * Called when the model type of a lexem changes from VT to something else.
   * Only deletes participles that do not have their own definitions.
   */
  public function deleteParticiple($oldModelNumber) {
    $infl = Inflection::loadParticiple();
    $pm = ParticipleModel::loadByVerbModel($oldModelNumber);
    $this->_deleteDependentModels($infl->id, 'A', array($pm->participleModel));
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
   * Arguments for participles: 'A', $participleModel.
   * Arguments for long infinitives: 'F', ('107', '113').
   */
  private function _deleteDependentModels($inflId, $modelType, $modelNumbers) {
    $wordlist = WordList::loadByLexemIdInflectionId($this->id, $inflId);
    $ldms = LexemDefinitionMap::loadByLexemId($this->id);

    $defHash = array();
    foreach($ldms as $ldm) {
      $defHash[$ldm->definitionId] = true;
    }
    
    foreach ($wordlist as $wl) {
      // Delete lexems of model T1 or A{$pm}
      $lexems = Lexem::loadByUnaccented($wl->unaccented);
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
      WordList::deleteByLexemId($this->id);
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
    $definition = Definition::load($definitionId);
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

class Transform {
  public $id;
  public $from;
  public $to;
  public $description;

  public function toString() {
    $from = $this->from ? $this->from : 'nil';
    $to = $this->to ? $this->to : 'nil';
    return "($from=>$to)";
  }

  public static function create($from, $to, $description) {
    $t = new Transform();
    $t->from = $from;
    $t->to = $to;
    $t->description = $description;
    return $t;
  }

  public static function createOrLoad($from, $to) {
    $t = Transform::loadByFromTo($from, $to);
    if (!$t) {
      $t = Transform::create($from, $to, '');
      $t->save();
      $t->id = db_getLastInsertedId();
    }
    return $t;
  }

  public static function load($id) {
    $dbRow = db_getTransformById($id);
    return Transform::createFromDbRow($dbRow);
  }

  public static function loadByFromTo($from, $to) {
    $dbRow = db_getTransformByFromTo($from, $to);
    return Transform::createFromDbRow($dbRow);
  }

  public static function createFromDbRow($dbRow) {
    if (!$dbRow) {
      return null;
    }
    $t = Transform::create($dbRow['transf_from'],
                           $dbRow['transf_to'],
                           $dbRow['transf_descr']);
    $t->id = $dbRow['transf_id'];
    return $t;
  }

  public static function populateFromDbResult($dbResult) {
    $result = array();
    while ($dbRow = mysql_fetch_assoc($dbResult)) {
      $result[] = Transform::createFromDbRow($dbRow);
    }
    mysql_free_result($dbResult);
    return $result;
  }

  public function save() {
    if ($this->id) {
      db_updateTransform($this);
    } else {
      db_insertTransform($this);
    }
  }
}

class WordList {
  // WordList::unacccented is mapped to two fields in the database,
  // wl_neaccentuat and wl_utf8_general. These fields will always have
  // identical values, but they are stored with different collates to
  // speed up searches with/without diacritics.
  public $form;
  public $unaccented;
  public $lexemId;
  public $inflectionId;
  public $variant;

  public static function create($form, $lexemId, $inflectionId, $variant) {
    $wl = new WordList();
    $wl->form = $form;
    $wl->unaccented = str_replace("'", '', $form);
    $wl->lexemId = $lexemId;
    $wl->inflectionId = $inflectionId;
    $wl->variant = $variant;
    return $wl;
  }

  public static function loadByLexemId($lexemId) {
    $dbResult = db_getWordListsByLexemId($lexemId);
    return WordList::populateFromDbResult($dbResult);
  }

  public static function loadByLexemIdMapByInflectionId($lexemId) {
    $wordLists = WordList::loadByLexemId($lexemId);
    return WordList::mapByInflectionId($wordLists);
  }

  public static function mapByInflectionId($wordLists) {
    $result = array();
    foreach ($wordLists as $wl) {
      if (array_key_exists($wl->inflectionId, $result)) {
        // The wordlists are already sorted by variant
        $result[$wl->inflectionId][] = $wl;
      } else {
        $result[$wl->inflectionId] = array($wl);
      }
    }
    return $result;
  }

  public static function loadByLexemIdInflectionId($lexemId, $inflectionId) {
    $dbResult = db_getWordListByLexemIdInflectionId($lexemId, $inflectionId);
    return WordList::populateFromDbResult($dbResult);
  }

  public static function loadByUnaccented($unaccented) {
    $dbResult = db_getWordListsByUnaccented($unaccented);
    return WordList::populateFromDbResult($dbResult);
  }

  // Used by the scrabble LOC verification tool
  public static function loadLoc($cuv, $hasDiacritics) {
    $dbResult = db_getLocWordlists($cuv, $hasDiacritics);
    return Wordlist::populateFromDbResult($dbResult);
  }

  public static function createFromDbRow($dbRow) {
    if (!$dbRow) {
      return null;
    }
    return WordList::create($dbRow['wl_form'], $dbRow['wl_lexem'],
                            $dbRow['wl_analyse'], $dbRow['wl_variant']);
  }

  public static function populateFromDbResult($dbResult) {
    $result = array();
    while ($dbRow = mysql_fetch_assoc($dbResult)) {
      $result[] = WordList::createFromDbRow($dbRow);
    }
    mysql_free_result($dbResult);
    return $result;
  }

  public function save() {
    db_insertWordList($this);
  }

  public static function deleteByLexemId($lexemId) {
    db_deleteWordListsByLexemId($lexemId);
  }
}

class ParticipleModel {
  public $id;
  public $verbModel;
  public $participleModel;

  public static function create($verbModel, $participleModel) {
    $pm = new ParticipleModel();
    $pm->verbModel = $verbModel;
    $pm->participleModel = $participleModel;
    return $pm;
  }

  public static function loadByVerbModel($verbModel) {
    $dbRow = db_getParticipleModelByVerbModel($verbModel);
    return ParticipleModel::createFromDbRow($dbRow);
  }

  public static function createFromDbRow($dbRow) {
    if (!$dbRow) {
      return NULL;
    }
    $pm = ParticipleModel::create($dbRow['pm_verb_model'],
                                  $dbRow['pm_participle_model']);
    $pm->id = $dbRow['pm_id'];
    return $pm;
  }

  public function save() {
    if ($this->id) {
      db_updateParticipleModel($this);
    } else {
      db_insertParticipleModel($this);
    }
  }

  public function delete() {
    db_deleteParticipleModel($this);
  }

  public static function updateVerbModel($modelNumber, $newModelNumber) {
    return db_updateParticipleModelVerb($modelNumber, $newModelNumber);
  }

  public static function updateAdjectiveModel($modelNumber, $newModelNumber) {
    return db_updateParticipleModelAdjective($modelNumber, $newModelNumber);
  }
}

/**
 * This class has very limited usage and therefore will not have the usual
 * fields and methods.
 */
class Constraint {

  /**
   * Given a restriction like 'PT', and an inflection, returns true iff
   * the inflection ID is valid under all the restrictions.
   */
  public static function validInflection($inflId, $restr) {
    if (!$restr) {
      return true;
    }
    $a = db_getNumMetRestrictions($restr, $inflId);
    return ($a == mb_strlen($restr));
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

class FullTextIndex {
  public $lexemId;
  public $inflectionId;
  public $definitionId;
  public $position;

  public static function create($lexemId, $inflectionId, $definitionId,
                                $position) {
    $fti = new FullTextIndex();
    $fti->lexemId = $lexemId;
    $fti->inflectionId = $inflectionId;
    $fti->definitionId = $definitionId;
    $fti->position = $position;
    return $fti;
  }

  // Takes a comma-separated string of lexem ids
  public static function loadDefinitionIdsForLexems($lexemIds) {
    if (!$lexemIds) {
      return array();
    }
    return db_getFullTextIndexesByLexemIds($lexemIds);
  }

  public static function loadPositionsByLexemIdsDefinitionId($lexemIds,
                                                             $defId) {
    return db_getPositionsByLexemIdsDefinitionId($lexemIds, $defId);
  }

  public static function createFromDbRow($dbRow) {
    if (!$dbRow) {
      return NULL;
    }
    return FullTextIndex::create($dbRow['LexemId'],
                                 $dbRow['InflectionId'],
                                 $dbRow['DefinitionId'],
                                 $dbRow['Position']);
  }

  public function save() {
    db_insertFullTextIndex($this);
  }
}


class Variable {
  public $name;
  public $value;

  public static function peek($name, $default = null) {
    $result = new Variable();
    $dbRow = db_getVariable($name);
    if (!$dbRow) {
      return $default;
    }
    $result->populateFromDbRow($dbRow);
    return $result->value;
  }

  public static function poke($name, $value) {
    $v = self::peek($name);
    if ($v) {
      db_updateVariable($name, $value);
    } else {
      db_insertVariable($name, $value);
    }
  }

  private function populateFromDbRow($dbRow) {
    $this->name = $dbRow['Name'];
    $this->value = $dbRow['Value'];
  }
}

?>
