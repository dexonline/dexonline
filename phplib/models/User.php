<?php

class User extends BaseObject {
  public static $_table = 'User';

  public function __toString() {
    return $this->nick;
  }
}

?>
