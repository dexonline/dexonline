<?php

class Inflection extends BaseObject {
  public static $_table = 'Inflection';

  public static function loadParticiple() {
    return Model::factory('Inflection')->where_like('description', '%participiu%')->find_one();
  }

  public static function loadLongInfinitive() {
    return Model::factory('Inflection')->where_like('description', '%infinitiv lung%')->find_one();
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
