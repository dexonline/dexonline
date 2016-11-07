<?php

class Variable extends BaseObject implements DatedObject {
  public static $_table = 'Variable';

  public static function peek($name, $default = null) {
    $v = Variable::get_by_name($name);
    return $v ? $v->value : $default;
  }

  public static function poke($name, $value) {
    $v = Variable::get_by_name($name);
    if (!$v) {
      $v = Model::factory('Variable')->create();
      $v->name = $name;
    }
    $v->value = $value;
    $v->save();
  }
}

?>
