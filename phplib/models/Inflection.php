<?php

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

  public static function mapById($inflections) {
    $result = array();
    foreach ($inflections as $i) {
      $result[$i->id] = $i;
    }
    return $result;
  }

  public function delete() {
    db_execute("update Inflection set rank = rank - 1 where modelType = '{$this->modelType}' and rank > {$this->rank}");
    parent::delete();
  }
}

?>
