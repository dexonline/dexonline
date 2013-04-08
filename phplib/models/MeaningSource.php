<?php

class MeaningSource extends BaseObject implements DatedObject {
  public static $_table = 'MeaningSource';

  static function loadSourcesByMeaningId($meaningId) {
    $results = Model::factory('Source')->select('Source.*')->join('MeaningSource', array('Source.id', '=', 'sourceId'))->where('MeaningSource.meaningId', $meaningId)->find_many();
    return $results;
  }

  static function updateMeaningSources($meaningId, $sourceIds) {
    $mss = self::get_all_by_meaningId($meaningId);
    while (count($mss) < count($sourceIds)) {
      $mss[] = Model::factory('MeaningSource')->create();
    }
    while (count($mss) > count($sourceIds)) {
      $deadMs = array_pop($mss);
      $deadMs->delete();
    }
    foreach ($sourceIds as $i => $sourceId) {
      $mss[$i]->meaningId = $meaningId;
      $mss[$i]->sourceId = $sourceId;
      $mss[$i]->save();
    }
  }

  public static function deleteByMeaningId($meaningId) {
    $mss = self::get_all_by_meaningId($meaningId);
    foreach ($mss as $ms) {
      $ms->delete();
    }
  }
}

?>
