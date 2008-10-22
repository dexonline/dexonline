<?php

function os_errorAndExit($msg) {
  fprintf(STDERR, "ERROR: $msg\n");
  log_scriptLog("ERROR: $msg\n");
  exit(1);
}

function os_executeAndAssert($command) {
  os_executeAndAssertDebug($command, false);
}

function os_executeAndAssertDebug($command, $debug) {
  $exit_code = 0;
  $output = null;
  log_scriptLog("Executing $command");
  exec($command, $output, $exit_code);
  if ($exit_code || $debug) {
    log_scriptLog('Output: ' . implode("\n", $output));
  }
  if ($exit_code) {
    os_errorAndExit("Failed command: $command (code $exit_code)");
  }
}
?>
