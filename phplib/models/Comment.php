<?php

class Comment extends BaseObject {
  function __construct() {
    parent::__construct();
    $this->status = ST_ACTIVE;
  }

  public static function get($where) {
    $obj = new Comment();
    $obj->load($where);
    return $obj->id ? $obj : null;
  }
}

?>
