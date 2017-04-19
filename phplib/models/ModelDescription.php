<?php

class ModelDescription extends BaseObject {
  public static $_table = 'ModelDescription';

  const UNKNOWN_ACCENT_SHIFT = 100;
  const NO_ACCENT_SHIFT = 101;

  static function loadForModel($modelId) {
    return Model::factory('ModelDescription')
      ->where('modelId', $modelId)
      ->where('applOrder', 0)
      ->order_by_asc('inflectionId')
      ->order_by_asc('variant')
      ->find_many();
  }
}

?>
