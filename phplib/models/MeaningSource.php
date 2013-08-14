<?php

class MeaningSource extends BaseObject implements DatedObject {
  public static $_table = 'MeaningSource';

  static function loadSourcesByMeaningId($meaningId) {
    return Model::factory('Source')
      ->select('Source.*')
      ->join('MeaningSource', array('Source.id', '=', 'sourceId'))
      ->where('MeaningSource.meaningId', $meaningId)->find_many();
  }

  public static function deleteByMeaningId($meaningId) {
    $mss = self::get_all_by_meaningId($meaningId);
    foreach ($mss as $ms) {
      $ms->delete();
    }
  }
}

?>
