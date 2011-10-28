<?php

class User extends BaseObject {
  public static function get($where) {
    $obj = new User();
    $obj->load($where);
    return $obj->id ? $obj : null;
  }

  public function __toString() {
    return $this->nick;
  }
}

?>
