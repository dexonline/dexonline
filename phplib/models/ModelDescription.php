<?php

class ModelDescription extends BaseObject {
  public static $_table = 'ModelDescription';

  function copyFrom($other) {
    $this->modelId = $other->modelId;
    $this->inflectionId = $other->inflectionId;
    $this->variant = $other->variant;
    $this->applOrder = $other->applOrder;
    $this->transformId = $other->transformId;
    $this->accentShift = $other->accentShift;
    $this->vowel = $other->vowel;
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

?>
