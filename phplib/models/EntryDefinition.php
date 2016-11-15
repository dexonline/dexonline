<?php

class EntryDefinition extends Association implements DatedObject {
  public static $_table = 'EntryDefinition';

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

}

?>
