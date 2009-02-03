<?

function db_init($host, $user, $password, $database) {
  $db = mysql_connect($host, $user, $password);
  mysql_select_db($database, $db);
  mysql_query('set names utf8');
  return $db;
}

function db_changeDatabase($dbName) {
  $dbName = addslashes($dbName);
  return logged_query("use $dbName");
}

/**
 * For queries that count rows, or that otherwise return a single record with
 * an integer, return that integer.
 */
function db_fetchInteger($result) {
  $row = mysql_fetch_row($result);
  mysql_free_result($result);
  return (int)$row[0];
}

function db_fetchSingleRow($result) {
  if ($result) {
    $row = mysql_fetch_assoc($result);
    mysql_free_result($result);
    return $row;
  } else {
    return NULL;
  }
}

function db_getLastInsertedId() {
  $query = "select last_insert_id()";
  return db_fetchInteger(logged_query($query));
}

function db_getArray($dbSet) {
  $result = array();
  while ($dbSet && $row = mysql_fetch_assoc($dbSet)) {
    $result[] = $row;
  }
  mysql_free_result($dbSet);
  return $result;
}

function db_getScalarArray($dbSet) {
  $result = array();
  while ($dbSet && $row = mysql_fetch_row($dbSet)) {
    $result[] = $row[0];
  }
  mysql_free_result($dbSet);
  return $result;
}

function db_getCompactIntArray($dbSet) {
  $result = int_create(mysql_num_rows($dbSet));
  $pos = 0;
  while ($dbSet && $row = mysql_fetch_row($dbSet)) {
    int_put($result, $pos++, $row[0]);
  }
  mysql_free_result($dbSet);
  return $result;
}

function logged_query($query) {
  debug_resetClock();
  $result = mysql_query($query);
  debug_stopClock($query);
  if (!$result) {
    $errno = mysql_errno();
    $message = "A intervenit o eroare $errno la comunicarea cu baza de date: ";

    if ($errno == 1139) {
      $message .= "Verificaţi că parantezele folosite sunt închise corect.";
    } else if ($errno == 1049) {
      $message .= "Nu există o bază de date pentru această versiune LOC.";
    } else {
      $message .= mysql_error();
    }

    $query = htmlspecialchars($query);
    $message .= "<br/>Query MySQL: [$query]<br/>";

    if (smarty_isInitialized()) {
      smarty_assign('errorMessage', $message);
      smarty_displayCommonPageWithSkin('errorMessage.ihtml');
    } else {
      var_dump($message);
    }
    exit;  
  }
  return $result;
}

/*************************** Users *******************************/

function db_changesInsert($definitionId, $userId, $oldInternalRep) {
  $now = time();
  $oldInternalRep = addslashes($oldInternalRep);
  return logged_query("insert into changes set " .
                      "definitionId = $definitionId, " .
                      "userId = $userId, " .
                      "internalRep = '$oldInternalRep', " .
                      "createDate = $now");
}

function db_getUserById($id) {
  $query = "select * from User where Id = '$id'";
  return db_fetchSingleRow(logged_query($query));
}

function db_getUserByNick($nick) {
  $nick = addslashes($nick);
  $query = "select * from User where Nick = '$nick'";
  return db_fetchSingleRow(logged_query($query));
}

function db_getUserByEmail($email) {
  $email = addslashes($email);
  $query = "select * from User where Email = '$email'";
  return db_fetchSingleRow(logged_query($query));
}

function db_getUserByNickEmailPassword($nickOrEmail, $password) {
  $nickOrEmail = addslashes($nickOrEmail);
  $query = "select * from User " .
    "where (Email = '$nickOrEmail' or Nick = '$nickOrEmail') " .
    "and Password = '$password'";
  return db_fetchSingleRow(logged_query($query));
}

function db_getUserByIdPassword($id, $password) {
  $query = "select * from User " .
    "where Id = '$id' and Password = '$password'";
  return db_fetchSingleRow(logged_query($query));
}


function db_getDefinitionById($id) {
  $query = "select * from Definition where Id = '$id'";
  return db_fetchSingleRow(logged_query($query));
}

function db_getDefinitionsByIds($ids) {
  $query = "select * from Definition where Id in ($ids) order by Lexicon";
  return logged_query($query);
}

function db_getDefinitionsByLexemId($lexemId) {
  $query = "select Definition.* from Definition, LexemDefinitionMap " .
    "where Definition.Id = LexemDefinitionMap.DefinitionId " .
    "and LexemDefinitionMap.LexemId = $lexemId " .
    "and Status in (0, 1) " .
    "order by SourceId";
  return logged_query($query);
}

function db_selectDefinitionsHavingTypos() {
  return logged_query("select distinct Definition.* from Definition, Typo " .
                      "where Definition.Id = Typo.DefinitionId " .
                      "order by Lexicon limit 500");
}

function db_selectDefinitionsForLexemIds($lexemIds, $sourceId, $preferredWord) {
  $sourceClause = $sourceId ? "and Definition.SourceId = $sourceId" : '';
  $query = "select distinct Definition.* " .
    "from Definition, LexemDefinitionMap " .
    "where Definition.Id = LexemDefinitionMap.DefinitionId " .
    "and LexemDefinitionMap.LexemId in ($lexemIds) " .
    "and Definition.Status = 0 " .
    $sourceClause .
    " order by (Lexicon = '$preferredWord') desc, " .
    "Definition.Lexicon, Definition.SourceId";
  return logged_query($query);
}

function db_countDefinitions() {
  return db_fetchInteger(logged_query("select count(*) from Definition"));
}

function db_countAssociatedDefinitions() {
  // same as select count(distinct DefinitionId) from LexemDefinitionMap,
  // only faster.
  $query = 'select count(*) from ' .
    '(select count(*) from LexemDefinitionMap group by DefinitionId) ' .
    'as someLabel';
  return db_fetchInteger(logged_query($query));
}

function db_countDefinitionsByStatus($status) {
  $query = "select count(*) from Definition where Status = $status";
  return db_fetchInteger(logged_query($query));
}

function db_countRecentDefinitions($minCreateDate) {
  $query = "select count(*) from Definition where " .
    "CreateDate >= $minCreateDate and " .
    "Status = " . ST_ACTIVE;
  return db_fetchInteger(logged_query($query));
}

function db_countDefinitionsHavingTypos() {
  $query = 'select count(distinct DefinitionId) from Typo';
  return db_fetchInteger(logged_query($query));
}

