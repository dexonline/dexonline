<?php

class Relation extends BaseObject implements DatedObject {
  public static $_table = 'Relation';
  const TYPE_SYNONYM = 1;
  const TYPE_ANTONYM = 2;
  const TYPE_DIMINUTIVE = 3;
  const TYPE_AUGMENTATIVE = 4;
  const NUM_TYPES = 4;

  // Returns a meaning's related lexems, mapped by type
  static function loadByMeaningId($meaningId) {
    $lexems = Model::factory('Lexem')
      ->select('Lexem.*')
      ->select('Relation.type')
      ->join('Relation', array('Lexem.id', '=', 'lexemId'))
      ->where('Relation.meaningId', $meaningId)
      ->order_by_asc('formNoAccent')
      ->find_many();
    $results = array();
    for ($i = 1; $i <= self::NUM_TYPES; $i++) {
      $results[$i] = array();
    }
    foreach ($lexems as $l) {
      $results[$l->type][] = $l;
    }
    return $results;
  }

  // Returns a meaning's related lexems, given a map of relation type to lexem ID array
  static function loadRelatedLexems($map) {
    $results = array();
    foreach ($map as $type => $lexemIds) {
      if ($type) {
        $results[$type] = Lexem::loadByIds($lexemIds);
      }
    }
    return $results;
  }
}

?>
