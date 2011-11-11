<?php

class ModelType extends BaseObject {
  public static $_table = 'ModelType';

  public static function loadCanonical() {
    return Model::factory('ModelType')->where_raw('code = canonical')->where_not_equal('code', 'T')->order_by_asc('code')->find_many();
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
