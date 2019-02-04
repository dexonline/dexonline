<?php

class ModelDescription extends BaseObject {
  public static $_table = 'ModelDescription';

  const UNKNOWN_ACCENT_SHIFT = 100;
  const NO_ACCENT_SHIFT = 101;

  private static $cache = [];

  /**
   * Loads MD's for a given model and inflection and caches them
   **/
  static function loadForInflectionCached($modelId, $inflId) {
    if (!isset(self::$cache[$modelId][$inflId])) {
      $mds = Model::factory('ModelDescription')
        ->where('modelId', $modelId)
        ->where('inflectionId', $inflId)
        ->order_by_asc('variant')
        ->order_by_asc('applOrder')
        ->find_many();
      self::$cache[$modelId][$inflId] = $mds;
    }
    return self::$cache[$modelId][$inflId];
  }

  static function loadForModel($modelId) {
    return Model::factory('ModelDescription')
      ->where('modelId', $modelId)
      ->where('applOrder', 0)
      ->order_by_asc('inflectionId')
      ->order_by_asc('variant')
      ->find_many();
  }
}
