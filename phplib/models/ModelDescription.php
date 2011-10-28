<?php

class ModelDescription extends BaseObject {
  function __construct($other = null) {
    parent::__construct();
    if ($other instanceof ModelDescription) {
      $this->modelId = $other->modelId;
      $this->inflectionId = $other->inflectionId;
      $this->variant = $other->variant;
      $this->applOrder = $other->applOrder;
      $this->transformId = $other->transformId;
      $this->accentShift = $other->accentShift;
      $this->vowel = $other->vowel;
    }
  }

  public static function get($where) {
    $obj = new ModelDescription();
    $obj->load($where);
    return $obj->id ? $obj : null;
  }

  static function getByModelIdMapByInflectionIdVariantApplOrder($modelId) {
    $mds = db_find(new ModelDescription, "modelId = {$modelId} order by inflectionId, variant, applOrder");
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