function db_getUnassociatedDefinitions() {
  $query = 'select * from Definition ' .
    'where Status != 2 ' .
    'and Id not in (select DefinitionId from LexemDefinitionMap)';
  return logged_query($query);
}

function db_searchRegexp($regexp, $hasDiacritics, $sourceId) {
  $field = $hasDiacritics ? 'lexem_neaccentuat' : 'lexem_utf8_general';
  $sourceClause = $sourceId ? "and Definition.SourceId = $sourceId " : '';
  $sourceJoin = $sourceId ?  "join LexemDefinitionMap " .
    "on lexem_id = LexemDefinitionMap.LexemId " .
    "join Definition on LexemDefinitionMap.DefinitionId = Definition.Id " : '';
  $query = "select * from lexems " .
    $sourceJoin .
    "where $field $regexp " .
    $sourceClause .
    "order by lexem_neaccentuat limit 1000";
  return logged_query($query);
}

function db_countRegexpMatches($regexp, $hasDiacritics, $sourceId) {
  $field = $hasDiacritics ? 'lexem_neaccentuat' : 'lexem_utf8_general';
  $sourceClause = $sourceId ? "and Definition.SourceId = $sourceId " : '';
  $sourceJoin = $sourceId ?  "join LexemDefinitionMap " .
    "on lexem_id = LexemDefinitionMap.LexemId " .
    "join Definition on LexemDefinitionMap.DefinitionId = Definition.Id " : '';
  $query = "select count(*) from lexems " .
    $sourceJoin .
    "where $field $regexp " .
    $sourceClause;
  return db_fetchInteger(logged_query($query));
}

function db_searchLexems($cuv, $hasDiacritics) {
  $field = $hasDiacritics ? 'lexem_neaccentuat' : 'lexem_utf8_general';
  $query = "select * from lexems " .
    "where $field = '$cuv' " .
    "order by lexem_neaccentuat";
  return logged_query($query);
}

function db_searchWordlists($cuv, $hasDiacritics) {
  $field = $hasDiacritics ? 'wl_neaccentuat' : 'wl_utf8_general';
  $query = "select distinct lexems.* from wordlist, lexems " .
    "where wl_lexem = lexem_id and $field = '$cuv' " .
    "order by lexem_neaccentuat";
  return logged_query($query);
}

function db_getLocWordlists($cuv, $hasDiacritics) {
  $field = $hasDiacritics ? 'wl_neaccentuat' : 'wl_utf8_general';
  $query = "select distinct wordlist.* from wordlist, lexems " .
    "where wl_lexem = lexem_id and $field = '$cuv' " .
    "and lexem_is_loc order by lexem_neaccentuat";
  return logged_query($query);
}

function db_searchApproximate($cuv, $hasDiacritics) {
  $field = $hasDiacritics ? 'lexem_neaccentuat' : 'lexem_utf8_general';
  $query = "select * from lexems " .
    "where dist2($field, '$cuv') " .
    "order by lexem_neaccentuat";
  return logged_query($query);
}

function db_searchModerator($regexp, $hasDiacritics, $sourceId, $status,
                            $userId, $minCreateDate, $maxCreateDate) {
  $field = $hasDiacritics ? 'lexem_neaccentuat' : 'lexem_utf8_general';
  $sourceClause = $sourceId ? "and Definition.SourceId = $sourceId" : '';
  $userClause = $userId ? "and Definition.UserId = $userId" : '';

  $query = "select distinct Definition.* " .
    "from lexems " .
    "join LexemDefinitionMap " .
    "on lexem_id = LexemDefinitionMap.LexemId " .
    "join Definition on LexemDefinitionMap.DefinitionId = Definition.Id " .
    "where $field $regexp " .
    "and Definition.Status = $status " .
    "and Definition.CreateDate >= $minCreateDate " .
    "and Definition.CreateDate <= $maxCreateDate " .
    $sourceClause . " " . $userClause . " " .
    "order by Definition.Lexicon, Definition.SourceId " .
    "limit 500";
  return logged_query($query);
}

function db_searchDeleted($regexp, $hasDiacritics, $sourceId, $userId,
                          $minCreateDate, $maxCreateDate) {
  $collate = $hasDiacritics ? '' : 'collate utf8_general_ci';
  $sourceClause = $sourceId ? "and Definition.SourceId = $sourceId" : '';
  $userClause = $userId ? "and Definition.UserId = $userId" : '';

  $query = "select * from Definition " .
    "where Lexicon $collate $regexp " .
    "and Status = " . ST_DELETED . " " .
    "and CreateDate >= $minCreateDate " .
    "and CreateDate <= $maxCreateDate " .
    $sourceClause . " " . $userClause . " " .
    "order by Lexicon, SourceId " .
    "limit 500";
  return logged_query($query);
}

function db_searchDefId($defId) {
  $query = "select * from Definition where Id = '$defId' and Status = 0 ";
  return db_fetchSingleRow(logged_query($query));
}

function db_searchLexemId($lexemId) {
  $lexemId = addslashes($lexemId);
  $query = "select Definition.* from Definition, LexemDefinitionMap " .
    "where Definition.Id = LexemDefinitionMap.DefinitionId " .
    "and LexemDefinitionMap.LexemId = '$lexemId' " .
    "and Definition.Status = 0 " .
    "order by Definition.Lexicon, Definition.SourceId";
  return logged_query($query);
}

function db_selectTop() {
  return logged_query("select Nick, count(*) as NumDefinitions, " .
                      "sum(length(InternalRep)) as NumChars, " .
                      "max(CreateDate) as Timestamp " .
                      "from Definition, User " .
                      "where Definition.UserId = User.Id " .
                      "and Definition.Status = 0 " .
                      "group by Nick");
}

function db_getDefinitionsByMinModDate($modDate) {
  $query = "select * from Definition " .
    "where Status = " . ST_ACTIVE . " and " .
    "ModDate >= '$modDate' " .
    "order by ModDate, Id";
  return logged_query($query);
}

function db_getLexemsByMinModDate($modDate) {
  $query = "select Definition.Id, lexem_neaccentuat " .
    "from Definition force index(ModDate), LexemDefinitionMap, lexems " .
    "where Definition.Id = LexemDefinitionMap.DefinitionId " .
    "and LexemDefinitionMap.LexemId = lexem_id " .
    "and Definition.Status = 0 " .
    "and Definition.ModDate >= $modDate " .
    "order by Definition.ModDate, Definition.Id";
  return logged_query($query);
}

function db_getUpdate3Definitions($modDate) {
  $query = "select * from Definition " .
    "where ModDate >= '$modDate' " .
    "order by ModDate, Id";
  return logged_query($query);
}

