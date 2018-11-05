<?php

class DB {
  static $dsn;
  static $user;
  static $password;
  static $host;
  static $database;

  static function init() {
    self::$dsn = Config::get('testing.enabled')
               ? Config::get('testing.database')
               : Config::get('global.database');
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
      PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8',
      PDO::MYSQL_ATTR_LOCAL_INFILE => true,
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

  /**
   * There is a bug with running "load data local infile" from PDO:
   * http://www.yiiframework.com/forum/index.php/topic/33612-load-data-local-infile-forbidden/
   * We can still run that statement from the command line.
   * This function allows us to do that until we upgrade to PHP 5.4.
   *
   * NOTE: The query may not contain quotes (') due to escaping issues.
   **/
  static function executeFromOS($query) {
    $query = str_replace("\n", ' ', $query);

    // Skip the username/password here to avoid a Percona warning.
    // Place them in my.cnf (remeber this command runs as the webserver user).
    $command = sprintf("mysql -u %s -h %s %s -e '%s'",
                       self::$user, self::$host, self::$database, $query);
    OS::executeAndAssert($command);
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
    $dbResult = ORM::get_db()->query($query);
    $results = [];
    foreach ($dbResult as $row) {
      $results[] = $row[0];
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
