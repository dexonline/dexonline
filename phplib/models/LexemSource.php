<?php

class LexemSource extends BaseObject implements DatedObject {
  public static $_table = 'LexemSource';

  /* Returns a list of sourceId's. */
  public static function getForLexem($lexem) {
    $results = array();
    $lexemSources = LexemSource::get_all_by_lexemId($lexem->id);
    foreach($lexemSources as $ls) {
      $results[] = $ls->sourceId;
    }
    return $results;
  }

  public static function update($lexemId, $sourceIds) {
    $lss = self::get_all_by_lexemId($lexemId);
    while (count($lss) < count($sourceIds)) {
      $lss[] = Model::factory('LexemSource')->create();
    }
    while (count($lss) > count($sourceIds)) {
      $deadLs = array_pop($lss);
      $deadLs->delete();
    }
    foreach ($sourceIds as $i => $sourceId) {
      $lss[$i]->lexemId = $lexemId;
      $lss[$i]->sourceId = $sourceId;
      $lss[$i]->save();
    }
  }

  public static function deleteByLexemId($lexemId) {
    $meanings = self::get_all_by_lexemId($lexemId);
    foreach ($meanings as $m) {
      $m->delete();
    }
  }
}

?>
