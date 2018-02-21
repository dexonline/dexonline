<?php

class Abbreviation extends BaseObject implements DatedObject {
  public static $_table = 'Abbreviation';

  static function countAvailable($sourceId) {
    return Model::factory('Abbreviation')
      ->where('sourceID', $sourceId)
      ->count();
  }
}
