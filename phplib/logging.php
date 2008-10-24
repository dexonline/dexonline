<?php

function log_userLog($s) {
  $s = session_getUserNick() . ": " . $s;
  _log_namedLog('userlog', $s);
}

function log_dictLog($s) {
  _log_namedLog('dictlog', $s);
}

function log_scriptLog($s) {
  _log_namedLog('scriptlog', $s);
}

function _log_namedLog($name, $s) {
  $f = fopen(util_getRootPath() . "log/$name", "a");
  fwrite($f, date("Y-m-d H:i:s", time()) . " $s\n");
  fclose($f);
}

?>
