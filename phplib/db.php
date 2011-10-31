<?php

function db_init() {
  $functestFile = pref_getSectionPreference('functest', 'functestLockFile');
  if ($functestFile && file_exists($functestFile)) {
    $db = NewADOConnection(pref_getSectionPreference('functest', 'functestDatabase'));
  } else {
    $db = NewADOConnection(pref_getServerPreference('database'));
  }
  if (!$db) {
    die("Connection failed");
  }
  ADOdb_Active_Record::SetDatabaseAdapter($db);
  $db->Execute('set names utf8');
  $GLOBALS['db'] = $db;
  // $db->debug = true; //just for debug
}

function db_execute($query) {
  return $GLOBALS['db']->execute($query);
}

function db_changeDatabase($dbName) {
  $dbName = addslashes($dbName);
  db_init();
  return logged_query("use $dbName");
}

/**
 * Returns an array mapping user, password, host and database to their respective values.
 **/
function db_splitDsn() {
  $result = array();
  $dsn = pref_getServerPreference('database');
  $prefix = 'mysql://';
  assert(StringUtil::startsWith($dsn, $prefix));
  $dsn = substr($dsn, strlen($prefix));

  $parts = preg_split("/[:@\/]/", $dsn);
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

function db_fetchSingleRow($result) {
  if ($result) {
    $row = mysql_fetch_assoc($result);
    mysql_free_result($result);
    return $row;
  } else {
    return NULL;
  }
}

function db_getArray($recordSet) {
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

function db_getObjectsMapById($obj, $dbResult) {
  $class = get_class($obj);
  $result = array();
  while (!$dbResult->EOF) {
    $x = new $class;
    $x->set($dbResult->fields);
    $result[$x->id] = $x;
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
  $statements = explode(";\n", $statements);
  foreach ($statements as $statement) {
    if (trim($statement) != '') {
      logged_query($statement);
    }
  }
}

?>
