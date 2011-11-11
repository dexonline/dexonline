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

  static function getByModelIdMapByInflectionIdVariantApplOrder($modelId) {
    $mds = Model::factory('ModelDescription')->where('modelId', $modelId)
      ->order_by_asc('inflectionId')->order_by_asc('variant')->order_by_asc('applOrder')->find_many();
    $map = array();
    foreach ($mds as $md) {
      if (!array_key_exists($md->inflectionId, $map)) {
        $map[$md->inflectionId] = array();
      }
      if (!array_key_exists($md->variant, $map[$md->inflectionId])) {
        $map[$md->inflectionId][$md->variant] = array();
      }
      $map[$md->inflectionId][$md->variant][$md->applOrder] = $md;
    }
    return $map;
  }
}

?>
