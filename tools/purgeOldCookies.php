<?php

require_once "../phplib/util.php";

log_scriptLog('Running purgeOldCookies.php');

$thirtyTwoDaysAgo = time() - 31 * 24 * 3600;
$c = new Cookie();
$cookies = $c->find("createDate < $thirtyTwoDaysAgo");
foreach ($cookies as $cookie) {
  $cookie->delete();
}

log_scriptLog('purgeOldCookies.php completed successfully (against all odds)');

?>
