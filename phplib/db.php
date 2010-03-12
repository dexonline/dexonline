<?

function db_init() {
  $db = NewADOConnection(pref_getServerPreference('database'));
  if (!$db) {
    die("Connection failed");
  }
  ADOdb_Active_Record::SetDatabaseAdapter($db);
  $db->Execute('set names utf8');
  $GLOBALS['db'] = $db;
  // $db->debug = true;
}

function db_execute($query) {
  return $GLOBALS['db']->execute($query);
}

function db_changeDatabase($dbName) {
  $dbName = addslashes($dbName);
  return logged_query("use $dbName");
}

/**
 * Returns an array mapping user, password, host and database to their respective values.
 **/
function db_splitDsn() {
  $result = array();
  $dsn = pref_getServerPreference('database');
  $prefix = 'mysql://';
  assert(text_startsWith($dsn, $prefix));
  $dsn = substr($dsn, strlen($prefix));

  $parts = split("[:@/]", $dsn);
  assert(count($parts) == 3 || count($parts) == 4);

  if (count($parts) == 4) {
    $result['user'] = $parts[0];
    $result['password'] = $parts[1];
    $result['host'] = $parts[2];
    $result['database'] = $parts[3];
  } else {
    $result['user'] = $parts[0];
    $result['host'] = $parts[1];
    $result['database'] = $parts[2];
    $result['password'] = '';
  }
  return $result;
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

function db_getScalarArray($recordSet) {
  $result = array();
  while (!$recordSet->EOF) {
    $result[] = $recordSet->fields[0];
    $recordSet->MoveNext();
  }
  return $result;
}

function db_getObjects($obj, $dbResult) {
  $class = get_class($obj);
  $result = array();
  while (!$dbResult->EOF) {
    $x = new $class;
    $x->set($dbResult->fields);
    $result[] = $x;
    $dbResult->MoveNext();
  }
  return $result;
}

// One-line syntactic sugar for find()
function db_find($obj, $where) {
  return $obj->find($where);
}

function db_getSingleValue($query) {
  $recordSet = db_execute($query);
  return $recordSet->fields[0];
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
      $message .= "Verificați că parantezele folosite sunt închise corect.";
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

function db_tableExists($tableName) {
  return db_fetchSingleRow(logged_query("show tables like '$tableName'")) !== false;
}

function db_executeSqlFile($fileName) {
  $statements = file_get_contents($fileName);
  $statements = explode(';', $statements);
  foreach ($statements as $statement) {
    if (trim($statement) != '') {
      logged_query($statement);
    }
  }
}

function db_searchRegexp($regexp, $hasDiacritics, $sourceId) {
  $field = $hasDiacritics ? 'lexem_neaccentuat' : 'lexem_utf8_general';
  $sourceClause = $sourceId ? "and Definition.sourceId = $sourceId " : '';
  $sourceJoin = $sourceId ?  "join LexemDefinitionMap " .
    "on lexem_id = LexemDefinitionMap.lexemId " .
    "join Definition on LexemDefinitionMap.definitionId = Definition.id " : '';
  $query = "select * from lexems " .
    $sourceJoin .
    "where $field $regexp " .
    $sourceClause .
    "order by lexem_neaccentuat limit 1000";
  return logged_query($query);
}

function db_countRegexpMatches($regexp, $hasDiacritics, $sourceId) {
  $field = $hasDiacritics ? 'lexem_neaccentuat' : 'lexem_utf8_general';
  $sourceClause = $sourceId ? "and Definition.sourceId = $sourceId " : '';
  $sourceJoin = $sourceId ?  "join LexemDefinitionMap " .
    "on lexem_id = LexemDefinitionMap.lexemId " .
    "join Definition on LexemDefinitionMap.definitionId = Definition.id " : '';
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

function db_searchInflectedForms($cuv, $hasDiacritics) {
  $field = $hasDiacritics ? 'formNoAccent' : 'formUtf8General';
  $query = "select distinct lexems.* from InflectedForm, lexems " .
    "where lexemId = lexem_id and $field = '$cuv' " .
    "order by lexem_neaccentuat";
  return logged_query($query);
}

function db_searchApproximate($cuv, $hasDiacritics) {
  $field = $hasDiacritics ? 'lexem_neaccentuat' : 'lexem_utf8_general';
  $query = "select * from lexems " .
    "where dist2($field, '$cuv') " .
    "order by lexem_neaccentuat";
  return logged_query($query);
}

function db_selectTop() {
  return logged_query("select nick, count(*) as NumDefinitions, " .
                      "sum(length(internalRep)) as NumChars, " .
                      "max(createDate) as Timestamp " .
                      "from Definition, User " .
                      "where userId = user.id " .
                      "and status = 0 " .
                      "group by nick");
}

function db_getLexemsByMinModDate($modDate) {
  $query = "select Definition.id, lexem_neaccentuat " .
    "from Definition force index(modDate), LexemDefinitionMap, lexems " .
    "where Definition.id = LexemDefinitionMap.definitionId " .
    "and LexemDefinitionMap.lexemId = lexem_id " .
    "and Definition.status = 0 " .
    "and Definition.modDate >= $modDate " .
    "order by Definition.modDate, Definition.id";
  return logged_query($query);
}

function db_getUpdate3LexemIds($modDate) {
  // Do not report deleted / pending definitions the first time this script is invoked
  $statusClause = $modDate ? "" : " and status = 0";
  $query = "select Definition.id, lexemId " .
    "from Definition force index(modDate), LexemDefinitionMap " .
    "where Definition.id = definitionId " .
    "and Definition.modDate >= $modDate $statusClause " .
    "order by Definition.modDate, Definition.id";
  return logged_query($query);
}

function db_getUpdate3Lexems($modDate) {
  $query = "select * from lexems " .
    "where ModDate >= '$modDate' " .
    "order by ModDate, lexem_id";
  return logged_query($query);
}

function db_updateLexemModDate($lexemId, $modDate) {
  $query = sprintf("update lexems set ModDate = '$modDate' " .
                   "where lexem_id = '$lexemId'");
  return logged_query($query);
}

function db_getLexemHomonyms($lexem) {
  $unaccented = addslashes($lexem->unaccented);
  $query = "select * from lexems " .
    "where lexem_neaccentuat = '" . $unaccented . "' " .
    "and lexem_id != " . $lexem->id;
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
  $query = "select lexems.* from lexems, LexemDefinitionMap where lexem_id = lexemId and definitionId = '$definitionId'";
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
  $query = "select lexems.* from lexems, ModelType " .
    "where lexem_model_type = code " .
    "and canonical = '$modelType' " .
    "and lexem_model_no = '$modelNumber' " .
    "and lexem_invers like '$suffix%' " .
    "order by lexem_forma desc limit 1";
  return db_fetchSingleRow(logged_query($query));
}

function db_getLexemByUnaccentedCanonicalModel($unaccented, $modelType, $modelNumber) {
  $unaccented = addslashes($unaccented);
  $modelType = addslashes($modelType);
  $modelNumber = addslashes($modelNumber);
  $query = "select lexems.* from lexems, ModelType " .
    "where lexem_model_type = code " .
    "and canonical = '$modelType' " .
    "and lexem_model_no = '$modelNumber' ".
    "and lexem_neaccentuat = '$unaccented' " .
    "limit 1";
  return db_fetchSingleRow(logged_query($query));
}

function db_getLexemsByCanonicalModel($modelType, $modelNumber) {
  $modelType = addslashes($modelType);
  $modelNumber = addslashes($modelNumber);
  $query = "select lexems.* from lexems, ModelType " .
    "where lexem_model_type = code " .
    "and canonical = '$modelType' " .
    "and lexem_model_no = '$modelNumber'" .
    "order by lexem_neaccentuat";
  return logged_query($query);
}

function db_countLexems() {
  $query = "select count(*) from lexems";
  return db_fetchInteger(logged_query($query));
}

function db_countAssociatedLexems() {
  // same as select count(distinct lexemId) from LexemDefinitionMap, only faster.
  $query = 'select count(*) from (select count(*) from LexemDefinitionMap group by lexemId) as someLabel';
  return db_fetchInteger(logged_query($query));
}

function db_getUnassociatedLexems() {
  $query = 'select * from lexems where lexem_id not in (select lexemId from LexemDefinitionMap) order by lexem_neaccentuat';
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
  $query = 'select * from lexems where lexem_model_type = "T" order by lexem_neaccentuat';
  return logged_query($query);
}

function db_getTemporaryLexemsFromSource($sourceId) {
  $query = "select distinct lexems.* from lexems, LexemDefinitionMap, Definition " .
    "where lexems.lexem_id = LexemDefinitionMap.lexemId and LexemDefinitionMap.definitionId = Definition.id " .
    "and Definition.status = 0 and Definition.sourceId = $sourceId and lexem_model_type = 'T' " .
    "order by lexem_neaccentuat";
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
  $query = "select count(*) from (select lexems.*, count(*) as c from lexems where lexem_descr = '' group by lexem_forma having c > 1) as t1";
  return db_fetchInteger(logged_query($query));
}

function db_getAmbiguousLexems() {
  $query = "select lexems.*, count(*) as c from lexems where lexem_descr = '' group by lexem_forma having c > 1";
  return logged_query($query);
}

function db_getParticiplesForVerbModel($modelNumber, $participleNumber, $partInflId) {
  $query = "select part.* from lexems part, InflectedForm, lexems infin " .
    "where infin.lexem_model_type = 'VT' " .
    "and infin.lexem_model_no = '$modelNumber' " .
    "and lexemId = infin.lexem_id " .
    "and inflectionId = $partInflId " .
    "and part.lexem_neaccentuat = formNoAccent " .
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
    "from lexems, ModelType " .
    "where lexem_model_type = code " .
    "and canonical = '$modelType' " .
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

function db_selectPluralLexemsByModelType($modelType) {
  if ($modelType == 'T') {
    $whereClause = 'lexem_model_type = "T"';
  } else if ($modelType) {
    $whereClause = "lexem_model_type = '{$modelType}' and lexem_restriction like '%P%'";
  } else {
    $whereClause = '(lexem_model_type = "T") or (lexem_model_type in ("M", "F", "N") and lexem_restriction like "%P%")';
  }
  return logged_query("select * from lexems where {$whereClause} order by lexem_neaccentuat");
}

?>
