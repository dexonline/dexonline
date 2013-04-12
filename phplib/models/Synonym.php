<?php

class Synonym extends BaseObject implements DatedObject {
  public static $_table = 'Synonym';
  const TYPE_SYNONYM = 1;
  const TYPE_ANTONYM = 2;

  static function loadByMeaningId($meaningId, $type) {
    return Model::factory('Lexem')
      ->select('Lexem.*')
      ->join('Synonym', array('Lexem.id', '=', 'lexemId'))
      ->where('Synonym.meaningId', $meaningId)
      ->where('Synonym.type', $type)
      ->order_by_asc('formNoAccent')
      ->find_many();
  }

  static function updateList($meaningId, $synonymIds, $type) {
    $synonyms = Model::factory('Synonym')->where('meaningId', $meaningId)->where('type', $type)->find_many();
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
      $synonyms[$i]->type = $type;
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