function db_getUpdate3LexemIds($modDate) {
  $query = "select Definition.Id, LexemId " .
    "from Definition force index(ModDate), LexemDefinitionMap " .
    "where Definition.Id = LexemDefinitionMap.DefinitionId " .
    "and Definition.ModDate >= $modDate " .
    "order by Definition.ModDate, Definition.Id";
  return logged_query($query);
}

function db_getUpdate3Lexems($modDate) {
  $query = "select * from lexems " .
    "where ModDate >= '$modDate' " .
    "order by ModDate, lexem_id";
  return logged_query($query);
}

function db_insertTypo($typo) {
  $query = sprintf("insert into Typo set " .
                   "DefinitionId = '%d', " .
                   "Problem = '%s'",
                   $typo->definitionId,
                   addslashes($typo->problem));
  return logged_query($query);
}

function db_getTyposByDefinitionId($definitionId) {
  return logged_query("select * from Typo where DefinitionId = '$definitionId'");
}

function db_deleteTyposByDefinitionId($definitionId) {
  return logged_query("delete from Typo where DefinitionId = '$definitionId'");
}

function db_insertDefinition($definition) {
  $query = sprintf("insert into Definition set UserId = '%d', " .
                   "SourceId = '%d', " .
                   "Displayed = '%d', " .
                   "Lexicon = '%s', " .
                   "InternalRep = '%s', " .
                   "HtmlRep = '%s', " .
                   "Status = '%d', " .
                   "CreateDate = '%d', " .
                   "ModDate = '%d'",
                   $definition->userId,
                   $definition->sourceId,
                   $definition->displayed,
                   addslashes($definition->lexicon),
                   addslashes($definition->internalRep),
                   addslashes($definition->htmlRep),
                   $definition->status,
                   $definition->createDate,
                   $definition->modDate);
  return logged_query($query);
}

function db_updateDefinition($definition) {
  $query = sprintf("update Definition set UserId = '%d', " .
                   "SourceId = '%d', " .
                   "Displayed = '%d', " .
                   "Lexicon = '%s', " .
                   "InternalRep = '%s', " .
                   "HtmlRep = '%s', " .
                   "Status = '%d', " .
                   "CreateDate = '%d', " .
                   "ModDate = '%d' " .
                   "where Id = '%d'",
                   $definition->userId,
                   $definition->sourceId,
                   $definition->displayed,
                   addslashes($definition->lexicon),
                   addslashes($definition->internalRep),
                   addslashes($definition->htmlRep),
                   $definition->status,
                   $definition->createDate,
                   $definition->modDate,
                   $definition->id);
  return logged_query($query);
}

function db_updateDefinitionModDate($defId, $modDate) {
  $query = sprintf("update Definition set ModDate = '$modDate' " .
                   "where Id = '$defId'");
  return logged_query($query);
}

function db_updateLexemModDate($lexemId, $modDate) {
  $query = sprintf("update lexems set ModDate = '$modDate' " .
                   "where lexem_id = '$lexemId'");
  return logged_query($query);
}

function db_updateDefinitionDisplayed($definition) {
  $query = sprintf("update Definition set Displayed = '%d' " .
                   "where Id = '%d'",
                   $definition->displayed,
                   $definition->id);
  return logged_query($query);
}

function db_insertUser($user) {
  // Note: No user preferences at this point
  $query = sprintf("insert into User set Nick = '%s', " .
                   "Name = '%s', " .
                   "Email = '%s', " .
                   "EmailVisible = '%s', " .
                   "Password = '%s', " .
                   "Moderator = '%s'",
                   addslashes($user->nick),
                   addslashes($user->name),
                   addslashes($user->email),
                   $user->emailVisible,
                   $user->password,
                   $user->moderator);
  return logged_query($query);
}

function db_updateUser($user) {
  // Note: We never save the moderator value here. That should only be done
  // explicitly from the MySQL prompt by an admin.
  $query = sprintf("update User set Nick = '%s', " .
                   "Name = '%s', " .
                   "Email = '%s', " .
                   "EmailVisible = '%s', " .
                   "Password = '%s', " .
                   "Preferences = '%s' " .
                   "where Id = '%d'",
                   addslashes($user->nick),
                   addslashes($user->name),
                   addslashes($user->email),
                   $user->emailVisible,
                   $user->password,
                   addslashes($user->prefs),
                   $user->id);
  return logged_query($query);
}

function db_getUserByCookieString($cookieString) {
  $query = "select User.* from User, Cookie " .
    "where Cookie.CookieString = '$cookieString' " .
    "and User.Id = Cookie.UserId";
  return db_fetchSingleRow(logged_query($query));
}

function db_insertCookie($cookie) {
  $query = sprintf("insert into Cookie set CookieString = '%s', " .
                   "UserId = '%d', CreateDate = '%d'",
                   $cookie->cookieString,
                   $cookie->userId,
                   $cookie->createDate);
  return logged_query($query);
}

function db_getCookieByCookieString($cookieString) {
  $query = sprintf("select * from Cookie where CookieString = '%s'",
                   addslashes($cookieString));
  return db_fetchSingleRow(logged_query($query));
}

function db_deleteCookie($cookie) {
  logged_query("delete from Cookie where Id = '" . $cookie->id . "'");
}

function db_deleteCookiesBefore($timestamp) {
  $query = "delete from Cookie where CreateDate < $timestamp";
  logged_query($query);
}

/**
 * Searches and appends the user's nick for the id found in $row['uid'].
 */
function db_appendUserNick($row) {
  $result = db_usrSelectNickById($row['uid']);
  $usrRow = mysql_fetch_assoc($result);
  $row['nick'] = $usrRow['nick'];
  return $row;
}


/****************************** Guide entries ******************************/

function db_selectAllActiveGuideEntries() {
  return logged_query("select * from GuideEntry where Status = 0");
}

function db_getGuideEntryById($id) {
  $query = "select * from GuideEntry where Id = '$id'";
  return db_fetchSingleRow(logged_query($query));
}

