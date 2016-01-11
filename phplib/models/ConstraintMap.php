<?php

class ConstraintMap extends BaseObject {
  public static $_table = 'ConstraintMap';

  const ALL_VARIANTS = -1;

  /**
   * Given a restriction like 'PT', and an inflection, returns true iff the inflection ID is valid under all the restrictions.
   */
  public static function validInflection($inflId, $restr, $variant) {
    $count = Model::factory('ConstraintMap')
           ->where_raw("locate(code, binary '$restr') > 0")
           ->where('inflectionId', $inflId)
           ->where_in('variant', [$variant, self::ALL_VARIANTS])
           ->count();
    return !$count;
  }
}

?>
