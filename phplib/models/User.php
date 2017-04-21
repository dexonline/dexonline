<?php

class User extends BaseObject {
  public static $_table = 'User';

  const PRIV_ADMIN = 0x01;
  const PRIV_LOC = 0x02;
  const PRIV_EDIT = 0x04;
  const PRIV_WOTD = 0x08;
  const PRIV_STRUCT = 0x10;
  const PRIV_VISUAL = 0x20;
  const PRIV_DONATION = 0x40;
  const NUM_PRIVILEGES = 7;

  static $PRIV_NAMES = [
    self::PRIV_ADMIN => 'administrator',
    self::PRIV_LOC => 'LOC scrabble',
    self::PRIV_EDIT => 'moderator',
    self::PRIV_WOTD => 'cuvântul zilei',
    self::PRIV_STRUCT => 'structurist al definițiilor',
    self::PRIV_VISUAL => 'dicționarul vizual',
    self::PRIV_DONATION => 'procesare donații',
  ];

  const PRIV_VIEW_HIDDEN = self::PRIV_ADMIN;
  const PRIV_ANY = (1 << self::NUM_PRIVILEGES) - 1;

  public function __toString() {
    return $this->nick;
  }

  static function getStructurists($includeUserId = 0) {
    if (!$includeUserId) {
      $includeUserId = null; // prevent loading the Anonymous user (id = 0)
    }
    return Model::factory('User')
      ->where_raw('(moderator & ?) or (id = ?)', [self::PRIV_STRUCT, $includeUserId])
      ->order_by_asc('nick')
      ->find_many();
  }

  // If the user does not have at least one privilege from the mask, redirect to the home page.
  static function mustHave($priv) {
    if (!self::can($priv)) {
      FlashMessage::add('Nu aveți privilegii suficiente pentru a accesa această pagină.');
      Util::redirect(Core::getWwwRoot());
    }
  }

  // Check if the user has at least one privilege from the mask.
  static function can($priv) {
    // Check the actual database, not the session user
    $userId = Session::getUserId();
    $user = $userId ? User::get_by_id($userId) : null;
    return $user ? ($user->moderator & $priv) : false;
  }

}

?>
