<?php

class PasswordToken extends BaseObject {
  public static function get($where) {
    $obj = new PasswordToken();
    $obj->load($where);
    return $obj->id ? $obj : null;
  }
}

?>
