<?php

class MeaningTagMap extends BaseObject implements DatedObject {
  public static $_table = 'MeaningTagMap';

  static function updateMeaningTags($meaningId, $tagValues) {
    $mtms = self::get_all_by_meaningId($meaningId);
    while (count($mtms) < count($tagValues)) {
      $mtms[] = Model::factory('MeaningTagMap')->create();
    }
    while (count($mtms) > count($tagValues)) {
      $deadMtm = array_pop($mtms);
      $deadMtms->delete();
    }
    foreach ($tagValues as $i => $value) {
      $mt = MeaningTag::get_by_value($value);
      $mtms[$i]->meaningId = $meaningId;
      $mtms[$i]->meaningTagId = $mt->id;
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
