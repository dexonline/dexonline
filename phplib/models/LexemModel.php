<?php

class LexemModel extends BaseObject implements DatedObject {
  public static $_table = 'LexemModel';

  const METHOD_GENERATE = 1;
  const METHOD_LOAD = 2;
  const MAP_INFLECTION_ID = 1;
  const MAP_INFLECTION_RANK = 2;

  private $lexem = null;
  private $mt = null;                // ModelType object, but we call it $mt because there is already a DB field called 'modelType'
  private $sources = null;
  private $sourceNames = null;       // Comma-separated list of source names
  private $inflectedForms = null;
  private $inflectedFormMap = null;  // Mapped by various criteria depending on the caller

  static function create($modelType, $modelNumber) {
    $lm = Model::factory('LexemModel')->create();
    $lm->modelType = $modelType;
    $lm->modelNumber = $modelNumber;
    $lm->restriction = '';
    $lm->isLoc = false;
    return $lm;
  }

  function getLexem() {
    if ($this->lexem === null) {
      $this->lexem = Lexem::get_by_id($this->lexemId);
    }
    return $this->lexem;
  }

  function setLexem($lexem) {
    $this->lexem = $lexem;
  }

  function hasRestriction($letter) {
    return FlexStringUtil::contains($this->restriction, $letter);
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

  function getSourceIds() {
    $results = array();
    foreach($this->getSources() as $s) {
      $results[] = $s->id;
    }
    return $results;
  }

  function setSources($sources) {
    $this->sources = $sources;
  }

  /**
   * Returns an array of InflectedForms. These can be loaded from the disk ($method = METHOD_LOAD)
   * or generated on the fly ($method = METHOD_GENERATE);
   **/
  function getInflectedForms($method) {
    return ($method == self::METHOD_LOAD)
      ? $this->loadInflectedForms()
      : $this->generateInflectedForms();
  }

  function loadInflectedForms() {
    if ($this->inflectedForms === null) {
      $this->inflectedForms = Model::factory('InflectedForm')
        ->where('lexemModelId', $this->id)
        ->order_by_asc('inflectionId')
        ->order_by_asc('variant')
        ->find_many();
    }
    return ($this->inflectedForms);
  }

  function generateInflectedForms() {
    if ($this->inflectedForms === null) {
      $lexem = $this->getLexem();
      $model = FlexModel::loadCanonicalByTypeNumber($this->modelType, $this->modelNumber);
      $inflIds = db_getArray("select distinct inflectionId from ModelDescription where modelId = {$model->id} order by inflectionId");

      try {
        $this->inflectedForms = array();
        foreach ($inflIds as $inflId) {
          $if = $this->generateInflectedFormWithModel($lexem->form, $inflId, $model->id);
          $this->inflectedForms = array_merge($this->inflectedForms, $if);
        }
      } catch (Exception $ignored) {
        // Make a note of the inflection we cannot generate
        $this->inflectedForms = $inflId;
      }
    }        
    return ($this->inflectedForms);
  }

  function getInflectedFormMap($method, $map) {
    if ($this->inflectedFormMap === null) {
      $ifs = $this->getInflectedForms($method);
      if (is_array($ifs)) {
        switch ($map) {
          case self::MAP_INFLECTION_ID: $this->inflectedFormMap = InflectedForm::mapByInflectionId($ifs);
          case self::MAP_INFLECTION_RANK: $this->inflectedFormMap = InflectedForm::mapByInflectionRank($ifs);
        }
      }
    }
    return $this->inflectedFormMap;
  }

  function loadInflectedFormsMappedByRank() {
    return $this->getInflectedFormMap(self::METHOD_LOAD, self::MAP_INFLECTION_RANK);
  }

  function loadInflectedFormsMappedByInflectionId() {
    return $this->getInflectedFormMap(self::METHOD_LOAD, self::MAP_INFLECTION_ID);
  }

  function generateInflectedFormsMappedByRank() {
    return $this->getInflectedFormMap(self::METHOD_GENERATE, self::MAP_INFLECTION_RANK);
  }

  // Throws an exception if the given inflection cannot be generated
  public function generateInflectedFormWithModel($form, $inflId, $modelId) {
    if (!ConstraintMap::validInflection($inflId, $this->restriction)) {
      return array();
    }
    $ifs = array();
    $mds = Model::factory('ModelDescription')->where('modelId', $modelId)->where('inflectionId', $inflId)
      ->order_by_asc('variant')->order_by_asc('applOrder')->find_many();
 
    $start = 0;
    while ($start < count($mds)) {
      // Identify all the md's that differ only by the applOrder
      $end = $start + 1;
      while ($end < count($mds) && $mds[$end]->applOrder != 0) {
        $end++;
      }

      $inflId = $mds[$start]->inflectionId;
      $accentShift = $mds[$start]->accentShift;
      $vowel = $mds[$start]->vowel;
      
      // Apply all the transforms from $start to $end - 1.
      $variant = $mds[$start]->variant;
      $recommended = $mds[$start]->recommended;
      
      // Load the transforms
      $transforms = array();
      for ($i = $end - 1; $i >= $start; $i--) {
        $transforms[] = Transform::get_by_id($mds[$i]->transformId);
      }
      
      $result = FlexStringUtil::applyTransforms($form, $transforms, $accentShift, $vowel);
      if (!$result) {
        throw new Exception();
      }
      $ifs[] = InflectedForm::create($result, $this->id, $inflId, $variant, $recommended);
      $start = $end;
    }
    
    return $ifs;
  }

  function delete() {
    InflectedForm::delete_all_by_lexemModelId($this->id);
    LexemSource::delete_all_by_lexemModelId($this->id);
    FullTextIndex::delete_all_by_lexemModelId($this->id);
    parent::delete();
  }

}

?>
