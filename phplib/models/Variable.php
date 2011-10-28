<?php

class Variable extends BaseObject {
  public static function peek($name, $default = null) {
    $v = new Variable();
    $v->load("name = '$name'");
    return $v->name ? $v->value : $default;
  }

  public static function poke($name, $value) {
    $v = new Variable();
    $v->load("name = '$name'");
    if (!$v->name) {
      $v->name = $name;
    }
    $v->value = $value;
    $v->save();
  }
}

?>
