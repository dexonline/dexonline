<?php

class Loc extends BaseObject {
  public static $_table = 'Loc';

  static function lookup($form, $version) {
    return Model::factory('Loc')
      ->where('version', $version)
      ->where('form', $form)
      ->count();
  }
}
