<?php

class WotdAssignment extends BaseObject {
  public static $_table = 'WotdAssignment';

  static function assign($date, $wotdArtist) {
    $wa = self::get_by_date($date);
    if (!$wa) {
      $wa = Model::factory('WotdAssignment')->create();
      $wa->date = $date;
    }
    $wa->artistId = $wotdArtist->id;
    $wa->save();
  }

  static function unassign($date) {
    self::delete_all_by_date($date);
  }
  
}
