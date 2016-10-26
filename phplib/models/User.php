<?php

class User extends BaseObject {
  public static $_table = 'User';

  public function __toString() {
    return $this->nick;
  }

  static function getStructurists($includeUserId = null) {
    return Model::factory('User')
      ->where_raw('(moderator & ?) or (id = ?)', [PRIV_STRUCT, $includeUserId])
      ->order_by_asc('nick')
      ->find_many();


  }
}

?>