function db_updateGuideEntry($guideEntry) {
  $query = sprintf("update GuideEntry set " .
                   "Correct = '%s', " .
                   "CorrectHtml = '%s', " .
                   "Wrong = '%s', " .
                   "WrongHtml = '%s', " .
                   "Comments = '%s', " .
                   "CommentsHtml = '%s', " .
                   "Status = '%s', " .
                   "CreateDate = '%s', " .
                   "ModDate = '%s' " .
                   "where Id = '%s'",
                   addslashes($guideEntry->correct),
                   addslashes($guideEntry->correctHtml),
                   addslashes($guideEntry->wrong),
                   addslashes($guideEntry->wrongHtml),
                   addslashes($guideEntry->comments),
                   addslashes($guideEntry->commentsHtml),
                   $guideEntry->status,
                   $guideEntry->createDate,
                   $guideEntry->modDate,
                   $guideEntry->id);
  return logged_query($query);
}

function db_insertGuideEntry($guideEntry) {
  $query = sprintf("insert into GuideEntry set " .
                   "Correct = '%s', " .
                   "CorrectHtml = '%s', " .
                   "Wrong = '%s', " .
                   "WrongHtml = '%s', " .
                   "Comments = '%s', " .
                   "CommentsHtml = '%s', " .
                   "Status = '%s', " .
                   "CreateDate = '%s', " .
                   "ModDate = '%s' ",
                   addslashes($guideEntry->correct),
                   addslashes($guideEntry->correctHtml),
                   addslashes($guideEntry->wrong),
                   addslashes($guideEntry->wrongHtml),
                   addslashes($guideEntry->comments),
                   addslashes($guideEntry->commentsHtml),
                   $guideEntry->status,
                   $guideEntry->createDate,
                   $guideEntry->modDate);
  return logged_query($query);
}


/****************************** Comments ******************************/

function db_getCommentById($id) {
  $query = "select * from Comment where Id = '$id'";
  return db_fetchSingleRow(logged_query($query));
}

function db_getCommentByDefinitionId($definitionId) {
  $query = "select * from Comment ".
    "where DefinitionId = '$definitionId' " .
    "and Status = ". ST_ACTIVE;
  return db_fetchSingleRow(logged_query($query));
}

function db_updateComment($comment) {
  $query = sprintf("update Comment set " .
                   "DefinitionId = '%s', " .
                   "UserId = '%s', " .
                   "Status = '%s', " .
                   "Contents = '%s', " .
                   "HtmlContents = '%s' " .
                   "where Id = '%s'",
                   $comment->definitionId,
                   $comment->userId,
                   $comment->status,
                   addslashes($comment->contents),
                   addslashes($comment->htmlContents),
                   $comment->id);
  return logged_query($query);
}

function db_insertComment($comment) {
  $query = sprintf("insert into Comment set " .
                   "DefinitionId = '%s', " .
                   "UserId = '%s', " .
                   "Status = '%s', " .
                   "Contents = '%s', " .
                   "HtmlContents = '%s'",
                   $comment->definitionId,
                   $comment->userId,
                   $comment->status,
                   addslashes($comment->contents),
                   addslashes($comment->htmlContents));
  return logged_query($query);
}

function db_getLexemHomonyms($lexem) {
  $unaccented = addslashes($lexem->unaccented);
  $query = "select * from lexems " .
    "where lexem_neaccentuat = '" . $unaccented . "' " .
    "and lexem_id != " . $lexem->id;
  return logged_query($query);
}

function db_getRecentLinkById($id) {
  $query = "select * from RecentLink where Id = '$id'";
  return db_fetchSingleRow(logged_query($query));
}

function db_getRecentLinksByUserId($userId) {
  $query = "select * from RecentLink where UserId = '$userId' " .
    "order by VisitDate desc";
  return logged_query($query);
}

function db_getRecentLinkByUserIdUrlText($userId, $url, $text) {
  $url = addslashes($url);
  $text = addslashes($text);
  $query = "select * from RecentLink " .
    "where UserId = '$userId' " .
    "and Url = '$url' " .
    "and Text = '$text' ";
  return db_fetchSingleRow(logged_query($query));
}

function db_insertRecentLink($rl) {
  $query = sprintf("insert into RecentLink set " .
                   "UserId = '%d', " .
                   "VisitDate = '%d', " .
                   "Url = '%s', " .
                   "Text = '%s'" ,
                   $rl->userId,
                   $rl->visitDate,
                   addslashes($rl->url),
                   addslashes($rl->text));
  return logged_query($query);
}

function db_updateRecentLink($rl) {
  $query = sprintf("update RecentLink set " .
                   "UserId = '%d', " .
                   "VisitDate = '%d', " .
                   "Url = '%s', " .
                   "Text = '%s' " .
                   "where Id = '%d'",
                   $rl->userId,
                   $rl->visitDate,
                   addslashes($rl->url),
                   addslashes($rl->text),
                   $rl->id);
  return logged_query($query);
}

function db_deleteRecentLink($recentLink) {
  $query = "delete from RecentLink where Id = " . $recentLink->id;
  logged_query($query);
}

function db_getModelTypeById($id) {
  $query = "select * from model_types where mt_id = '$id'";
  return db_fetchSingleRow(logged_query($query));
}

function db_getModelTypeByValue($value) {
  $value = addslashes($value);
  $query = "select * from model_types where mt_value = '$value'";
  return db_fetchSingleRow(logged_query($query));
}

function db_selectAllModelTypes() {
  $query = 'select * from model_types order by mt_value';
  return logged_query($query);
}

function db_selectAllCanonicalModelTypes() {
  $query = 'select * from model_types where mt_value = mt_canonical ' .
    'and mt_value != "T" ' .
    'order by mt_value';
  return logged_query($query);
}

function db_countModelsByModelType($mt) {
  $query = "select count(*) from models where model_type = '" . $mt->value
    . "'";
  return db_fetchInteger(logged_query($query));
}

function db_insertModelType($mt) {
  $query = sprintf("insert into model_types set " .
                   "mt_value = '%s', " .
                   "mt_descr = '%s'",
                   addslashes($mt->value),
                   addslashes($mt->description));
  return logged_query($query);
}

function db_updateModelType($mt) {
  $query = sprintf("update model_types set " .
                   "mt_value = '%s', " .
                   "mt_descr = '%s' " .
                   "where mt_id = '%d'",
                   addslashes($mt->value),
                   addslashes($mt->description),
                   $mt->id);
  return logged_query($query);
}

function db_insertModel($m) {
  $query = sprintf("insert into models set " .
                   "model_type = '%s', " .
                   "model_no = '%s', " .
                   "model_descr = '%s', " .
                   "model_exponent = '%s', " .
                   "model_flag = '%d'",
                   addslashes($m->modelType),
                   addslashes($m->number),
                   addslashes($m->description),
                   addslashes($m->exponent),
                   $m->flag
                   );
  return logged_query($query);
}

