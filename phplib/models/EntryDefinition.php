<?php

class EntryDefinition extends BaseObject implements DatedObject {
  public static $_table = 'EntryDefinition';

  public static function associate($entryId, $definitionId) {
    // The definition and the entry should exist
    $definition = Definition::get_by_id($definitionId);
    $entry = Entry::get_by_id($entryId);
    if (!$definition || !$entry) {
      return;
    }

    // The association itself should not exist.
    $ed = self::get_by_entryId_definitionId($entryId, $definitionId);
    if (!$ed) {
      $ed = Model::factory('EntryDefinition')->create();
      $ed->entryId = $entryId;
      $ed->definitionId = $definitionId;
      $ed->save();
    }
  }

  public static function dissociate($entryId, $definitionId) {
    self::delete_all_by_entryId_definitionId($entryId, $definitionId);
    Definition::updateModDate($definitionId);
  }

  public static function dissociateDefinition($definitionId) {
    // If by deleting this definition, any associated entries become unassociated, delete them
    $eds = EntryDefinition::get_all_by_definitionId($definitionId);
    EntryDefinition::delete_all_by_definitionId($definitionId);

    foreach ($eds as $ed) {
      $e = Entry::get_by_id($ed->entryId);
      $otherEds = EntryDefinition::get_all_by_entryId($e->id);
      if (!count($otherEds)) {
        Log::warning("Deleting unassociated entry {$e->id} ({$e->description})");
        $e->delete();
      }
    }
  }

  static function getForLexem($l) {
    return Model::factory('EntryDefinition')
      ->table_alias('ed')
      ->select('ed.*')
      ->join('EntryLexem', ['ed.entryId', '=', 'el.entryId'], 'el')
      ->where('el.lexemId', $l->id)
      ->find_many();
  }

  public function save() {
    parent::save();
    Definition::updateModDate($this->definitionId);
  }  

  public static function deleteByEntryId($entryId) {
    $eds = self::get_all_by_entryId($entryId);
    foreach ($eds as $ed) {
      Definition::updateModDate($ed->definitionId);
      $ed->delete();
    }
  }

  public static function deleteByDefinitionId($definitionId) {
    Definition::updateModDate($definitionId);
    self::delete_all_by_definitionid($definitionId);
  }
}

?>
