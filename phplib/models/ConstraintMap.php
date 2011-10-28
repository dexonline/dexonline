<?php

class ConstraintMap extends BaseObject {

  /**
   * Given a restriction like 'PT', and an inflection, returns true iff the inflection ID is valid under all the restrictions.
   */
  public static function validInflection($inflId, $restr) {
    if (!$restr) {
      return true;
    }
    $numAllowed = db_getSingleValue("select count(*) from ConstraintMap where locate(code, '$restr') > 0 and inflectionId = $inflId");
    return ($numAllowed == mb_strlen($restr));
  }
}

?>
