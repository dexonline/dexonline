<?php

require_once "../phplib/util.php";

log_scriptLog('Running purgeOldCookies.php');

$thirtyTwoDaysAgo = time() - 32 * 24 * 3600;
db_deleteCookiesBefore($thirtyTwoDaysAgo);

log_scriptLog('purgeOldCookies.php completed successfully (against all odds)');

?>
