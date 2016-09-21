<?php
require_once("../phplib/util.php");

$skey = Request::get('key');
$url = AdsLink::get_by_skey($skey)->url;
AdsClick::addClick($skey, $_SERVER['REMOTE_ADDR']);
header("Location: $url");

?>
