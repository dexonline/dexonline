<?php

class Curator extends BaseObject {

  public static $_table = 'Curator';

  /**
   * Retrieve the list of curators for a given source
   *
   * @param integer $id
   *   Filtering value for Curator.sourceId column
   *
   * @return array
   *   Array of User objects
   */
  public static function getCuratorsForSource($id) {
    return ORM::for_table('User')
      ->select('User.*')
      ->inner_join('Curator', array('User.id', '=', 'Curator.userId'))
      ->where_equal('Curator.sourceId', $id)
      ->find_many();
  }
}
