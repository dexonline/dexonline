<?php

class LocVersion {
  public $name;
  public $freezeTimestamp;

  function getDate() {
    if (!$this->freezeTimestamp) {
      return 'curentă';
    } else {
      return date('m/d/Y', $this->freezeTimestamp);
    }
  }

  function getDbName() {
    return str_replace('.', '_', $this->name);
  }

  /* Returns the SQL database name on success, false on failure */
  static function changeDatabase($versionName) {
    $lvs = Config::getLocVersions();
    if ($versionName == $lvs[0]->name || !$versionName) {
      $dbName = DB::$database;
    } else {
      $lv = new LocVersion();
      $lv->name = $versionName;
      $dbName = Config::get('global.mysql_loc_prefix') . $lv->getDbName();
    }

    try {
      DB::changeDatabase($dbName);
      return $dbName;
    } catch (PDOException $e) {
      return false;
    }
  }
}
