<?php

class MeaningTag extends BaseObject implements DatedObject {
  public static $_table = 'MeaningTag';

  public static function deleteByMeaningId($meaningId) {
    $mts = self::get_all_by_meaningId($meaningId);
    foreach ($mts as $mt) {
      $mt->delete();
    }
  }
}

?>
