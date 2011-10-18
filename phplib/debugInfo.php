<?php

function debug_init() {
  $GLOBALS['debugInfo_startTimestamp'] = debug_getTimeInMillis();
  $GLOBALS['debugInfo_lastClockReset'] = debug_getTimeInMillis();
  $GLOBALS['debugInfo_debugOn'] = true;
  $GLOBALS['debugInfo'] = array();
}

function debug_getTimeInMillis() {
  $seconds = microtime(true);
  return (int)($seconds * 1000);
}

function debug_resetClock() {
  $GLOBALS['debugInfo_lastClockReset'] = debug_getTimeInMillis();
}

function debug_off() {
  $GLOBALS['debugInfo_debugOn'] = false;
}

function debug_stopClock($message) {
  // Some scripts must not collect debug info, because they run millions of
  // queries and the debug info grows to a huge string.
  if ($GLOBALS['debugInfo_debugOn']) {
    $delta = debug_getTimeInMillis() - $GLOBALS['debugInfo_lastClockReset'];
    $GLOBALS['debugInfo'][] = "$delta ms: [$message]";
    debug_resetClock();
  }
}

function debug_getRunningTimeInMillis() {
  return debug_getTimeInMillis() - $GLOBALS['debugInfo_startTimestamp'];
}

/**
 * Handler for ADOConnection::outp().
 * Note that the timers for these commands are off by 1, because AdoDB logs the SQL statement before it executes it.
 **/
function debug_adodbHandler($msg, $newline = true) {
  $msg = preg_replace(array('/\s*<hr>\s*/', '/\s*\(mysql\):\s*/', '/\s*&nbsp;\s*/'), '', $msg);
  debug_stopClock($msg);
}

?>
