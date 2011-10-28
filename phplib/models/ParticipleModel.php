<?php

class ParticipleModel extends BaseObject {
  public static function get($where) {
    $obj = new ParticipleModel();
    $obj->load($where);
    return $obj->id ? $obj : null;
  }

  public static function loadByVerbModel($verbModel) {
    $verbModel = addslashes($verbModel);
    return self::get("verbModel = '{$verbModel}'");
  }
}

?>
