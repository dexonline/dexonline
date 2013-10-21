<?php

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
    $lvs = Config::getLocVersions();
    if ($versionName == $lvs[0]->name || !$versionName) {
      $dbInfo = db_splitDsn();
      $dbName = $dbInfo['database'];
    } else {
      $lv = new LocVersion();
      $lv->name = $versionName;
      $dbName = Config::get('global.mysql_loc_prefix') . $lv->getDbName();
    }
    db_changeDatabase($dbName);
  }
}

?>
