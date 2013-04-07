<?php

class Lexem extends BaseObject implements DatedObject {
  public static $_table = 'Lexem';

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
    $l->source = '';
    $l->modelType = $modelType;
    $l->modelNumber = $modelNumber;
    $l->restriction = $restriction;
    $l->comment = '';
    $l->isLoc = false;
    $l->noAccent = false;
    return $l;
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

  public static function loadUnassociated() {
    return Model::factory('Lexem')
      ->raw_query('select * from Lexem where id not in (select lexemId from LexemDefinitionMap) order by formNoAccent', null)->find_many();
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
    }
    parent::delete();
  }

  public function save() {
    $this->formUtf8General = $this->formNoAccent;
    $this->charLength = mb_strlen($this->formNoAccent);
    parent::save();
  }  

  public function __toString() {
    return $this->description ? "{$this->formNoAccent} ({$this->description})" : $this->formNoAccent;
  }
}

?>
