<?php

class MeaningSource extends Association implements DatedObject {
  public static $_table = 'MeaningSource';

  static function loadSourcesByMeaningId($meaningId) {
    return Model::factory('Source')
      ->select('Source.*')
      ->join('MeaningSource', array('Source.id', '=', 'sourceId'))
      ->where('MeaningSource.meaningId', $meaningId)->find_many();
  }
}

?>
