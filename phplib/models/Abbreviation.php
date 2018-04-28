<?php

class Abbreviation extends BaseObject implements DatedObject {
  public static $_table = 'Abbreviation';

  static function countAvailable($sourceId) {
    return Model::factory('Abbreviation')
      ->where('sourceID', $sourceId)
      ->count();
  }
  
  /**
   * Returns, with constraints, first find abbreviation of form $short
   * 
   * @param   string  $excludedId all others
   * @param   string  $short      abbreviation short form
   * @param   int     $sourceId   source to search
   * @return  ORMWrapper
   */
  static function getDuplicate($excludedId, $short, $sourceId) {
    return Model::factory('Abbreviation')
        ->where_raw('short = binary ?', $short)
        ->where('sourceId', $sourceId)
        ->where_not_in('id', [ $excludedId ])
        ->find_one();
  }

}