function db_updateModel($m) {
  $query = sprintf("update models set " .
                   "model_type = '%s', " .
                   "model_no = '%s', " .
                   "model_descr = '%s', " .
                   "model_exponent = '%s', " .
                   "model_flag = '%d' " .
                   "where model_id = '%d'",
                   addslashes($m->modelType),
                   addslashes($m->number),
                   addslashes($m->description),
                   addslashes($m->exponent),
                   $m->flag,
                   $m->id);
  return logged_query($query);
}

function db_deleteModel($model) {
  return logged_query("delete from models where model_id = {$model->id}");
}

function db_getModelDescriptionsByModelId($modelId) {
  $modelId = addslashes($modelId);
  $query = "select * from model_description " .
    "where md_model = '$modelId' " .
    "order by md_infl, md_variant, md_order ";
  return logged_query($query);
}

function db_getModelDescriptionsByModelIdInflId($modelId, $inflId) {
  $modelId = addslashes($modelId);
  $inflId = addslashes($inflId);
  $query = "select * from model_description " .
    "where md_model = '$modelId' " .
    "and md_infl = '$inflId' " .
    "order by md_variant, md_order ";
  return logged_query($query);
}

function db_insertModelDescription($md) {
  $query = sprintf("insert into model_description set " .
                   "md_model = '%d', " .
                   "md_infl = '%d', " .
                   "md_variant = '%d', " .
                   "md_order = '%d', " .
                   "md_transf = '%d', " .
                   "md_accent_shift = '%d', " .
                   "md_vowel = '%s'",
                   $md->modelId,
                   $md->inflectionId,
                   $md->variant,
                   $md->order,
                   $md->transformId,
                   $md->accentShift,
                   addslashes($md->accentedVowel));
  return logged_query($query);
}

function db_updateModelDescription($md) {
  $query = sprintf("update model_description set " .
                   "md_model = '%d', " .
                   "md_infl = '%d', " .
                   "md_variant = '%d', " .
                   "md_order = '%d', " .
                   "md_transf = '%d', " .
                   "md_accent_shift = '%d', " .
                   "md_vowel = '%s' " .
                   "where md_id = '%d'",
                   $md->modelId,
                   $md->inflectionId,
                   $md->variant,
                   $md->order,
                   $md->transformId,
                   $md->accentShift,
                   addslashes($$md->accentedVowel),
                   $md->id);
  return logged_query($query);
}

function db_deleteModelDescriptionsByModelInflection($modelId, $inflectionId) {
  $query = "delete from model_description where md_model = $modelId " .
    "and md_infl = $inflectionId";
  return logged_query($query);
}

function db_deleteModelDescriptionsByModel($modelId) {
  $query = "delete from model_description where md_model = $modelId";
  return logged_query($query);
}

function db_deleteModelType($modelType) {
  $query = "delete from model_types where mt_id = " . $modelType->id;
  logged_query($query);
}

function db_getInflectionById($id) {
  $query = "select * from inflections where infl_id = '$id'";
  return db_fetchSingleRow(logged_query($query));
}

function db_getParticipleInflection() {
  $query = "select * from inflections where infl_descr like '%participiu%'";
  return db_fetchSingleRow(logged_query($query));
}

function db_getInfinitiveInflection() {
  $query = "select * from inflections " .
    "where infl_descr like '%infinitiv prezent%'";
  return db_fetchSingleRow(logged_query($query));
}

function db_getLongInfinitiveInflection() {
  $query = "select * from inflections " .
    "where infl_descr like '%infinitiv lung%'";
  return db_fetchSingleRow(logged_query($query));
}

function db_getInflectionsForModelId($modelId) {
  $query = "select distinct md_infl from model_description " .
    "where md_model = $modelId order by md_infl";
  return db_getScalarArray(logged_query($query));
}

function db_getInflectionsByModelType($modelType) {
  $modelType = addslashes($modelType);
  $query = "select distinct inflections.* " .
    "from models, model_description, inflections " .
    "where md_model = model_id and md_infl = infl_id " .
    "and model_type = '$modelType' order by md_infl";
  return logged_query($query);
}

function db_selectAllInflections() {
  $query = 'select * from inflections order by infl_id';
  return logged_query($query);
}

function db_insertInflection($inf) {
  $query = sprintf("insert into inflections set " .
                   "infl_descr = '%s'",
                   addslashes($inf->description));
  return logged_query($query);
}

function db_updateInflection($inf) {
  $query = sprintf("update inflections set " .
                   "infl_descr = '%s' " .
                   "where infl_id = '%d'",
                   addslashes($inf->description),
                   $inf->id);
  return logged_query($query);
}

function db_getModelByTypeNumber($type, $number) {
  $type = addslashes($type);
  $number = addslashes($number);
  $query = "select * from models where model_type = '$type' " .
    "and model_no = '$number'";
  return db_fetchSingleRow(logged_query($query));
}

function db_getModelsByType($type) {
  $type = addslashes($type);
  $query = "select * from models where model_type = '$type' " .
    "order by cast(model_no as unsigned)";
  return logged_query($query);
}

function db_getModelById($id) {
  $query = "select * from models where model_id = $id";
  return db_fetchSingleRow(logged_query($query));
}

function db_selectAllModels() {
  $query = 'select * from models order by model_type, model_no';
  return logged_query($query);
}

function db_getLexemById($id) {
  $query = "select * from lexems where lexem_id = '$id'";
  return db_fetchSingleRow(logged_query($query));
}

function db_getLexemsByDefinitionId($definitionId) {
  $query = "select lexems.* from lexems, LexemDefinitionMap " .
    "where lexems.lexem_id = LexemDefinitionMap.LexemId " .
    "and LexemDefinitionMap.DefinitionId = '$definitionId'";
  return logged_query($query);
}

function db_getLexemsByUnaccented($unaccented) {
  $unaccented = addslashes($unaccented);
  $query = "select * from lexems where lexem_neaccentuat = '$unaccented'";
  return logged_query($query);
}

function db_getLexemsByForm($form) {
  $form = addslashes($form);
  $query = "select * from lexems where lexem_forma = '$form'";
  return logged_query($query);
}

function db_getLexemsByPartialUnaccented($name) {
  $name = addslashes($name);
  $query = "select * from lexems where lexem_neaccentuat like '$name%' " .
    "order by lexem_neaccentuat limit 10";
  return logged_query($query);  
}

