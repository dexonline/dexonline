<?php
require_once("../phplib/util.php");

$PS_COMMAND = 'ps -eo user,pid,etime,args --no-headers --sort etime';
$APACHE_USER = 'www-data';
$PHP_EXECUTABLE = '/usr/lib/cgi-bin/php5';
$TIME_LIMIT = 3600; /*seconds */

log_scriptLog('Running killOrphanPhpProcesses.php.');
$output = OS::executeAndReturnOutput($PS_COMMAND);
foreach($output as $line) {
  $parts = preg_split('/ +/', $line, 4);
  $runningTime = getRunningTime($parts[2]);
  if ($parts[0] == $APACHE_USER && $runningTime > $TIME_LIMIT && $parts[3] == $PHP_EXECUTABLE) {
    log_scriptLog("killing process {$parts[1]}");
    OS::executeAndAssert("kill -9 {$parts[1]}");
  }
}
log_scriptLog('killOrphanPhpProcesses.php done.');

/****************************************************************************/

// ps gives us the running time in [[DD-]hh:]mm:ss format.
function getRunningTime($string) {
  $matches = array();
  preg_match("/^(?:(?:(\\d+)-)?(\\d+):)?(\\d+):(\\d+)$/", $string, $matches);
  return $matches[1] * 86400 + $matches[2] * 3600 + $matches[3] * 60 + $matches[4];
}

?>
