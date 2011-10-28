<?php

class Source extends BaseObject {
  // Static version of load()
  public static function get($where) {
    $obj = new Source();
    $obj->load($where);
    return $obj->id ? $obj : null;
  }
}

?>