function db_getLexemsByUnaccentedPartialDescription($name, $description) {
  $name = addslashes($name);
  $description = addslashes($description);
  $query = "select * from lexems where lexem_neaccentuat = '$name' " .
    "and lexem_descr like '$description%' " .
    "order by lexem_neaccentuat, lexem_descr limit 10";
  return logged_query($query);  
}

function db_getLexemsByUnaccentedDescription($unaccented, $description) {
  $unaccented = addslashes($unaccented);
  $description = addslashes($description);
  $query = "select * from lexems where lexem_neaccentuat = '$unaccented' " .
    "and lexem_descr = '$description'";
  return logged_query($query);  
}

function db_getLexemsByReverseSuffix($suffix, $excludeLexemId, $limit) {
  $query = "select * from lexems where lexem_invers like '$suffix%' " .
    "and lexem_model_type != 'T' " .
    "and lexem_id != $excludeLexemId " .
    "group by lexem_model_type, lexem_model_no " .
    "limit $limit";
  return logged_query($query);
}

function db_getLexemsByModel($modelType, $modelNumber) {
  $modelType = addslashes($modelType);
  $modelNumber = addslashes($modelNumber);
  $query = "select * from lexems where lexem_model_type = '$modelType' " .
    "and lexem_model_no = '$modelNumber' " .
    "order by lexem_neaccentuat";
  return logged_query($query);
}

function db_getLexemByCanonicalModelSuffix($modelType, $modelNumber, $suffix) {
  $modelType = addslashes($modelType);
  $modelNumber = addslashes($modelNumber);
  $query = "select lexems.* from lexems, model_types " .
    "where lexem_model_type = mt_value " .
    "and mt_canonical = '$modelType' " .
    "and lexem_model_no = '$modelNumber' " .
    "and lexem_invers like '$suffix%' " .
    "order by lexem_forma desc limit 1";
  return db_fetchSingleRow(logged_query($query));
}

function db_getLexemByUnaccentedCanonicalModel($unaccented, $modelType,
                                               $modelNumber) {
  $unaccented = addslashes($unaccented);
  $modelType = addslashes($modelType);
  $modelNumber = addslashes($modelNumber);
  $query = "select lexems.* from lexems, model_types " .
    "where lexem_model_type = model_types.mt_value " .
    "and model_types.mt_canonical = '$modelType' " .
    "and lexem_model_no = '$modelNumber' ".
    "and lexem_neaccentuat = '$unaccented' " .
    "limit 1";
  return db_fetchSingleRow(logged_query($query));
}

function db_getLexemsByCanonicalModel($modelType, $modelNumber) {
  $modelType = addslashes($modelType);
  $modelNumber = addslashes($modelNumber);
  $query = "select lexems.* from lexems, model_types " .
    "where lexem_model_type = model_types.mt_value " .
    "and model_types.mt_canonical = '$modelType' " .
    "and lexem_model_no = '$modelNumber'" .
    "order by lexem_neaccentuat";
  return logged_query($query);
}

function db_countLexems() {
  $query = "select count(*) from lexems";
  return db_fetchInteger(logged_query($query));
}

function db_countAssociatedLexems() {
  // same as select count(distinct LexemId) from LexemDefinitionMap,
  // only faster.
  $query = 'select count(*) from (select count(*) from LexemDefinitionMap ' .
    'group by LexemId) as someLabel';
  return db_fetchInteger(logged_query($query));
}

function db_getUnassociatedLexems() {
  $query = 'select * from lexems ' .
    'where lexem_id not in (select LexemId from LexemDefinitionMap) ' .
    'order by lexem_neaccentuat';
  return logged_query($query);
}

function db_selectAllLexems() {
  $query = 'select * from lexems';
  return logged_query($query);
}

function db_countTemporaryLexems() {
  $query = 'select count(*) from lexems where lexem_model_type = "T"';
  return db_fetchInteger(logged_query($query));
}

function db_getTemporaryLexems() {
  $query = 'select * from lexems where lexem_model_type = "T" ' .
    'order by lexem_neaccentuat limit 1000';
  return logged_query($query);
}

function db_countLexemsWithComments() {
  $query = 'select count(*) from lexems where lexem_comment != ""';
  return db_fetchInteger(logged_query($query));
}

function db_getLexemsWithComments() {
  $query = 'select * from lexems where lexem_comment != "" ' .
    'order by lexem_neaccentuat';
  return logged_query($query);
}

function db_countLexemsWithoutAccents() {
  $query = 'select count(*) from lexems where lexem_forma not rlike "\'" ' .
    'and not lexem_no_accent';
  return db_fetchInteger(logged_query($query));
}

function db_getLexemsWithoutAccents() {
  $query = 'select * from lexems where lexem_forma not rlike "\'" ' .
    'and not lexem_no_accent limit 1000';
  return logged_query($query);
}

function db_getRandomLexemsWithoutAccents($count) {
  $query = 'select * from lexems where lexem_forma not rlike "\'" ' .
    'and not lexem_no_accent order by rand() limit ' . $count;
  return logged_query($query);
}

function db_countAmbiguousLexems() {
  $query = "select count(distinct a.lexem_id) from lexems a, lexems b where a.lexem_model_type = 'T'" .
    "and a.lexem_id != b.lexem_id and a.lexem_neaccentuat = b.lexem_neaccentuat and a.lexem_descr = b.lexem_descr order by a.lexem_neaccentuat";
  return db_fetchInteger(logged_query($query));
}

function db_getAmbiguousLexems() {
  $query = "select distinct a.* from lexems a, lexems b where a.lexem_model_type = 'T'" .
    "and a.lexem_id != b.lexem_id and a.lexem_neaccentuat = b.lexem_neaccentuat and a.lexem_descr = b.lexem_descr order by a.lexem_neaccentuat";
  return logged_query($query);
}

function db_getParticiplesForVerbModel($modelNumber, $participleNumber,
                                       $partInflId) {
  $query = "select part.* from lexems part, wordlist, lexems infin " .
    "where infin.lexem_model_type = 'VT' " .
    "and infin.lexem_model_no = '$modelNumber' " .
    "and wl_lexem = infin.lexem_id " .
    "and wl_analyse = $partInflId " .
    "and part.lexem_neaccentuat = wl_neaccentuat " .
    "and part.lexem_model_type = 'A' " .
    "and part.lexem_model_no = '$participleNumber' " .
    "order by part.lexem_neaccentuat";
  return logged_query($query);
}

function db_getLexemsForScrabbleDownload() {
  $query = 'select * from lexems where lexem_is_loc ' .
    'order by lexem_neaccentuat';
  return logged_query($query);
}

