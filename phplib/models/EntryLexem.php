<?php

class EntryLexem extends Association implements DatedObject {
  public static $_table = 'EntryLexem';

  public static function dissociate($entryId, $lexemId) {
    self::delete_all_by_entryId_lexemId($entryId, $lexemId);
  }

}

?>
