<?php

require_once 'third-party/idiorm/idiorm.php';
require_once 'third-party/idiorm/paris.php';

class DB {
  const INIT_COMMAND =
    'set names utf8mb4, ' .
    'sql_mode = (select replace(@@sql_mode, "ONLY_FULL_GROUP_BY", ""))';

  static $dsn;
  static $user;
  static $password;
  static $host;
  static $database;

  static function init() {
    self::$dsn = Config::TEST_MODE
      ? Config::TEST_DATABASE
      : Config::DATABASE;
    $parts = self::splitDsn(self::$dsn);
    self::$user = $parts['user'];
    self::$password = $parts['password'];
    self::$host = $parts['host'];
    self::$database = $parts['database'];

    ORM::configure(sprintf('mysql:host=%s;dbname=%s', self::$host, self::$database));
    ORM::configure('username', self::$user);
    ORM::configure('password', self::$password);
    // This allows var_dump(ORM::get_query_log()) or var_dump(ORM::get_last_query())
    // ORM::configure('logging', true);
    ORM::configure('driver_options', [
      PDO::MYSQL_ATTR_INIT_COMMAND => self::INIT_COMMAND,
    ]);
  }

  // When false, PDO returns result sets and does not load the results in memory.
  static function setBuffering($boolean) {
    ORM::get_db()->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, $boolean);
  }

  // Returns a DB result set that you can iterate with foreach($result as $row)
  static function execute($query, $fetchStyle = PDO::FETCH_BOTH) {
    DebugInfo::resetClock();
    $result = ORM::get_db()->query($query, $fetchStyle);
    DebugInfo::stopClock("Low-level query: $query");
    return $result;
  }

  static function executeSqlFile($filename, $database = null) {
    $database = $database ?? self::$database;

    $cat = OS::getCatCommand();
    $filename = realpath(Str::portable($filename));
    $command = sprintf(
      "{$cat} {$filename} | mysql -h %s -u %s %s %s",
      self::$host,
      self::$user,
      $database,
      self::$password ? ("-p" . self::$password) : '');
    OS::executeAndAssert($command);
  }

  /**
   * Extracts the user, password, host and database from a DSN
   **/
  static function splitDsn($dsn = null) {
    if (!$dsn) {
      $dsn = self::$dsn;
    }
    $prefix = 'mysql://';
    assert(Str::startsWith($dsn, $prefix));
    $dsn = substr($dsn, strlen($prefix));

    $parts = preg_split("/[:@\/]/", $dsn);
    assert(count($parts) == 3 || count($parts) == 4);

    if (count($parts) == 4) {
      return [
        'user' => $parts[0],
        'password' => $parts[1],
        'host' => $parts[2],
        'database' => $parts[3],
      ];
    } else {
      return [
        'user' => $parts[0],
        'password' => '',
        'host' => $parts[1],
        'database' => $parts[2],
      ];
    }
  }

  // Idiorm has no way of returning a result set, so we do this at the PDO level;
  // otherwise we could end up with huge arrays of Models.
  // Example: full text search of the word 'mică'
  static function getArray($query) {
    DebugInfo::resetClock();
    $dbResult = ORM::get_db()->query($query);
    DebugInfo::stopClock("Low-level query: $query");
    $results = [];
    foreach ($dbResult as $row) {
      $results[] = $row[0];
    }
    return $results;
  }

  /**
   * Loads the objects with the given IDs in a single query (which returns
   * them in increasing ID order), then resorts them in the order given by
   * $ids.
   */
  static function loadInIdOrder(string $class, array $ids) {
    $objects = Model::factory($class)
      ->where_in('id', $ids ?: [ 0 ])
      ->find_many();

    // Resort the objects in order of $ids
    $map = Util::mapById($objects);
    $results = [];
    foreach ($ids as $id) {
      $results[] = $map[$id];
    }

    return $results;
  }

  static function getArrayOfRows($query, $fetchStyle = PDO::FETCH_BOTH) {
    $dbResult = ORM::get_db()->query($query, $fetchStyle);
    $results = [];
    foreach ($dbResult as $row) {
      $results[] = $row;
    }
    return $results;
  }

  // Normally you can do this with Idiorm's ->count() method, but that doesn't work
  // for complicated queries, e.g. select count(distinct Lexeme.id) from ...
  static function getSingleValue($query) {
    $recordSet = self::execute($query);
    $row = $recordSet->fetch();
    return $row[0];
  }

  static function tableExists($tableName) {
    $r = ORM::for_table($tableName)->raw_query("show tables like '$tableName'")->find_one();
    return ($r !== false);
  }

}
