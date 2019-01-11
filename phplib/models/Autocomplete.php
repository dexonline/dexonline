<?php

class Autocomplete extends BaseObject implements DatedObject {
  public static $_table = 'Autocomplete';

  static function ac($prefix, $limit) {
    $hasDiacritics =
      Session::userPrefers(Preferences::FORCE_DIACRITICS) ||
      Str::hasDiacritics($prefix);

    $field = $hasDiacritics ? 'formNoAccent' : 'formUtf8General';
    $results = Model::factory('Autocomplete')
      ->select('formNoAccent')
      ->where_like($field, "{$prefix}%")
      ->order_by_asc('formNoAccent')
      ->limit($limit)
      ->find_array();

    $forms = array_column($results, 'formNoAccent');

    return $forms;
  }

}
