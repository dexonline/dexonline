<?php

class Transform extends BaseObject {
  function __construct($from = null, $to = null) {
    parent::__construct();
    $this->transfFrom = $from;
    $this->transfTo = $to;
  }

  public static function get($where) {
    $obj = new Transform();
    $obj->load($where);
    return $obj->id ? $obj : null;
  }

  public static function createOrLoad($from, $to) {
    $t = self::get("transfFrom = '{$from}' and transfTo = '{$to}'");
    if (!$t) {
      $t = new Transform($from, $to);
      $t->save();
    }
    return $t;
  }

  public function __toString() {
    $from = $this->transfFrom ? $this->transfFrom : 'nil';
    $to = $this->transfTo ? $this->transfTo : 'nil';
    return "($from=>$to)";
  }
}

?>
