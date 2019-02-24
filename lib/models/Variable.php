<?php

class Variable extends BaseObject implements DatedObject {
  public static $_table = 'Variable';

  static function peek($name, $default = null) {
    $v = Variable::get_by_name($name);
    return $v ? $v->value : $default;
  }

  static function poke($name, $value) {
    $v = Variable::get_by_name($name);
    if (!$v) {
      $v = Model::factory('Variable')->create();
      $v->name = $name;
    }
    $v->value = $value;
    $v->save();
  }

  // returns an array of name => value
  static function loadCounts() {
    $vars = Model::factory('Variable')
      ->where_like('name', 'Count.%')
      ->find_many();

    $result = [];
    foreach ($vars as $var) {
      $name = str_replace('Count.', '', $var->name);
      $result[$name] = (int)$var->value;
    }
    return $result;
  }
}
