<?php

class LexemDefinitionMap extends BaseObject implements DatedObject {
  public static $_table = 'LexemDefinitionMap';

  public static function create($lexemId = null, $definitionId = null) {
    $ldm = Model::factory('LexemDefinitionMap')->create();
    $ldm->lexemId = $lexemId;
    $ldm->definitionId = $definitionId;
    return $ldm;
  }

  public static function associate($lexemId, $definitionId) {
    // The definition and the lexem should exist
    $definition = Definition::get_by_id($definitionId);
    $lexem = Lexem::get_by_id($lexemId);
    if (!$definition || !$lexem) {
      return;
    }

    // The association itself should not exist.
    $ldm = Model::factory('LexemDefinitionMap')->where('lexemId', $lexemId)->where('definitionId', $definitionId)->find_one();
    if (!$ldm) {
      $ldm = LexemDefinitionMap::create($lexemId, $definitionId);
      $ldm->save();
    }
  }

  public static function dissociate($lexemId, $definitionId) {
    $ldm = Model::factory('LexemDefinitionMap')->where('lexemId', $lexemId)->where('definitionId', $definitionId)->find_one();
    if ($ldm) {
      $ldm->delete();
    }
    Definition::updateModDate($definitionId);
  }

  public function save() {
    parent::save();
    Definition::updateModDate($this->definitionId);
  }  

  public static function deleteByLexemId($lexemId) {
    $ldms = LexemDefinitionMap::get_all_by_lexemId($lexemId);
    foreach ($ldms as $ldm) {
      Definition::updateModDate($ldm->definitionId);
      $ldm->delete();
    }
  }
}

?>
