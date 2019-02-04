<?php

class Transform extends BaseObject {
  public static $_table = 'Transform';

  private static $cache = [];

  static function create($from = null, $to = null) {
    $t = Model::factory('Transform')->create();
    $t->transfFrom = $from;
    $t->transfTo = $to;
    return $t;
  }

  static function createOrLoad($from, $to) {
    $t = Model::factory('Transform')->where('transfFrom', $from)->where('transfTo', $to)->find_one();
    if (!$t) {
      $t = self::create($from, $to);
      $t->save();
    }
    return $t;
  }

  static function loadByIdCached($id) {
    if (!isset(self::$cache[$id])) {
      self::$cache[$id] = self::get_by_id($id);
    }
    return self::$cache[$id];
  }

  function __toString() {
    $from = $this->transfFrom ? $this->transfFrom : 'nil';
    $to = $this->transfTo ? $this->transfTo : 'nil';
    return "($from=>$to)";
  }
}
