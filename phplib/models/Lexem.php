<?php

class Lexem extends BaseObject {
  function __construct($form = null, $modelType = null, $modelNumber = null, $restriction = '') {
    parent::__construct();
    if ($form) {
      $this->form = $form;
      $this->formNoAccent = str_replace("'", '', $form);
      $this->formUtf8General = $this->formNoAccent;
      $this->reverse = StringUtil::reverse($this->formNoAccent);
    }
    $this->description = '';
    $this->tags = '';
    $this->source = '';
    $this->modelType = $modelType;
    $this->modelNumber = $modelNumber;
    $this->restriction = $restriction;
    $this->comment = '';
    $this->isLoc = false;
    $this->noAccent = false;
  }

  public static function get($where) {
    $obj = new Lexem();
    $obj->load($where);
    return $obj->id ? $obj : null;
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
    return db_find(new Lexem(), "formNoAccent = '{$name}' and description = '{$description}'");
  }

  // For V1, this loads all lexems in (V1, VT1)
  public static function loadByCanonicalModel($modelType, $modelNumber) {
    $dbResult = db_execute("select Lexem.* from Lexem, ModelType where modelType = code and canonical = '{$modelType}' and modelNumber = '{$modelNumber}' " .
                           "order by formNoAccent");
    return db_getObjects(new Lexem(), $dbResult);
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
    $dbResult = db_execute("select distinct L.* from InflectedForm I, Lexem L where I.lexemId = L.id and I.$field = '$cuv' order by L.formNoAccent");
    $result = db_getObjects(new Lexem(), $dbResult);
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
    $result = db_find(new Lexem(), "dist2($field, '$cuv') order by formNoAccent");
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
      $dbResult = db_execute("select distinct L.* from Lexem L join LexemDefinitionMap on L.id = lexemId join Definition D on definitionId = D.id " .
                             "where $field $mysqlRegexp and sourceId = $sourceId order by formNoAccent limit 1000");
      $result = db_getObjects(new Lexem(), $dbResult);
    } else {
      $result = db_find(new Lexem(), "$field $mysqlRegexp order by formNoAccent limit 1000");
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
      db_getSingleValue("select count(*) from Lexem where $field $mysqlRegexp");
    if ($useMemcache) {
      mc_set($key, $result);
    }
    return $result;
  }

  public static function loadUnassociated() {
    return db_find(new Lexem(), "id not in (select lexemId from LexemDefinitionMap) order by formNoAccent");
  }

  public function regenerateParadigm() {
    $ifs = $this->generateParadigm();
    assert(is_array($ifs));

    InflectedForm::deleteByLexemId($this->id);
    foreach($ifs as $if) {
      $if->save();
    }

    if ($this->modelType == 'VT') {
      $model = Model::loadCanonicalByTypeNumber($this->modelType, $this->modelNumber);
      $pm = ParticipleModel::loadByVerbModel($model->number);
      $this->regeneratePastParticiple($pm->adjectiveModel);
    }
    if ($this->modelType == 'V' || $this->modelType == 'VT') {
      $this->regenerateLongInfinitive();
    }
  }

  public function regeneratePastParticiple($adjectiveModel) {
    $infl = Inflection::loadParticiple();
    $ifs = db_find(new InflectedForm(), "lexemId = {$this->id} and inflectionId = {$infl->id}");
    $model = Model::get("modelType = 'A' and number = '{$adjectiveModel}'");

    foreach ($ifs as $if) {
      // Load an existing lexem only if it has the same model as $model or T1. Otherwise create a new lexem.
      $lexems = db_find(new Lexem(), "formNoAccent = '{$if->formNoAccent}'");
      $lexem = null;
      foreach ($lexems as $l) {
        if ($l->modelType == 'T' || ($l->modelType == 'A' && $l->modelNumber = $model->number)) {
          $lexem = $l;
        } else if ($this->isLoc && !$l->isLoc) {
          flash_add("Lexemul {$l->formNoAccent} ({$l->modelType}{$l->modelNumber}), care nu este în LOC, nu a fost modificat.", 'info');
        }
      }
      if ($lexem) {
        $lexem->modelType = 'A';
        $lexem->modelNumber = $model->number;
        $lexem->restriction = '';
        if ($this->isLoc && !$lexem->isLoc) {
          $lexem->isLoc = $this->isLoc;
          flash_add("Lexemul {$lexem->formNoAccent}, care nu era în LOC, a fost inclus automat în LOC.", 'info');
        }
        $lexem->noAccent = false;
        $lexem->save();
      } else {
        $lexem = new Lexem($if->form, 'A', $model->number, '');
        $lexem->isLoc = $this->isLoc;
        $lexem->save();

        // Also associate the new lexem with the same definitions as $this.
        $ldms = db_find(new LexemDefinitionMap(), "lexemId = {$this->id}");
        foreach ($ldms as $ldm) {
          LexemDefinitionMap::associate($lexem->id, $ldm->definitionId);
        }
        flash_add("Am creat automat lexemul {$lexem->formNoAccent} (A{$lexem->modelNumber}) și l-am asociat cu toate definițiile verbului.", 'info');
      }
      $lexem->regenerateParadigm();
    }
  }

