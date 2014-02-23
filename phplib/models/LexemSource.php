<?php

class LexemSource extends BaseObject implements DatedObject {
  public static $_table = 'LexemSource';

  /* Returns a list of sourceId's. */
  public static function getForLexem($lexem) {
    $lexemSources = LexemSource::get_all_by_lexemId($lexem->id);
    return util_objectProperty($lexemSources, 'sourceId');
  }

  public static function deleteByLexemId($lexemId) {
    $meanings = self::get_all_by_lexemId($lexemId);
    foreach ($meanings as $m) {
      $m->delete();
    }
  }
}

?>
