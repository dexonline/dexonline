<?php

class EntryDefinition extends Association implements DatedObject {
  public static $_table = 'EntryDefinition';
  static $classes = ['Entry', 'Definition'];
  static $fields = ['entryId', 'definitionId'];
  static $ranks = ['entryRank', 'definitionRank'];

  static function dissociateDefinition($definitionId) {
    // If by deleting this definition, any associated entries become unassociated, delete them
    $eds = EntryDefinition::get_all_by_definitionId($definitionId);
    EntryDefinition::delete_all_by_definitionId($definitionId);

    foreach ($eds as $ed) {
      $e = Entry::get_by_id($ed->entryId);
      $otherEds = EntryDefinition::get_all_by_entryId($e->id);
      if (!count($otherEds)) {
        // Also delete any T1 lexemes that are only associated with this entry.
        $lexemes = $e->getLexemes();
        foreach ($lexemes as $l) {
          if (($l->modelType == 'T') &&
              (count($l->getEntries()) == 1)) {
            Log::warning("Deleting T1 lexeme {$l->id} ({$l})");
            $l->delete();
          }
        }
        
        Log::warning("Deleting unassociated entry {$e->id} ({$e->description})");
        $e->delete();
      }
    }
  }

}
