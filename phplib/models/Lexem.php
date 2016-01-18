<?php

class Lexem extends BaseObject implements DatedObject {
  public static $_table = 'Lexem';

  private $lexemModels = null;

  const STRUCT_STATUS_NEW = 1;
  const STRUCT_STATUS_IN_PROGRESS = 2;
  const STRUCT_STATUS_UNDER_REVIEW = 3;
  const STRUCT_STATUS_DONE = 4;
  public static $STRUCT_STATUS_NAMES = array(self::STRUCT_STATUS_NEW => 'neîncepută',
                                             self::STRUCT_STATUS_IN_PROGRESS => 'în lucru',
                                             self::STRUCT_STATUS_UNDER_REVIEW => 'așteaptă moderarea',
                                             self::STRUCT_STATUS_DONE => 'terminată');

  function setForm($form) {
    $this->form = $form;
    $this->formNoAccent = str_replace("'", '', $form);
    $this->formUtf8General = $l->formNoAccent;
    $this->reverse = StringUtil::reverse($l->formNoAccent);
  }
  
  public static function create($form) {
    $l = Model::factory('Lexem')->create();
    $l->setForm($form);
    $l->description = '';
    $l->comment = null;
    $l->noAccent = false;

    return $l;
  }

  public static function deepCreate($form, $modelType, $modelNumber, $restriction = '', $isLoc = false) {
    $l = self::create($form);

    $lm = Model::factory('LexemModel')->create();
    $lm->displayOrder = 1;
    $lm->modelType = $modelType;
    $lm->modelNumber = $modelNumber;
    $lm->restriction = $restriction;
    $lm->tags = '';
    $lm->isLoc = $isLoc;
    $lm->setLexem($l);

    $l->setLexemModels(array($lm));
    return $l;
  }

  function getLexemModels() {
    if ($this->lexemModels === null) {
      $this->lexemModels = Model::factory('LexemModel')
        ->where('lexemId', $this->id)
        ->order_by_asc('displayOrder')
        ->find_many();
    }
    return $this->lexemModels;
  }

  function getFirstLexemModel() {
    $lms = $this->getLexemModels();
    return count($lms) ? $lms[0] : null;
  }

  function setLexemModels($lexemModels) {
    $this->lexemModels = $lexemModels;
  }

  function hasModelType($mt) {
    foreach ($this->getLexemModels() as $lm) {
      if ($lm->modelType == $mt) {
        return true;
      }
    }
    return false;
  }

  function hasModel($modelType, $modelNumber) {
    foreach ($this->getLexemModels() as $lm) {
      if ($lm->modelType == $modelType && $lm->modelNumber == $modelNumber) {
        return true;
      }
    }
    return false;
  }

  function isLoc() {
    foreach ($this->getLexemModels() as $lm) {
      if ($lm->isLoc) {
        return true;
      }
    }
    return false;
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
    return Model::factory('Lexem')
      ->table_alias('l')
      ->select('l.*')
      ->distinct()
      ->join('LexemModel', 'l.id = lm.lexemId', 'lm')
      ->join('ModelType', 'lm.modelType = mt.code', 'mt')
      ->where('mt.canonical', $modelType)
      ->where('lm.modelNumber', $modelNumber)
      ->order_by_asc('formNoAccent')
      ->find_many();
  }

  /**
   * For update.php
   */
  public static function loadNamesByMinModDate($modDate) {
    return db_execute("select D.id, formNoAccent from Definition D force index(modDate), LexemDefinitionMap M, Lexem L " .
                      "where D.id = definitionId and lexemId = L.id and status = 0 and D.modDate >= {$modDate} " .
                      "and sourceId in (select id from Source where canDistribute) order by D.modDate, D.id");
  }

