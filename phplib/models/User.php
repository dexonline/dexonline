<?php

class User extends BaseObject {
  public static $_table = 'User';

  public function __toString() {
    return $this->nick;
  }

  static function getStructurists($includeUserId = 0) {
    if (!$includeUserId) {
      $includeUserId = null; // prevent loading the Anonymous user (id = 0)
    }
    return Model::factory('User')
      ->where_raw('(moderator & ?) or (id = ?)', [PRIV_STRUCT, $includeUserId])
      ->order_by_asc('nick')
      ->find_many();
  }
}

?>