function db_insertLexem($lexem) {
  $query = sprintf("insert into lexems set " .
                   "lexem_forma = '%s', " .
                   "lexem_neaccentuat = '%s', " .
                   "lexem_utf8_general = '%s', " .
                   "lexem_invers = '%s', " .
                   "lexem_descr = '%s', " .
                   "lexem_model_type = '%s', " .
                   "lexem_model_no = '%s', " .
                   "lexem_restriction = '%s', " .
                   "lexem_parse_info = '%s', " .
                   "lexem_comment = '%s', " .
                   "lexem_is_loc = '%d', " .
                   "lexem_no_accent = '%d', " .
                   "CreateDate = '%d', " .
                   "ModDate = '%d'",
                   addslashes($lexem->form),
                   addslashes($lexem->unaccented),
                   addslashes($lexem->unaccented),
                   addslashes($lexem->reverse),
                   addslashes($lexem->description),
                   addslashes($lexem->modelType),
                   addslashes($lexem->modelNumber),
                   addslashes($lexem->restriction),
                   addslashes($lexem->parseInfo),
                   addslashes($lexem->comment),
                   $lexem->isLoc,
                   $lexem->noAccent,
                   $lexem->createDate,
                   $lexem->modDate);
  return logged_query($query);
}

function db_updateLexem($lexem) {
  $query = sprintf("update lexems set " .
                   "lexem_forma = '%s', " .
                   "lexem_neaccentuat = '%s', " .
                   "lexem_utf8_general = '%s', " .
                   "lexem_invers = '%s', " .
                   "lexem_descr = '%s', " .
                   "lexem_model_type = '%s', " .
                   "lexem_model_no = '%s', " .
                   "lexem_restriction = '%s', " .
                   "lexem_parse_info = '%s', " .
                   "lexem_comment = '%s', " .
                   "lexem_is_loc = '%d', " .
                   "lexem_no_accent = '%d', " .
                   "CreateDate = '%d', " .
                   "ModDate = '%d' " .
                   "where lexem_id = '%d'",
                   addslashes($lexem->form),
                   addslashes($lexem->unaccented),
                   addslashes($lexem->unaccented),
                   addslashes($lexem->reverse),
                   addslashes($lexem->description),
                   addslashes($lexem->modelType),
                   addslashes($lexem->modelNumber),
                   addslashes($lexem->restriction),
                   addslashes($lexem->parseInfo),
                   addslashes($lexem->comment),
                   $lexem->isLoc,
                   $lexem->noAccent,
                   $lexem->createDate,
                   $lexem->modDate,
                   $lexem->id);
  return logged_query($query);
}

function db_deleteLexem($lexem) {
  $query = "delete from lexems where lexem_id = " . $lexem->id;
  logged_query($query);
}

function db_selectModelStatsWithSuffixes($modelType, $modelNumber) {
  $modelType = addslashes($modelType);
  $modelNumber = addslashes($modelNumber);

  $query = "select substring(lexem_invers, 1, 3) as s " .
    "from lexems, model_types " .
    "where lexem_model_type = mt_value " .
    "and mt_canonical = '$modelType' " .
    "and lexem_model_no = '$modelNumber' " .
    "group by s order by count(*) desc";
  return logged_query($query);
}

function db_selectSuffixesAndCountsForTemporaryLexems() {
  $query = "select reverse(substring(lexem_invers, 1, 4)) as s, " .
    "count(*) as c from lexems " .
    "where lexem_model_type = 'T' " .
    "group by s having c >= 5 order by c desc, s";
  return logged_query($query);
}

function db_countLabeledBySuffix($reverseSuffix) {
  $query = "select count(*) from lexems " .
    "where lexem_model_type != 'T' " .
    "and lexem_invers like '$reverseSuffix%'";
  return db_fetchInteger(logged_query($query));
}

function db_selectRestrictionsBySuffix($reverseSuffix, $tempModelId) {
  $query = "select lexem_restriction, count(*) as c  from lexems " .
    "where lexem_model != $tempModelId " .
    "and lexem_invers like '$reverseSuffix%' " .
    "group by lexem_restriction";
  return logged_query($query);
}

function db_selectModelsBySuffix($reverseSuffix) {
  $query = "select lexem_model_type, lexem_model_no, count(*) as c " .
    "from lexems " .
    "where lexem_model_type != 'T' " .
    "and lexem_invers like '$reverseSuffix%' " .
    "group by lexem_model_type, lexem_model_no order by c desc";
  return logged_query($query);
}

function db_selectTemporaryLexemsBySuffix($reverseSuffix) {
  $query = "select * from lexems where lexem_model_type = 'T' " .
    "and lexem_invers like '$reverseSuffix%' " .
    "order by lexem_neaccentuat limit 20";
  return logged_query($query);
}

function db_getLexemDefinitionMapByLexemIdDefinitionId($lexemId,
                                                       $definitionId) {
  $query = "select * from LexemDefinitionMap " .
    "where LexemId = '$lexemId' " .
    "and DefinitionId = '$definitionId'";
  return db_fetchSingleRow(logged_query($query));
}

function db_getLexemDefinitionMapsByLexemId($lexemId) {
  $query = "select * from LexemDefinitionMap " .
    "where LexemId = '$lexemId'";
  return logged_query($query);
}

function db_getLexemDefinitionMapsByDefinitionId($definitionId) {
  $query = "select * from LexemDefinitionMap " .
    "where DefinitionId = '$definitionId'";
  return logged_query($query);
}

function db_insertLexemDefinitionMap($ldm) {
  $query = sprintf("insert into LexemDefinitionMap set " .
                   "LexemId = '%d', " .
                   "DefinitionId = '%d' ",
                   $ldm->lexemId,
                   $ldm->definitionId);
  return logged_query($query);
}

function db_updateLexemDefinitionMap($ldm) {
  $query = sprintf("update LexemDefinitionMap set " .
                   "LexemId = '%d', " .
                   "DefinitionId = '%d' " .
                   "where Id = '%d'",
                   $ldm->lexemId,
                   $ldm->definitionId,
                   $ldm->id);
  return logged_query($query);
}

function db_deleteLexemDefinitionMap($ldm) {
  logged_query("delete from LexemDefinitionMap where Id = '" . $ldm->id . "'");
}

function db_deleteLexemDefinitionMapsByDefinitionId($definitionId) {
  $query = "delete from LexemDefinitionMap where DefinitionId = $definitionId";
  logged_query($query);
}

function db_deleteLexemDefinitionMapsByLexemId($lexemId) {
  $query = "delete from LexemDefinitionMap where LexemId = $lexemId";
  logged_query($query);
}

