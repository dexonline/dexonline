<?php

class Cookie extends BaseObject {
  public static function get($where) {
    $obj = new Cookie();
    $obj->load($where);
    return $obj->id ? $obj : null;
  }
}

?>
