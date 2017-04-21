<?php

class AdsClick extends BaseObject {
  public static $_table = 'AdsClick';

  static function addClick($skey, $ip) {
    $ac = Model::factory('AdsClick')->create();
    $ac->skey = $skey;
    $ac->ip = ip2long($ip);
    $ac->save();
  }
}

?>
