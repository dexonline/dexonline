<?php

class AdsClick extends BaseObject {
    function __construct($skey, $ip) {
        parent::__construct();
        $this->skey = $skey;
        $this->ip = ip2long($ip);
    }

    public static function addClick($skey, $ip) {
        $ac = new AdsClick($skey, $ip);
        $ac->save();
    }
}

?>
