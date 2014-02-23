<?php

class Lexem extends BaseObject implements DatedObject {
  public static $_table = 'Lexem';

  private $mt = null;                // ModelType object, but we call it $mt because there is already a DB field called 'modelType'
  private $sources = null;
  private $sourceNames = null;       // Comma-separated list of source names
  private $inflectedFormMap = null;  // Mapped by various criteria depending on the caller

  const STRUCT_STATUS_NEW = 1;
  const STRUCT_STATUS_IN_PROGRESS = 2;
  const STRUCT_STATUS_UNDER_REVIEW = 3;
  const STRUCT_STATUS_DONE = 4;
  public static $STRUCT_STATUS_NAMES = array(self::STRUCT_STATUS_NEW => 'neîncepută',
                                             self::STRUCT_STATUS_IN_PROGRESS => 'în lucru',
                                             self::STRUCT_STATUS_UNDER_REVIEW => 'așteaptă moderarea',
                                             self::STRUCT_STATUS_DONE => 'terminată');

  public static function create($form = null, $modelType = null, $modelNumber = null, $restriction = '') {
    $l = Model::factory('Lexem')->create();
    if ($form) {
      $l->form = $form;
      $l->formNoAccent = str_replace("'", '', $form);
      $l->formUtf8General = $l->formNoAccent;
      $l->reverse = StringUtil::reverse($l->formNoAccent);
    }
    $l->description = '';
    $l->tags = '';
    $l->modelType = $modelType;
    $l->modelNumber = $modelNumber;
    $l->restriction = $restriction;
    $l->comment = null;
    $l->isLoc = false;
    $l->noAccent = false;
    return $l;
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
        ->where('LexemSource.lexemId', $this->id)
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

  function getInflectedFormsMappedByRank() {
    if ($this->inflectedFormMap === null) {
      // These inflected forms have an extra field (rank) from the join
      $ifs = Model::factory('InflectedForm')
        ->select('InflectedForm.*')
        ->select('rank')
        ->join('Inflection', 'inflectionId = Inflection.id')
        ->where('lexemId', $this->id)
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

  public static function loadByExtendedName($extName) {
    $parts = preg_split('/\(/', $extName, 2);
    $name = addslashes(trim($parts[0]));
    if (count($parts) == 2) {
      $description = addslashes(trim($parts[1]));
      $description = str_replace(')', '', $description);
    } else {
      $description = '';
    }
    return Model::factory('Lexem')->where('formNoAccent', $name)->where('description', $description)->find_many();
  }

  // For V1, this loads all lexems in (V1, VT1)
  public static function loadByCanonicalModel($modelType, $modelNumber) {
    return Model::factory('Lexem')->select('Lexem.*')->join('ModelType', 'modelType = code', 'mt')->where('mt.canonical', $modelType)->where('modelNumber', $modelNumber)
      ->order_by_asc('formNoAccent')->find_many();
  }

  /**
   * For update.php
   */
  public static function loadNamesByMinModDate($modDate) {
    return db_execute("select D.id, formNoAccent from Definition D force index(modDate), LexemDefinitionMap M, Lexem L " .
                      "where D.id = definitionId and lexemId = L.id and status = 0 and D.modDate >= {$modDate} " .
                      "and sourceId in (select id from Source where canDistribute) order by D.modDate, D.id");
  }

  public static function searchInflectedForms($cuv, $hasDiacritics, $useMemcache = false) {
    if ($useMemcache) {
      $key = "inflected_" . ($hasDiacritics ? '1' : '0') . "_$cuv";
      $result = mc_get($key);
      if ($result) {
        return $result;
      }
    }
    $field = $hasDiacritics ? 'formNoAccent' : 'formUtf8General';
    $result = Model::factory('Lexem')->select('Lexem.*')->distinct()->join('InflectedForm', 'Lexem.id = InflectedForm.lexemId')
      ->where("InflectedForm.$field", $cuv)->order_by_asc('Lexem.formNoAccent')->find_many();
    if ($useMemcache) {
      mc_set($key, $result);
    }
    return $result;
  }

  public static function searchApproximate($cuv, $hasDiacritics, $useMemcache = false) {
    if ($useMemcache) {
      $key = "approx_" . ($hasDiacritics ? '1' : '0') . "_$cuv";
      $result = mc_get($key);
      if ($result) {
        return $result;
      }
    }
    $field = $hasDiacritics ? 'formNoAccent' : 'formUtf8General';

    $start = microtime(true);
    $method = "trigram";
    $leng = mb_strlen($cuv);
    $result = NGram::searchNGram($cuv);
    $end = microtime(true);
    $search_time = sprintf('%0.3f', $end - $start);
/*
    $logArray = "";
    foreach ($result as $word) {		
      $logArray = $logArray . " " . $word;
    }
    $logEntry = "$method\t$search_time\t$cuv:\t$logArray\t$leng\t" . count($result) . "\n";
    file_put_contents("/var/log/dex-approx.log", $logEntry, FILE_APPEND | LOCK_EX);
*/
    if ($useMemcache) {
      mc_set($key, $result);
    }
    return $result;

  }

  public static function searchRegexp($regexp, $hasDiacritics, $sourceId, $useMemcache) {
    if ($useMemcache) {
      $key = "regexp_" . ($hasDiacritics ? '1' : '0') . "_" . ($sourceId ? $sourceId : 0) . "_$regexp";
      $result = mc_get($key);
      if ($result) {
        return $result;
      }
    }
    $mysqlRegexp = StringUtil::dexRegexpToMysqlRegexp($regexp);
    $field = $hasDiacritics ? 'formNoAccent' : 'formUtf8General';
    if ($sourceId) {
      // Suppres warnings from idiorm's log query function, which uses vsprintf, which trips on extra % signs.
      $result = @Model::factory('Lexem')->select('Lexem.*')->distinct()->join('LexemDefinitionMap', 'Lexem.id = lexemId')
        ->join('Definition', 'definitionId = d.id', 'd')->where_raw("$field $mysqlRegexp")->where('d.sourceId', $sourceId)
        ->order_by_asc('formNoAccent')->limit(1000)->find_many();
    } else {
      $result = @Model::factory('Lexem')->where_raw("$field $mysqlRegexp")->order_by_asc('formNoAccent')->limit(1000)->find_many();
    }
    if ($useMemcache) {
      mc_set($key, $result);
    }
    return $result;
  }

  public static function countRegexpMatches($regexp, $hasDiacritics, $sourceId, $useMemcache) {
    if ($useMemcache) {
      $key = "regexpCount_" . ($hasDiacritics ? '1' : '0') . "_" . ($sourceId ? $sourceId : 0) . "_$regexp";
      $result = mc_get($key);
      if ($result) {
        return $result;
      }
    }
    $mysqlRegexp = StringUtil::dexRegexpToMysqlRegexp($regexp);
    $field = $hasDiacritics ? 'formNoAccent' : 'formUtf8General';
    $result = $sourceId ?
      db_getSingleValue("select count(distinct L.id) from Lexem L join LexemDefinitionMap on L.id = lexemId join Definition D on definitionId = D.id " .
                        "where $field $mysqlRegexp and sourceId = $sourceId order by formNoAccent") :
      Model::factory('Lexem')->where_raw("$field $mysqlRegexp")->count();
    if ($useMemcache) {
      mc_set($key, $result);
    }
    return $result;
  }

  public static function countUnassociated() {
    // We compute this as (all lexems) - (lexems showing up in LexemDefinitionMap)
    $all = Model::factory('Lexem')->count();
    $associated = db_getSingleValue('select count(distinct lexemId) from LexemDefinitionMap');
    return $all - $associated;
  }

  public static function loadUnassociated() {
    return Model::factory('Lexem')
      ->raw_query('select * from Lexem where id not in (select lexemId from LexemDefinitionMap) order by formNoAccent', null)->find_many();
  }

  /**
   * For every set of lexems having the same form and no description, load one of them at random.
   */
  public static function loadAmbiguous() {
    // The key here is to create a subquery of all the forms appearing at least twice
    // This takes about 0.6s
    $query = 'select * from Lexem ' .
      'join (select form as f from Lexem group by form having count(*) > 1) dup ' .
      'on form = f ' .
      'where description = "" ' .
      'group by form ' .
      'having count(*) > 1';
    return Model::factory('Lexem')->raw_query($query, null)->find_many();
  }

  public function getVariantIds() {
    $variants = Model::factory('Lexem')->select('id')->where('variantOfId', $this->id)->find_many();
    $ids = array();
    foreach ($variants as $variant) {
      $ids[] = $variant->id;
    }
    return $ids;
  }

  public function regenerateParadigm() {
    $ifs = $this->generateParadigm();
    assert(is_array($ifs));

    InflectedForm::deleteByLexemId($this->id);
    foreach($ifs as $if) {
      $if->save();
    }

    if ($this->modelType == 'VT') {
      $model = FlexModel::loadCanonicalByTypeNumber($this->modelType, $this->modelNumber);
      $pm = ParticipleModel::loadByVerbModel($model->number);
      $this->regeneratePastParticiple($pm->adjectiveModel);
    }
    if ($this->modelType == 'V' || $this->modelType == 'VT') {
      $this->regenerateLongInfinitive();
    }
  }

  public function regeneratePastParticiple($adjectiveModel) {
    $infl = Inflection::loadParticiple();
    $ifs = Model::factory('InflectedForm')->where('lexemId', $this->id)->where('inflectionId', $infl->id)->find_many();
    $model = Model::factory('FlexModel')->where('modelType', 'A')->where('number', $adjectiveModel)->find_one();

    foreach ($ifs as $if) {
      // Load an existing lexem only if it has the same model as $model or T1. Otherwise create a new lexem.
      $lexems = Lexem::get_all_by_formNoAccent($if->formNoAccent);
      $lexem = null;
      foreach ($lexems as $l) {
        if ($l->modelType == 'T' || ($l->modelType == 'A' && $l->modelNumber = $model->number)) {
          $lexem = $l;
        } else if ($this->isLoc && !$l->isLoc) {
          FlashMessage::add("Lexemul {$l->formNoAccent} ({$l->modelType}{$l->modelNumber}), care nu este în LOC, nu a fost modificat.", 'info');
        }
      }
      if ($lexem) {
        $lexem->modelType = 'A';
        $lexem->modelNumber = $model->number;
        $lexem->restriction = '';
        if ($this->isLoc && !$lexem->isLoc) {
          $lexem->isLoc = $this->isLoc;
          FlashMessage::add("Lexemul {$lexem->formNoAccent}, care nu era în LOC, a fost inclus automat în LOC.", 'info');
        }
        $lexem->noAccent = false;
        $lexem->save();
      } else {
        $lexem = Lexem::create($if->form, 'A', $model->number, '');
        $lexem->isLoc = $this->isLoc;
        $lexem->save();

        // Also associate the new lexem with the same definitions as $this.
        $ldms = LexemDefinitionMap::get_all_by_lexemId($this->id);
        foreach ($ldms as $ldm) {
          LexemDefinitionMap::associate($lexem->id, $ldm->definitionId);
        }
        FlashMessage::add("Am creat automat lexemul {$lexem->formNoAccent} (A{$lexem->modelNumber}) și l-am asociat cu toate definițiile verbului.", 'info');
      }
      $lexem->regenerateParadigm();
    }
  }

  public function regenerateLongInfinitive() {
    $infl = Inflection::loadLongInfinitive();
    $ifs = Model::factory('InflectedForm')->where('lexemId', $this->id)->where('inflectionId', $infl->id)->find_many();
    $f107 = Model::factory('FlexModel')->where('modelType', 'F')->where('number', '107')->find_one();
    $f113 = Model::factory('FlexModel')->where('modelType', 'F')->where('number', '113')->find_one();
    
    foreach ($ifs as $if) {
      $model = StringUtil::endsWith($if->formNoAccent, 'are') ? $f113 : $f107;
      
      // Load an existing lexem only if it has one of the models F113, F107 or T1. Otherwise create a new lexem.
      $lexems = Lexem::get_all_by_formNoAccent($if->formNoAccent);
      $lexem = null;
      foreach ($lexems as $l) {
        if ($l->modelType == 'T' || ($l->modelType == 'F' && $l->modelNumber == $model->number)) {
          $lexem = $l;
        } else if ($this->isLoc && !$l->isLoc) {
          FlashMessage::add("Lexemul {$l->formNoAccent} ({$l->modelType}{$l->modelNumber}), care nu este în LOC, nu a fost modificat.", 'info');
        }
      }
      if ($lexem) {
        $lexem->modelType = 'F';
        $lexem->modelNumber = $model->number;
        $lexem->restriction = '';
        if ($this->isLoc && !$lexem->isLoc) {
          $lexem->isLoc = $this->isLoc;
          FlashMessage::add("Lexemul {$lexem->formNoAccent}, care nu era în LOC, a fost inclus automat în LOC.", 'info');
        }
        $lexem->noAccent = false;
        $lexem->save();
      } else {
        $lexem = Lexem::create($if->form, 'F', $model->number, '');
        $lexem->isLoc = $this->isLoc;
        $lexem->save();

        // Also associate the new lexem with the same definitions as $this.
        $ldms = LexemDefinitionMap::get_all_by_lexemId($this->id);
        foreach ($ldms as $ldm) {
          LexemDefinitionMap::associate($lexem->id, $ldm->definitionId);
        }
        FlashMessage::add("Am creat automat lexemul {$lexem->formNoAccent} (F{$lexem->modelNumber}) și l-am asociat cu toate definițiile verbului.", 'info');
      }
      $lexem->regenerateParadigm();
    }
  }

  public function generateInflectedFormWithModel($inflId, $modelId) {
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
      
      $result = FlexStringUtil::applyTransforms($this->form, $transforms, $accentShift, $vowel);
      if (!$result) {
        return null;
      }
      $ifs[] = InflectedForm::create($result, $this->id, $inflId, $variant, $recommended);
      $start = $end;
    }
    
    return $ifs;
  }
  
  public function generateParadigm() {
    $model = FlexModel::loadCanonicalByTypeNumber($this->modelType, $this->modelNumber);
    // Select inflection IDs for this model
    $inflIds = db_getArray("select distinct inflectionId from ModelDescription where modelId = {$model->id} order by inflectionId");
    $ifs = array();
    foreach ($inflIds as $inflId) {
      $if = $this->generateInflectedFormWithModel($inflId, $model->id);
      if ($if === null) {
        return $inflId;
      }
      $ifs = array_merge($ifs, $if);
    }
    return $ifs;
  }

  /* Saves a lexem's variants as produced by dexEdit.php */
  public function updateVariants($variantIds) {
    foreach ($variantIds as $variantId) {
      $variant = Lexem::get_by_id($variantId);
      $variant->variantOfId = $this->id;
      $variant->save();
    }

    // Delete variants no longer in the list
    if ($variantIds) {
      $lexemsToClear = Model::factory('Lexem')->where('variantOfId', $this->id)->where_not_in('id', $variantIds)->find_many();
    } else {
      $lexemsToClear = Lexem::get_all_by_variantOfId($this->id);
    }
    foreach($lexemsToClear as $l) {
      $l->variantOfId = null;
      $l->save();
    }
  }

  /**
   * Called when the model type of a lexem changes from VT to something else.
   * Only deletes participles that do not have their own definitions.
   */
  public function deleteParticiple($oldModelNumber) {
    $infl = Inflection::loadParticiple();
    $pm = ParticipleModel::loadByVerbModel($oldModelNumber);
    $this->_deleteDependentModels($infl->id, 'A', array($pm->adjectiveModel));
  }

  /**
   * Called when the model type of a lexem changes from V/VT to something else.
   * Only deletes long infinitives that do not have their own definitions.
   */
  public function deleteLongInfinitive() {
    $infl = Inflection::loadLongInfinitive();
    $this->_deleteDependentModels($infl->id, 'F', array('107', '113'));
  }

  /**
   * Delete lexems that do not have their own definitions.
   * Arguments for participles: 'A', ($adjectiveModel).
   * Arguments for long infinitives: 'F', ('107', '113').
   */
  private function _deleteDependentModels($inflId, $modelType, $modelNumbers) {
    $ifs = Model::factory('InflectedForm')->where('lexemId', $this->id)->where('inflectionId', $inflId)->find_many();
    $ldms = LexemDefinitionMap::get_all_by_lexemId($this->id);

    $defHash = array();
    foreach($ldms as $ldm) {
      $defHash[$ldm->definitionId] = true;
    }
    
    foreach ($ifs as $if) {
      $lexems = Lexem::get_all_by_formNoAccent($if->formNoAccent);
      foreach ($lexems as $l) {
        if ($l->modelType == 'T' || ($l->modelType == $modelType && in_array($l->modelNumber, $modelNumbers))) {
          $ownDefinitions = false;
          $ldms = LexemDefinitionMap::get_all_by_lexemId($l->id);
          foreach ($ldms as $ldm) {
            if (!array_key_exists($ldm->definitionId, $defHash)) {
              $ownDefinitions = true;
            }
          }

          if (!$ownDefinitions) {
            $l->delete();
          }
        }
      }
    }
  }

  public function delete() {
    if ($this->id) {
      if ($this->modelType == 'VT') {
        $this->deleteParticiple($this->modelNumber);
      }
      if ($this->modelType == 'VT' || $this->modelType == 'V') {
        $this->deleteLongInfinitive();
      }
      LexemDefinitionMap::deleteByLexemId($this->id);
      InflectedForm::deleteByLexemId($this->id);
      Meaning::deleteByLexemId($this->id);
      LexemSource::deleteByLexemId($this->id);
      Synonym::deleteByLexemId($this->id);
    }
    // Clear the variantOfId field for lexems having $this as main.
    $lexemsToClear = Lexem::get_all_by_variantOfId($this->id);
    foreach ($lexemsToClear as $l) {
      $l->variantOfId = null;
      $l->save();
    }
    parent::delete();
  }

  public function save() {
    $this->formUtf8General = $this->formNoAccent;
    $this->reverse = StringUtil::reverse($this->formNoAccent);
    $this->charLength = mb_strlen($this->formNoAccent);
    $this->consistentAccent = (strpos($this->form, "'") !== false) ^ $this->noAccent;
    // It is important for empty fields to be null, not "".
    // This allows the admin report for lexems *with* comments to run faster.
    if ($this->comment == '') {
      $this->comment = null;
    }
    if (!$this->number) {
      $this->number = null;
    }
    parent::save();
  }  

  public function __toString() {
    return $this->description ? "{$this->formNoAccent} ({$this->description})" : $this->formNoAccent;
  }

  public function cloneLexem() {
    $clone = $this->parisClone();
    $clone->description = ($this->description) ? "CLONĂ {$this->description}" : "CLONĂ";
    $clone->modelType = 'T';
    $clone->modelNumber = 1;
    $clone->restriction = '';
    $clone->isLoc = false;
    $clone->verifSp = false;
    $clone->structStatus = self::STRUCT_STATUS_NEW;
    $clone->save();
    
    // Clone the definition list
    $ldms = LexemDefinitionMap::get_all_by_lexemId($this->id);
    foreach ($ldms as $ldm) {
      LexemDefinitionMap::associate($clone->id, $ldm->definitionId);
    }

    // Clone the root meanings
    $meanings = Model::factory('Meaning')->where('lexemId', $this->id)->where('parentId', 0)->find_many();
    foreach ($meanings as $m) {
      $m->cloneMeaning($clone->id, 0);
    }

    // Clone the sources
    $lss = LexemSource::get_all_by_lexemId($this->id);
    foreach ($lss as $ls) {
      $lsClone = $ls->parisClone();
      $lsClone->lexemId = $clone->id;
      $lsClone->save();
    }

    $clone->regenerateParadigm();
    return $clone;
  }
  
}

?>
