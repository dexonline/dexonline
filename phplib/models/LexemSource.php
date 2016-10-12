<?php

class LexemSource extends BaseObject implements DatedObject {
  public static $_table = 'LexemSource';

  public static function associate($lexemId, $sourceId) {
    // The lexem and the source should exist
    $lexem = Lexem::get_by_id($lexemId);
    $source = Source::get_by_id($sourceId);
    if (!$lexem || !$source) {
      return;
    }

    // The association itself should not exist.
    $ls = self::get_by_lexemId_sourceId($lexemId, $sourceId);
    if (!$ls) {
      $ls = Model::factory('LexemSource')->create();
      $ls->lexemId = $lexemId;
      $ls->sourceId = $sourceId;
      $ls->save();
    }
  }

}

?>
