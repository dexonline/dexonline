<?php

class EntryLexem extends BaseObject implements DatedObject {
  public static $_table = 'EntryLexem';

  public static function associate($entryId, $lexemId) {
    // The entry and the lexem should exist
    $entry = Entry::get_by_id($entryId);
    $lexem = Lexem::get_by_id($lexemId);
    if (!$lexem || !$entry) {
      return;
    }

    // The association itself should not exist.
    $el = self::get_by_entryId_lexemId($entryId, $lexemId);
    if (!$el) {
      $el = Model::factory('EntryLexem')->create();
      $el->entryId = $entryId;
      $el->lexemId = $lexemId;
      $el->save();
    }
  }

  public static function dissociate($entryId, $lexemId) {
    self::delete_all_by_entryId_lexemId($entryId, $lexemId);
  }

}

?>
