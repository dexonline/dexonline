<?php
require_once '../phplib/Core.php';

$skey = Request::get('key');

if ($url = AdsLink::get_by_skey($skey)->url) {
  AdsClick::addClick($skey, $_SERVER['REMOTE_ADDR']);
  header("Location: $url");
} else {
  header("Location: https://www.facebook.com/dexonline");
}
