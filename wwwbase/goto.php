<?php
require_once("../phplib/util.php");

$skey = util_getRequestParameter('key');
$url = AdsLink::getUrlByKey($skey);
AdsClick::addClick($skey, $_SERVER['REMOTE_ADDR']);
header("Location: $url");

?>
