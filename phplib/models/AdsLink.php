<?php

class AdsLink extends BaseObject {
    public static function getUrlByKey($skey) {
        $al = new AdsLink();
        $al->load("skey = ", $skey);
        return $al->url;
    }
}

?>
