<?php

class ConstraintMap extends BaseObject {
  public static $_table = 'ConstraintMap';

  /**
   * Given a restriction like 'PT', and an inflection, returns true iff the inflection ID is valid under all the restrictions.
   */
  public static function validInflection($inflId, $restr) {
    $count = Model::factory('ConstraintMap')
           ->where_raw("locate(code, '$restr') > 0")
           ->where('inflectionId', $inflId)
           ->count();
    return !$count;
  }
}

?>
