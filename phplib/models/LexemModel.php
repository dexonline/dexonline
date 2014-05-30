<?php

class LexemModel extends BaseObject implements DatedObject {
  public static $_table = 'LexemModel';
  public static $RESTRICTIONS = array('S' => 'singular',
                                      'P' => 'plural',
                                      'U' => 'unipersonal',
                                      'I' => 'impersonal',
                                      'T' => 'trecut');
  
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

  public function generateParadigmMappedByRank() {
    if ($this->inflectedFormMap === null) {
      $this->generateParadigm();
      if (is_array($this->inflectedFormMap)) {
        $this->inflectedFormMap = InflectedForm::mapByInflectionRank($this->inflectedForms);
      }
    }
    return $this->inflectedFormMap;
  }

  public function generateParadigm() {
    if ($this->inflectedForms === null) {
      $this->inflectedForms = array();
      $lexem = Lexem::get_by_id($this->lexemId);
      $model = FlexModel::loadCanonicalByTypeNumber($this->modelType, $this->modelNumber);
      $inflIds = db_getArray("select distinct inflectionId from ModelDescription where modelId = {$model->id} order by inflectionId");

      foreach ($inflIds as $inflId) {
        $if = $this->generateInflectedFormWithModel($lexem->form, $inflId, $model->id);
        if ($if === null) {
          // Make a note of the inflection we cannot generate
          $this->inflectedForms = $inflId;
          return;
        }
        $this->inflectedForms = array_merge($this->inflectedForms, $if);
      }
    }
  }

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
        return null;
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
