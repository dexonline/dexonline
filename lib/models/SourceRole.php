<?php

class SourceRole extends BaseObject {
  static $_table = 'SourceRole';

  function getName($count) {
    return ($count == 1) ? $this->nameSingular : $this->namePlural;
  }

  /**
   * Returns a list of roles sorted by name
   **/
  static function getAll() {
    return Model::factory('SourceRole')
      ->order_by_asc('nameSingular')
      ->find_many();
  }

  function isInUse() {
    $count = Model::factory('SourceAuthor')
      ->where('sourceRoleId', $this->id)
      ->count();
    return ($count > 0);
  }

  static function update($roles) {
    // delete vanishing DB records
    $existingIds = array_filter(Util::objectProperty($roles, 'id'));
    $existingIds[] = 0; // ensure array is non-empty

    Model::factory('SourceRole')
      ->where_not_in('id', $existingIds)
      ->delete_many();

    // update or insert existing objects
    foreach ($roles as $r) {
      $r->save();
    }
  }
}
