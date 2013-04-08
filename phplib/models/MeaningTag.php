<?php

class MeaningTag extends BaseObject implements DatedObject {
  public static $_table = 'MeaningTag';

  static function loadByMeaningId($meaningId) {
    return Model::factory('MeaningTag')
      ->select('MeaningTag.*')
      ->join('MeaningTagMap', array('MeaningTag.id', '=', 'meaningTagId'))
      ->where('MeaningTagMap.meaningId', $meaningId)
      ->order_by_asc('value')
      ->find_many();
  }

}

?>
