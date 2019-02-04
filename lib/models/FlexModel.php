<?php

// Used to be named Model, but that name collided with Idiorm's class.
class FlexModel extends BaseObject implements DatedObject {
  public static $_table = 'Model';

  static function create($modelType = '', $number = '', $description = '', $exponent = '') {
    $fm = Model::factory('FlexModel')->create();
    $fm->modelType = $modelType;
    $fm->number = $number;
    $fm->description = $description;
    $fm->exponent = $exponent;
    return $fm;
  }

  function getHtmlExponent() {
    return Str::highlightAccent($this->exponent);
  }

  /* Returns a lexeme with inflected forms. Creates one if one doesn't exist. */
  function getExponentWithParadigm() {
    // Load by canonical model, so if $modelType is V, look for a lexeme with type V or VT.
    $l = Model::factory('Lexeme')
       ->table_alias('l')
       ->select('l.*')
       ->join('ModelType', 'modelType = code', 'mt')
       ->where('mt.canonical', $this->modelType)
       ->where('l.modelNumber', $this->number)
       ->where('l.form', $this->exponent)
       ->find_one();
    if ($l) {
      $l->loadInflectedFormMap();
    } else {
      $l = Lexeme::create($this->exponent, $this->modelType, $this->number);
      $l->setAnimate(true);
      $l->generateInflectedFormMap();
    }
    return $l;
  }

  static function loadByType($type) {
    $type = ModelType::canonicalize($type);
    // Need a raw query here because order_by_asc() expects a field, nothing more
    return Model::factory('FlexModel')
      ->where('modelType', $type)
      ->order_by_expr('cast(number as unsigned)')
      ->order_by_asc('number')
      ->find_many();
  }

  // syntactic sugar so the caller doesn't have to split F1 into ['F', '1']
  static function loadCanonical($modelName) {
    $pos = strcspn($modelName, '0123456789');
    $type = substr($modelName, 0, $pos);
    $number = substr($modelName, $pos);
    return self::loadCanonicalByTypeNumber($type, $number);
  }

  static function loadCanonicalByTypeNumber($type, $number) {
    $type = ModelType::canonicalize($type);
    return FlexModel::get_by_modelType_number($type, $number);
  }

  function delete() {
    $mds = ModelDescription::get_by_modelId($this->id);
    foreach ($mds as $md) {
      $md->delete();
    }
    if ($this->modelType == 'V') {
      $pm = ParticipleModel::loadByVerbModel($this->number);
      $pm->delete();
    }
    parent::delete();
  }

  function __toString() {
    return $this->modelType . $this->number;
  }
}
