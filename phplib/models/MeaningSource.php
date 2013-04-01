<?php

class MeaningSource extends BaseObject {
  public static $_table = 'MeaningSource';

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
}

?>
