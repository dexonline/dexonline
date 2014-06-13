<?php

// Used to be named Model, but that name collided with Idiorm's class.
class FlexModel extends BaseObject {
  public static $_table = 'Model';

  public static function create($modelType = '', $number = '', $description = '', $exponent = '') {
    $fm = Model::factory('FlexModel')->create();
    $fm->modelType = $modelType;
    $fm->number = $number;
    $fm->description = $description;
    $fm->exponent = $exponent;
    $fm->flag = 0;
    return $fm;
  }

  public static function loadByType($type) {
    $type = ModelType::canonicalize($type);
    // Need a raw query here because order_by_asc() expects a field, nothing more
    return Model::factory('FlexModel')
      ->raw_query("select * from Model where modelType = '{$type}' order by cast(number as unsigned), number", null)->find_many();
  }

  public static function loadCanonicalByTypeNumber($type, $number) {
    $type = ModelType::canonicalize($type);
    return Model::factory('FlexModel')->where('modelType', $type)->where('number', $number)->find_one();
  }

  public function delete() {
    $mds = ModelDescription::get_by_modelId($this->id);
    foreach ($mds as $md) {
      $md->delete();
    }
    if ($this->modelType == 'V') {
      $pm = ParticipleModel::loadByVerbModel($this->number);
      $pm->delete();
    }
    parent::delete();
  }

  /** Returns an array containing the type, number and restrictions **/
  public static function splitName($name) {
    $result = array();
    $len = strlen($name);
    $i = 0;
    while ($i < $len && !ctype_digit($name[$i])) {
      $i++;
    }
    $result[] = substr($name, 0, $i);
    $j = $i;
    while ($j < $len && ctype_digit($name[$j])) {
      $j++;
    }
    $result[] = substr($name, $i, $j - $i);
    $result[] = substr($name, $j);
    return $result;
  }

  public function __toString() {
    return $this->modelType . $this->number;
  }
}

?>
