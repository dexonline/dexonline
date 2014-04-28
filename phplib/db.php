<?php

function db_init() {
  $functestFile = Config::get('functest.functestLockFile');
  if ($functestFile && file_exists($functestFile)) {
    $dsn = Config::get('functest.functestDatabase');
  } else {
    $dsn = Config::get('global.database');
  }
  $parts = db_splitDsn($dsn);
  ORM::configure(sprintf("mysql:host=%s;dbname=%s", $parts['host'], $parts['database']));
  ORM::configure('username', $parts['user']);
  ORM::configure('password', $parts['password']);
  // This allows var_dump(ORM::get_query_log()) or var_dump(ORM::get_last_query())
  // ORM::configure('logging', true);
  ORM::configure('driver_options', array(PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8',
                                         PDO::MYSQL_ATTR_LOCAL_INFILE => true));
}

// Returns a DB result set that you can iterate with foreach($result as $row)
function db_execute($query, $fetchStyle = PDO::FETCH_BOTH) {
  DebugInfo::resetClock();
  $result = ORM::get_db()->query($query, $fetchStyle);
  DebugInfo::stopClock("Low-level query: $query");
  return $result;
}

/**
 * There is a bug with running "load data local infile" from PDO:
 * http://www.yiiframework.com/forum/index.php/topic/33612-load-data-local-infile-forbidden/
 * We can still run that statement from the command line.
 * This function allows us to do that until we upgrade to PHP 5.4.
 **/
function db_executeFromOS($query) {
  $dsn = Config::get('global.database');
  $parts = db_splitDsn($dsn);
  $command = sprintf("mysql -u %s %s %s -e \"{$query}\"",
                     $parts['user'],
                     $parts['password'] ? ("-p " . $parts['password']) : '',
                     $parts['database']);
  OS::executeAndAssert($command);
}

function db_changeDatabase($dbName) {
  $dbName = addslashes($dbName);
  db_init();
  return db_execute("use $dbName");
}

/**
 * Returns an array mapping user, password, host and database to their respective values.
 **/
function db_splitDsn($dsn = null) {
  $result = array();
  if (!$dsn) {
    $dsn = Config::get('global.database');
  }
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

// Idiorm has no way of returning a result set, so we do this at the PDO level; otherwise we could end up with huge arrays of Models.
// Example: full text search of the word 'mică'
function db_getArray($query) {
  $dbResult = ORM::get_db()->query($query);
  $results = array();
  foreach ($dbResult as $row) {
    $results[] = $row[0];
  }
  return $results;
}

function db_getArrayOfRows($query) {
  $dbResult = ORM::get_db()->query($query);
  $results = array();
  foreach ($dbResult as $row) {
    $results[] = $row;
  }
  return $results;
}

// Normally you can do this with Idiorm's ->count() method, but that doesn't work for complicated queries
// E.g. select count(distinct Lexem.id) from ...
function db_getSingleValue($query) {
  $recordSet = db_execute($query);
  $row = $recordSet->fetch();
  return $row[0];
}

function db_tableExists($tableName) {
  $r = ORM::for_table($tableName)->raw_query("show tables like '$tableName'", null)->find_one();
  return ($r !== false);
}

function db_executeSqlFile($fileName) {
  $statements = file_get_contents($fileName);
  $statements = explode(";\n", $statements);
  foreach ($statements as $statement) {
    $statement = trim($statement);
    if ($statement != '') {
      db_execute($statement);
    }
  }
}

?>