  public function regenerateLongInfinitive() {
    $infl = Inflection::loadLongInfinitive();
    $ifs = db_find(new InflectedForm(), "lexemId = {$this->id} and inflectionId = {$infl->id}");
    $f107 = Model::get("modelType = 'F' and number = '107'");
    $f113 = Model::get("modelType = 'F' and number = '113'");
    
    foreach ($ifs as $if) {
      $model = StringUtil::endsWith($if->formNoAccent, 'are') ? $f113 : $f107;
      
      // Load an existing lexem only if it has one of the models F113, F107 or T1. Otherwise create a new lexem.
      $lexems = db_find(new Lexem(), "formNoAccent = '{$if->formNoAccent}'");
      $lexem = null;
      foreach ($lexems as $l) {
        if ($l->modelType == 'T' || ($l->modelType == 'F' && $l->modelNumber == $model->number)) {
          $lexem = $l;
        } else if ($this->isLoc && !$l->isLoc) {
          flash_add("Lexemul {$l->formNoAccent} ({$l->modelType}{$l->modelNumber}), care nu este în LOC, nu a fost modificat.", 'info');
        }
      }
      if ($lexem) {
        $lexem->modelType = 'F';
        $lexem->modelNumber = $model->number;
        $lexem->restriction = '';
        if ($this->isLoc && !$lexem->isLoc) {
          $lexem->isLoc = $this->isLoc;
          flash_add("Lexemul {$lexem->formNoAccent}, care nu era în LOC, a fost inclus automat în LOC.", 'info');
        }
        $lexem->noAccent = false;
        $lexem->save();
      } else {
        $lexem = new Lexem($if->form, 'F', $model->number, '');
        $lexem->isLoc = $this->isLoc;
        $lexem->save();

        // Also associate the new lexem with the same definitions as $this.
        $ldms = db_find(new LexemDefinitionMap(), "lexemId = {$this->id}");
        foreach ($ldms as $ldm) {
          LexemDefinitionMap::associate($lexem->id, $ldm->definitionId);
        }
        flash_add("Am creat automat lexemul {$lexem->formNoAccent} (F{$lexem->modelNumber}) și l-am asociat cu toate definițiile verbului.", 'info');
      }
      $lexem->regenerateParadigm();
    }
  }

  public function generateInflectedFormWithModel($inflId, $modelId) {
    if (!ConstraintMap::validInflection($inflId, $this->restriction)) {
      return array();
    }
    $ifs = array();
    $mds = db_find(new ModelDescription(), "modelId = '$modelId' and inflectionId = '$inflId' order by variant, applOrder");
 
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
        $transforms[] = Transform::get("id = " . $mds[$i]->transformId);
      }
      
      $result = FlexStringUtil::applyTransforms($this->form, $transforms, $accentShift, $vowel);
      if (!$result) {
        return null;
      }
      $ifs[] = new InflectedForm($result, $this->id, $inflId, $variant, $recommended);
      $start = $end;
    }
    
    return $ifs;
  }
  
  public function generateParadigm() {
    $model = Model::loadCanonicalByTypeNumber($this->modelType, $this->modelNumber);
    // Select inflection IDs for this model
    $dbResult = db_execute("select distinct inflectionId from ModelDescription where modelId = {$model->id} order by inflectionId");
    $inflIds = db_getArray($dbResult);
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
    $ifs = db_find(new InflectedForm(), "lexemId = {$this->id} and inflectionId = {$inflId}");
    $ldms = db_find(new LexemDefinitionMap(), "lexemId = {$this->id}");

    $defHash = array();
    foreach($ldms as $ldm) {
      $defHash[$ldm->definitionId] = true;
    }
    
    foreach ($ifs as $if) {
      $lexems = db_find(new Lexem(), "formNoAccent = '{$if->formNoAccent}'");
      foreach ($lexems as $l) {
        if ($l->modelType == 'T' || ($l->modelType == $modelType && in_array($l->modelNumber, $modelNumbers))) {
          $ownDefinitions = false;
          $ldms = db_find(new LexemDefinitionMap(), "lexemId = {$l->id}");
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
    parent::save();
  }  

  public function __toString() {
    return $this->description ? "{$this->formNoAccent} ({$this->description})" : $this->formNoAccent;
  }
}

?>