function db_deleteLexemDefinitionMapByLexemIdDefinitionId($lexemId,
                                                          $definitionId) {
  $query = "delete from LexemDefinitionMap " .
    "where LexemId = '$lexemId' " .
    "and DefinitionId = '$definitionId'";
  logged_query($query);
}

function db_deleteAllLexemDefinitionMaps() {
  $query = "delete from LexemDefinitionMap";
  logged_query($query);
}

function db_getTransformById($id) {
  $query = "select * from transforms where transf_id = '$id'";
  return db_fetchSingleRow(logged_query($query));
}

function db_getTransformByFromTo($from, $to) {
  $query = sprintf("select * from transforms where transf_from = '%s' " .
                   "and transf_to = '%s'",
                   addslashes($from),
                   addslashes($to));
  return db_fetchSingleRow(logged_query($query));
}

function db_insertTransform($transform) {
  $query = sprintf("insert into transforms set " .
                   "transf_from = '%s', " .
                   "transf_to = '%s', " .
                   "transf_descr = '%s'",
                   addslashes($transform->from),
                   addslashes($transform->to),
                   addslashes($transform->description));
  return logged_query($query);
}

function db_updateTransform($transform) {
  $query = sprintf("update transforms set " .
                   "transf_from = '%s', " .
                   "transf_to = '%s', " .
                   "transf_descr = '%s' " .
                   "where transf_id = '%d'",
                   addslashes($transform->from),
                   addslashes($transform->to),
                   addslashes($transform->description),
                   $transform->id);
  return logged_query($query);
}

function db_getWordListsByLexemId($lexemId) {
  $query = "select * from wordlist where wl_lexem = $lexemId " .
    "order by wl_analyse, wl_variant";
  return logged_query($query);
}

function db_getWordListByLexemIdInflectionId($lexemId, $inflectionId) {
  $query = "select * from wordlist where wl_lexem = $lexemId " .
    "and wl_analyse = $inflectionId";
  return logged_query($query);
}

function db_getWordListsByUnaccented($unaccented) {
  $unaccented = addslashes($unaccented);
  $query = "select * from wordlist where wl_neaccentuat = '$unaccented'";
  return logged_query($query);
}

function db_insertWordList($wl) {
  $query = sprintf("insert into wordlist set " .
                   "wl_form = '%s', " .
                   "wl_neaccentuat = '%s', " .
                   "wl_utf8_general = '%s', " .
                   "wl_lexem = '%d', " .
                   "wl_analyse = '%d', " .
                   "wl_variant = '%d'",
                   addslashes($wl->form),
                   addslashes($wl->unaccented),
                   addslashes($wl->unaccented),
                   $wl->lexemId,
                   $wl->inflectionId,
		   $wl->variant);
  return logged_query($query);
}

function db_deleteWordListsByLexemId($lexemId) {
  $lexemId = addslashes($lexemId);
  $query = "delete from wordlist where wl_lexem = '$lexemId'";
  return logged_query($query);
}

function db_getParticipleModelByVerbModel($verbModel) {
  $verbModel = addslashes($verbModel);
  $query = "select * from participle_models " .
    "where pm_verb_model = '$verbModel'";
  return db_fetchSingleRow(logged_query($query));
}

function db_insertParticipleModel($pm) {
  $query = sprintf("insert into participle_models set " .
                   "pm_verb_model = '%s', " .
                   "pm_participle_model = '%s'",
                   addslashes($pm->verbModel),
                   addslashes($pm->participleModel));
  return logged_query($query);
}

function db_updateParticipleModel($pm) {
  $query = sprintf("update participle_models set " .
                   "pm_verb_model = '%s', " .
                   "pm_participle_model = '%s' " .
                   "where pm_id = '%d'",
                   addslashes($pm->verbModel),
                   addslashes($pm->participleModel),
                   $pm->id);
  return logged_query($query);
}

function db_deleteParticipleModel($pm) {
  return logged_query("delete from participle_models where pm_id = {$pm->id}");
}

function db_updateParticipleModelVerb($modelNumber, $newModelNumber) {
  $query = sprintf("update participle_models set " .
                   "pm_verb_model = '%s' " .
                   "where pm_verb_model = '%s'",
                   addslashes($newModelNumber),
                   addslashes($modelNumber)
                   );
  logged_query($query);
}

function db_updateParticipleModelAdjective($modelNumber, $newModelNumber) {
  $query = sprintf("update participle_models set " .
                   "pm_participle_model = '%s' " .
                   "where pm_participle_model = '%s'",
                   addslashes($newModelNumber),
                   addslashes($modelNumber)
                   );
  logged_query($query);
}

function db_getFullTextIndexesByLexemIds($lexemIds) {
  $query = "select distinct DefinitionId from FullTextIndex " .
    "where LexemId in ($lexemIds) order by DefinitionId";
  return db_getScalarArray(logged_query($query));
}

function db_getPositionsByLexemIdsDefinitionId($lexemIds, $defId) {
  $query = "select distinct Position from FullTextIndex " .
    "where LexemId in ($lexemIds) " .
    "and DefinitionId = $defId " .
    "order by Position";
  return db_getScalarArray(logged_query($query));
}

function db_insertFullTextIndex($fti) {
  $query = sprintf("insert into FullTextIndex set " .
                   "LexemId = %d, " .
                   "InflectionId = %d, " .
                   "DefinitionId = %d, " .
                   "Position = %d ",
                   $fti->lexemId,
                   $fti->inflectionId,
                   $fti->definitionId,
                   $fti->position);
  return logged_query($query);
}

function db_getNumMetRestrictions($restr, $inflId) {
  $restr = addslashes($restr);
  $query = "select count(*) from constraints " .
    "where locate(constr_id, '$restr') > 0 " .
    "and infl_id = $inflId";
  return db_fetchInteger(logged_query($query));
}

/****************************** Sources ******************************/

function db_selectAllContribSources() {
  return logged_query("select * from Source where CanContribute");
}

function db_selectAllModeratorSources() {
  return logged_query("select * from Source where CanModerate");
}

function db_selectAllSources() {
  return logged_query("select * from Source");
}

function db_getSourceById($id) {
  $query = "select * from Source where Id = '$id'";
  return db_fetchSingleRow(logged_query($query));
}

function db_getSourceByShortName($shortName) {
  $shortName = addslashes($shortName);
  $query = "select * from Source where ShortName = '$shortName'";
  return db_fetchSingleRow(logged_query($query));
}

?>
