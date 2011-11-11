<?php
require_once("../phplib/util.php");

$skey = util_getRequestParameter('key');
$url = AdsLink::get_by_skey($skey)->url;
AdsClick::addClick($skey, $_SERVER['REMOTE_ADDR']);
header("Location: $url");

?>
