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
}

?>
