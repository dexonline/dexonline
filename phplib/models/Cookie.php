<?php

class Cookie extends BaseObject implements DatedObject {
  public static $_table = 'Cookie';

  static function create($userId) {
    $c = Model::factory('Cookie')->create();
    $c->userId = $userId;
    $c->cookieString = StringUtil::randomCapitalLetters(12);
    $c->save();
    return $c;
  }
}

?>
