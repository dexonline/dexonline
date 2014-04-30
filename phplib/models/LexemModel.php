<?php

class LexemModel extends BaseObject implements DatedObject {
  public static $_table = 'LexemModel';
  
  private $mt = null;                // ModelType object, but we call it $mt because there is already a DB field called 'modelType'
  private $sources = null;
  private $sourceNames = null;       // Comma-separated list of source names
  private $inflectedForms = null;
  private $inflectedFormMap = null;  // Mapped by various criteria depending on the caller

  public static function create($modelType, $modelNumber) {
    $lm = Model::factory('LexemModel')->create();
    $lm->modelType = $modelType;
    $lm->modelNumber = $modelNumber;
    $lm->restriction = '';
    $lm->isLoc = false;
    return $lm;
  }

  function getModelType() {
    if ($this->mt === null) {
      $this->mt = ModelType::get_by_code($this->modelType);
    }
    return $this->mt;
  }

  function getSources() {
    if ($this->sources === null) {
      $this->sources = Model::factory('Source')
        ->select('Source.*')
        ->join('LexemSource', 'Source.id = sourceId')
        ->where('LexemSource.lexemModelId', $this->id)
        ->find_many();
    }
    return $this->sources;
  }

  function getSourceNames() {
    if ($this->sourceNames === null) {
      $sources = $this->getSources();
      $results = array();
      foreach($sources as $s) {
        $results[] = $s->shortName;
      }
      $this->sourceNames = implode(', ', $results);
    }
    return $this->sourceNames;
  }

  function getInflectedForms() {
    if ($this->inflectedForms === null) {
      $this->inflectedForms = Model::factory('InflectedForm')
        ->where('lexemModelId', $this->id)
        ->order_by_asc('inflectionId')
        ->order_by_asc('variant')
        ->find_many();
    }
    return ($this->inflectedForms);
  }

  function getInflectedFormsMappedByRank() {
    if ($this->inflectedFormMap === null) {
      // These inflected forms have an extra field (rank) from the join
      $ifs = Model::factory('InflectedForm')
        ->select('InflectedForm.*')
        ->select('rank')
        ->join('Inflection', 'inflectionId = Inflection.id')
        ->where('lexemModelId', $this->id)
        ->order_by_asc('rank')
        ->order_by_asc('variant')
        ->find_many();

      $map = array();
      foreach ($ifs as $if) {
        if (!array_key_exists($if->rank, $map)) {
          $map[$if->rank] = array();
        }
        $map[$if->rank][] = $if;
      }

      $this->inflectedFormMap = $map;
    }
    return $this->inflectedFormMap;
  }

  function getInflectedFormsMappedByInflectionId() {
    if ($this->inflectedFormMap === null) {
      $this->inflectedFormMap = InflectedForm::mapByInflectionId($this->getInflectedForms());
    }
    return $this->inflectedFormMap;
  }

}

?>
