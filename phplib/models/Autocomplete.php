<?php

class Autocomplete extends BaseObject implements DatedObject {
  public static $_table = 'Autocomplete';

  static function ac($prefix, $limit) {
    $hasDiacritics =
      session_user_prefers(Preferences::FORCE_DIACRITICS) ||
      StringUtil::hasDiacritics($prefix);

    $field = $hasDiacritics ? 'formNoAccent' : 'formUtf8General';
    $results = Model::factory('Autocomplete')
      ->select('formNoAccent')
      ->where_like($field, "{$prefix}%")
      ->order_by_asc('formNoAccent')
      ->limit($limit)
      ->find_array();

    $forms = array_map(function($rec) {
      return $rec['formNoAccent'];
    }, $results);

    return $forms;
  }

}
