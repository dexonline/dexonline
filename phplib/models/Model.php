<?php

class Model extends BaseObject {
  function __construct($modelType = '', $number = '', $description = '', $exponent = '') {
    parent::__construct();
    $this->modelType = $modelType;
    $this->number = $number;
    $this->description = $description;
    $this->exponent = $exponent;
    $this->flag = 0;
  }

  public static function get($where) {
    $obj = new Model();
    $obj->load($where);
    return $obj->id ? $obj : null;
  }

  public static function loadByType($type) {
    $type = ModelType::canonicalize($type);
    return db_find(new Model(), "modelType = '{$type}' order by cast(number as unsigned)");
  }

  public static function loadCanonicalByTypeNumber($type, $number) {
    $type = ModelType::canonicalize($type);
    return Model::get("modelType = '{$type}' and number = '{$number}'");
  }

  public function delete() {
    db_execute("delete from ModelDescription where modelId = '{$this->id}'");
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
