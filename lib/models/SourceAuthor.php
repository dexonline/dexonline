<?php

class SourceAuthor extends BaseObject {
  static $_table = 'SourceAuthor';

  static function update($authors, $source) {
    // delete vanishing DB records
    $existingIds = array_filter(Util::objectProperty($authors, 'id'));
    $existingIds[] = 0; // ensure array is non-empty

    Model::factory('SourceAuthor')
      ->where('sourceId', $source->id)
      ->where_not_in('id', $existingIds)
      ->delete_many();

    // update or insert existing objects
    $rank = 0;
    foreach ($authors as $a) {
      $a->sourceId = $source->id;
      $a->rank = ++$rank;
      $a->save();
    }
  }
}
