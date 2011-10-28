<?php

class LexemDefinitionMap extends BaseObject {
  function __construct($lexemId = null, $definitionId = null) {
    parent::__construct();
    $this->lexemId = $lexemId;
    $this->definitionId = $definitionId;
  }

  public static function associate($lexemId, $definitionId) {
    // The definition and the lexem should exist
    $definition = Definition::get("id = {$definitionId}");
    $lexem = Lexem::get("id = {$lexemId}");
    if (!$definition || !$lexem) {
      return;
    }

    // The association itself should not exist.
    $ldm = new LexemDefinitionMap();
    $ldm->load("lexemId = {$lexemId} and definitionId = {$definitionId}");
    if (!$ldm->id) {
      $ldm = new LexemDefinitionMap($lexemId, $definitionId);
      $ldm->save();
    }
  }

  public static function dissociate($lexemId, $definitionId) {
    $ldm = new LexemDefinitionMap();
    $ldm->load("lexemId = {$lexemId} and definitionId = {$definitionId}");
    if ($ldm->id) {
      $ldm->delete();
    }
    Definition::updateModDate($definitionId);
  }

  public function save() {
    parent::save();
    Definition::updateModDate($this->definitionId);
  }  

  public static function deleteByLexemId($lexemId) {
    $ldms = db_find(new LexemDefinitionMap(), "lexemId = {$lexemId}");
    foreach ($ldms as $ldm) {
      Definition::updateModDate($ldm->definitionId);
      $ldm->delete();
    }
  }
}

?>
