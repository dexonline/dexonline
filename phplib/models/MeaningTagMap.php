<?php

class MeaningTagMap extends BaseObject implements DatedObject {
  public static $_table = 'MeaningTagMap';

  public static function deleteByMeaningId($meaningId) {
    $mtms = self::get_all_by_meaningId($meaningId);
    foreach ($mtms as $mtm) {
      $mtm->delete();
    }
  }
}

?>
