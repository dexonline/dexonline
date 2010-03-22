<?php

require_once "../phplib/util.php";

log_scriptLog('Running purgeOldData.php');

$thirtyOneDaysAgo = time() - 31 * 24 * 3600;
$cookies = db_find(new Cookie(), "createDate < $thirtyOneDaysAgo");
foreach ($cookies as $cookie) {
  $cookie->delete();
}

$yesterday = time() - 24 * 3600;
$pts = db_find(new PasswordToken(), "createDate < $yesterday");
foreach($pts as $pt) {
  $pt->delete();
}

log_scriptLog('purgeOldData.php completed successfully (against all odds)');

?>