  public static function searchLike($search, $hasDiacritics, $useMemcache = false, $limit = 10) {
    if ($useMemcache) {
      $key = "like_" . ($hasDiacritics ? '1' : '0') . "_$search";
      $result = mc_get($key);
      if ($result) {
        return $result;
      }
    }

    $field = $hasDiacritics ? 'formNoAccent' : 'formUtf8General';
    $result = Model::factory('Lexem')
      ->select('formNoAccent')
      ->distinct()
      ->where_like($field, $search)
      ->order_by_asc('formNoAccent')
      ->limit($limit)
      ->find_array();

    if ($useMemcache) {
      mc_set($key, $result);
    }
    return $result;
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
    $result = Model::factory('Lexem')
      ->table_alias('l')
      ->select('l.*')
      ->distinct()
      ->join('LexemModel', 'l.id = lm.lexemId', 'lm')
      ->join('InflectedForm', 'lm.id = f.lexemModelId', 'f')
      ->where("f.$field", $cuv)
      ->order_by_asc('l.formNoAccent')
      ->find_many();
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

    $result = NGram::searchNGram($cuv);

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
    try {
      if ($sourceId) {
	// Suppres warnings from idiorm's log query function, which uses vsprintf, which trips on extra % signs.
	$result = @Model::factory('Lexem')->select('Lexem.*')->distinct()->join('LexemDefinitionMap', 'Lexem.id = lexemId')
	  ->join('Definition', 'definitionId = d.id', 'd')->where_raw("$field $mysqlRegexp")->where('d.sourceId', $sourceId)
	  ->order_by_asc('formNoAccent')->limit(1000)->find_many();
      } else {
	$result = @Model::factory('Lexem')->where_raw("$field $mysqlRegexp")->order_by_asc('formNoAccent')->limit(1000)->find_many();
      }
    } catch (Exception $e) {
      $result = null; // Bad regexp
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
    try {
      $result = $sourceId ?
        db_getSingleValue("select count(distinct L.id) from Lexem L join LexemDefinitionMap on L.id = lexemId join Definition D on definitionId = D.id " .
                          "where $field $mysqlRegexp and sourceId = $sourceId order by formNoAccent") :
        Model::factory('Lexem')->where_raw("$field $mysqlRegexp")->count();
    } catch (Exception $e) {
      $result = 0; // Bad regexp
    }
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
      ->raw_query('select * from Lexem where id not in (select lexemId from LexemDefinitionMap) order by formNoAccent')->find_many();
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
    return Model::factory('Lexem')->raw_query($query)->find_many();
  }

  public function getVariantIds() {
    $variants = Model::factory('Lexem')->select('id')->where('variantOfId', $this->id)->find_many();
    $ids = array();
    foreach ($variants as $variant) {
      $ids[] = $variant->id;
    }
    return $ids;
  }

  public function regenerateDependentLexems() {
    if ($this->hasModelType('VT')) {
      $this->regeneratePastParticiple();
    }
    if ($this->hasModelType('V') || $this->hasModelType('VT')) {
      $this->regenerateLongInfinitive();
    }
  }

  public function regeneratePastParticiple() {
    $infl = Inflection::loadParticiple();

    // Iterate through all the participle forms of this Lexem
    foreach ($this->getLexemModels() as $lm) {
      $pm = ParticipleModel::get_by_verbModel($lm->modelNumber);
      $ifs = InflectedForm::get_all_by_lexemModelId_inflectionId($lm->id, $infl->id);
      foreach ($ifs as $if) {
        $lexem = Model::factory('Lexem')
          ->table_alias('l')
          ->select('l.*')
          ->distinct()
          ->join('LexemModel', 'l.id = lm.lexemId', 'lm')
          ->where('l.formNoAccent', $if->formNoAccent)
          ->where_raw("(lm.modelType = 'T' or (lm.modelType = 'A' and lm.modelNumber = '{$pm->adjectiveModel}'))")
          ->find_one();

        if ($lexem) {
          $partLm = $lexem->getFirstLexemModel();
          if ($partLm->modelType != 'A' || $partLm->modelNumber != $pm->adjectiveModel || $partLm->restriction != '') {
            $partLm->modelType = 'A';
            $partLm->modelNumber = $pm->adjectiveModel;
            $partLm->restriction = '';
            if ($this->isLoc() && !$infLm->isLoc) {
              $partLm->isLoc = true;
              FlashMessage::add("Lexemul {$lexem->formNoAccent}, care nu era în LOC, a fost inclus automat în LOC.", 'info');
            }
            $lexem->deepSave();
          }
        } else {
          $lexem = Lexem::deepCreate($if->form, 'A', $pm->adjectiveModel, '', $this->isLoc());
          $lexem->deepSave();

          // Also associate the new lexem with the same definitions as $this.
          $ldms = LexemDefinitionMap::get_all_by_lexemId($this->id);
          foreach ($ldms as $ldm) {
            LexemDefinitionMap::associate($lexem->id, $ldm->definitionId);
          }
          FlashMessage::add("Am creat automat lexemul {$lexem->formNoAccent} (A{$pm->adjectiveModel}) și l-am asociat cu toate definițiile verbului.", 'info');
        }
      }
    }
  }

  public function regenerateLongInfinitive() {
    $infl = Inflection::loadLongInfinitive();
    $f107 = FlexModel::get_by_modelType_number('F', '107');
    $f113 = FlexModel::get_by_modelType_number('F', '113');

    // Iterate through all the participle forms of this Lexem
    foreach ($this->getLexemModels() as $lm) {
      $ifs = InflectedForm::get_all_by_lexemModelId_inflectionId($lm->id, $infl->id);
      foreach ($ifs as $if) {
        $model = StringUtil::endsWith($if->formNoAccent, 'are') ? $f113 : $f107;

        $lexem = Model::factory('Lexem')
          ->select('l.*')
          ->table_alias('l')
          ->distinct()
          ->join('LexemModel', 'l.id = lm.lexemId', 'lm')
          ->where('l.formNoAccent', $if->formNoAccent)
          ->where_raw("(lm.modelType = 'T' or (lm.modelType = 'F' and lm.modelNumber = '$model->number'))")
          ->find_one();

        if ($lexem) {
          $infLm = $lexem->getFirstLexemModel();
          if ($infLm->modelType != 'F' || $infLm->modelNumber != $model->number || $inf->restriction != '') {
            $infLm->modelType = 'F';
            $infLm->modelNumber = $model->number;
            $infLm->restriction = '';
            if ($this->isLoc() && !$infLm->isLoc) {
              $infLm->isLoc = true;
              FlashMessage::add("Lexemul {$lexem->formNoAccent}, care nu era în LOC, a fost inclus automat în LOC.", 'info');
            }
            $lexem->deepSave();
          }
        } else {
          $lexem = Lexem::deepCreate($if->form, 'F', $model->number, '', $this->isLoc());
          $lexem->deepSave();

          // Also associate the new lexem with the same definitions as $this.
          $ldms = LexemDefinitionMap::get_all_by_lexemId($this->id);
          foreach ($ldms as $ldm) {
            LexemDefinitionMap::associate($lexem->id, $ldm->definitionId);
          }
          FlashMessage::add("Am creat automat lexemul {$lexem->formNoAccent} (F{$model->number}) și l-am asociat cu toate definițiile verbului.", 'info');
        }
      }
    }
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
      $lexemsToClear = self::get_all_by_variantOfId($this->id);
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
  public function deleteParticiple() {
    $infl = Inflection::loadParticiple();
    $adjModels = array();
    foreach ($this->getLexemModels() as $lm) {
      if ($lm->modelType == 'V' || $lm->modelType == 'VT') {
        $pm = ParticipleModel::get_by_verbModel($lm->modelNumber);
        $adjModels[] = $pm->adjectiveModel;
      }
    }
    $this->_deleteDependentModels($infl->id, 'A', $adjModels);
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
    // Load and hash all the definitionIds
    $ldms = LexemDefinitionMap::get_all_by_lexemId($this->id);
    $defHash = array();
    foreach($ldms as $ldm) {
      $defHash[$ldm->definitionId] = true;
    }

    // Iterate through all the forms of the desired inflection (participle / long infinitive)
    foreach ($this->getLexemModels() as $lm) {
      $ifs = InflectedForm::get_all_by_lexemModelId_inflectionId($lm->id, $inflId);
      foreach ($ifs as $if) {
        // Examine all lexems having one of the above forms
        $lexems = Lexem::get_all_by_formNoAccent($if->formNoAccent);
        foreach ($lexems as $l) {
          // Keep only the ones that have acceptable model types/numbers
          $acceptable = false;
          foreach ($l->getLexemModels() as $o) {
            if ($o->modelType == 'T' || ($o->modelType == $modelType && in_array($o->modelNumber, $modelNumbers))) {
              $acceptable = true;
            }
          }

          // If $l has the right model, delete it unless it has its own definitions
          if ($acceptable) {
            $ownDefinitions = false;
            $ldms = LexemDefinitionMap::get_all_by_lexemId($l->id);
            foreach ($ldms as $ldm) {
              if (!array_key_exists($ldm->definitionId, $defHash)) {
                $ownDefinitions = true;
              }
            }

            if (!$ownDefinitions) {
              FlashMessage::add("Am șters automat lexemul {$l->formNoAccent}.", 'info');
              $l->delete();
            }
          }
        }
      }
    }
  }

  public function delete() {
    if ($this->id) {
      if ($this->hasModelType('VT')) {
        $this->deleteParticiple();
      }
      if ($this->hasModelType('VT') || $this->hasModelType('V')) {
        $this->deleteLongInfinitive();
      }
      LexemDefinitionMap::deleteByLexemId($this->id);
      Meaning::delete_all_by_lexemId($this->id);
      Relation::delete_all_by_lexemId($this->id);
      LexemModel::delete_all_by_lexemId($this->id);
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

  /**
   * Saves a lexem and its dependants. Only call after having deleted the lexem's old dependants.
   **/
  function deepSave() {
    $this->save();
    foreach ($this->getLexemModels() as $lm) {
      $lm->lexemId = $this->id;
      $lm->save();
      foreach ($lm->generateInflectedForms() as $if) {
        $if->lexemModelId = $lm->id;
        $if->save();
      }
      foreach ($lm->getLexemSources() as $ls) {
        $ls->lexemModelId = $lm->id;
        $ls->save();
      }
    }
  }

  public function __toString() {
    return $this->description ? "{$this->formNoAccent} ({$this->description})" : $this->formNoAccent;
  }

  public function cloneLexem() {
    $clone = $this->parisClone();
    $clone->description = ($this->description) ? "CLONĂ {$this->description}" : "CLONĂ";
    $clone->verifSp = false;
    $clone->structStatus = self::STRUCT_STATUS_NEW;

    $lm = Model::factory('LexemModel')->create();
    $lm->displayOrder = 1;
    $lm->modelType = 'T';
    $lm->modelNumber = '1';
    $lm->restriction = '';
    $lm->tags = '';
    $lm->isLoc = false;
    $lm->setLexem($clone);

    $clone->setLexemModels(array($lm));
    $clone->deepSave();

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

    return $clone;
  }

}

?>
