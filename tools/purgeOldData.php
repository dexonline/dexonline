<?php

require_once __DIR__ . '/../phplib/Core.php';

Log::notice('started');

$now = time();

Log::info('removing old login cookies');
$thirtyOneDaysAgo = $now - 31 * 24 * 3600;
$cookies = Model::factory('Cookie')->where_lt('createDate', $thirtyOneDaysAgo)->find_many();
foreach ($cookies as $cookie) {
  $cookie->delete();
}

Log::info('removing old password tokens');
$yesterday = $now - 24 * 3600;
$pts = Model::factory('PasswordToken')->where_lt('createDate', $yesterday)->find_many();
foreach($pts as $pt) {
  $pt->delete();
}

Log::info('revoking private mode rights');
$users = Model::factory('User')
       ->where_lt('noAdsUntil', $now)
       ->where_gt('noAdsUntil', 0)
       ->find_many();
foreach ($users as $u) {
  Log::info("Clearing noAdsUntil for $u (ID={$u->id})");
  $u->noAdsUntil = 0;
  if ($u->preferences & Preferences::PRIVATE_MODE) {
    Log::info('* Also revoking private mode');
    $u->preferences &= ~Preferences::PRIVATE_MODE;
  }
  $u->save();
}

Log::notice('finished');

?>
