<?php

class AdsClick extends BaseObject {
  public static $_table = 'AdsClick';

  public static function addClick($skey, $ip) {
    $ac = Model::factory('AdsClick')->create();
    $ac->skey = $skey;
    $ac->ip = $ip;
    $ac->save();
  }
}

?>
