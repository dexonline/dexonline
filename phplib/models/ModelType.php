<?php

class ModelType extends BaseObject {
  public static function get($where) {
    $obj = new ModelType();
    $obj->load($where);
    return $obj->id ? $obj : null;
  }

  public static function loadCanonical() {
    return db_find(new ModelType(), 'code = canonical and code != "T" order by code');
  }

  public static function canonicalize($code) {
    if ($code == 'VT') {
      return 'V';
    } else if ($code == 'MF') {
      return 'A';
    } else {
      return $code;
    }
  }
}

?>
