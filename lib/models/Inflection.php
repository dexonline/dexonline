<?php

class Inflection extends BaseObject {
  public static $_table = 'Inflection';

  const GENDER_MASCULINE = 1;
  const GENDER_FEMININE = 2;

  const NUMBER_SINGULAR = 1;
  const NUMBER_PLURAL = 2;

  const CASE_NOMINATIVE = 1;
  const CASE_DATIVE = 2;
  const CASE_VOCATIVE = 3;

  const ARTICLE_NONE = 1;
  const ARTICLE_DEFINITE = 2;

  private static $cache = [];

  static function loadParticiple() {
    return Model::factory('Inflection')->where_like('description', '%participiu%')->find_one();
  }

  static function loadLongInfinitive() {
    return Model::factory('Inflection')->where_like('description', '%infinitiv lung%')->find_one();
  }

  static function loadByIdCached($id) {
    if (!isset(self::$cache[$id])) {
      self::$cache[$id] = self::get_by_id($id);
    }
    return self::$cache[$id];
  }

  function delete() {
    DB::execute(
      "update Inflection " .
      "set `rank` = `rank` - 1 " .
      "where modelType = '{$this->modelType}' " .
      "and `rank` > {$this->rank}");
    parent::delete();
  }
}
