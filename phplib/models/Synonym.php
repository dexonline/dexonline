<?php

class Synonym extends BaseObject implements DatedObject {
  public static $_table = 'Synonym';

  static function loadByMeaningId($meaningId) {
    return Model::factory('Lexem')
      ->select('Lexem.*')
      ->join('Synonym', array('Lexem.id', '=', 'lexemId'))
      ->where('Synonym.meaningId', $meaningId)
      ->order_by_asc('formNoAccent')
      ->find_many();
  }

  static function updateSynonyms($meaningId, $synonymIds) {
    $synonyms = self::get_all_by_meaningId($meaningId);
    while (count($synonyms) < count($synonymIds)) {
      $synonyms[] = Model::factory('Synonym')->create();
    }
    while (count($synonyms) > count($synonymIds)) {
      $deadSynonym = array_pop($synonyms);
      $deadSynonym->delete();
    }
    foreach ($synonymIds as $i => $lexemId) {
      $synonyms[$i]->meaningId = $meaningId;
      $synonyms[$i]->lexemId = $lexemId;
      $synonyms[$i]->save();
    }
  }

  public static function deleteByMeaningId($meaningId) {
    $synonyms = self::get_all_by_meaningId($meaningId);
    foreach ($synonyms as $s) {
      $s->delete();
    }
  }
}

?>
