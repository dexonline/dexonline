<?php

class ModelDescription extends BaseObject {
  public static $_table = 'ModelDescription';

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
