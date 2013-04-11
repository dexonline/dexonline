<?php

class MeaningTagMap extends BaseObject implements DatedObject {
  public static $_table = 'MeaningTagMap';

  static function updateMeaningTags($meaningId, $tagIds) {
    $mtms = self::get_all_by_meaningId($meaningId);
    while (count($mtms) < count($tagIds)) {
      $mtms[] = Model::factory('MeaningTagMap')->create();
    }
    while (count($mtms) > count($tagIds)) {
      $deadMtm = array_pop($mtms);
      $deadMtm->delete();
    }
    foreach ($tagIds as $i => $tagId) {
      $mtms[$i]->meaningId = $meaningId;
      $mtms[$i]->meaningTagId = $tagId;
      $mtms[$i]->save();
    }
  }

  public static function deleteByMeaningId($meaningId) {
    $mtms = self::get_all_by_meaningId($meaningId);
    foreach ($mtms as $mtm) {
      $mtm->delete();
    }
  }
}

?>
