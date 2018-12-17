<?php

class ConstraintMap extends BaseObject {
  public static $_table = 'ConstraintMap';

  /**
   * $map[$inflId], returns an array of tuples (restriction, variant) wich
   * forbid $inflId.
   **/
  private static $map = null;
  private static $cache = [];

  const ALL_VARIANTS = -1;

  /**
   * Given a restriction like 'PT', and an inflection, returns true iff the inflection ID is valid under all the restrictions.
   */
  static function validInflectionCached($inflId, $restr, $variant = self::ALL_VARIANTS) {
    if (!isset(self::$cache[$inflId][$restr][$variant])) {
      $count = Model::factory('ConstraintMap')
        ->where_raw("locate(code, binary '$restr') > 0")
        ->where('inflectionId', $inflId)
        ->where_in('variant', [$variant, self::ALL_VARIANTS])
        ->count();
      self::$cache[$inflId][$restr][$variant] = !$count;
    }
    return self::$cache[$inflId][$restr][$variant];
  }

  private static function buildMap() {
    if (!self::$map) {
      $cms = Model::factory('ConstraintMap')->find_many();
      self::$map = [];
      foreach ($cms as $cm) {
        self::$map[$cm->inflectionId][] = [$cm->code, $cm->variant];
      }
    }
  }

  /**
   * @$restriction is a string of 0 or more restrictions
   **/
  static function allows($restriction, $inflId, $variant) {
    self::buildMap();
    if (!isset(self::$map[$inflId])) {
      return true; // Nothing forbids this inflection
    }
    foreach (self::$map[$inflId] as $pair) {
      // Examine a [letter, variant] pair
      list($l, $v) = $pair;
      if ((($v == -1) || ($v == $variant)) &&
          (strpos($restriction, $l) !== false)) {
        return false;
      }
    }
    return true;
  }
}
