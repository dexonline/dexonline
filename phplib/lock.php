<?php
define('LOCK_PREFIX', '/tmp/lock_');

function lock_exists($name) {
  return file_exists(LOCK_PREFIX . $name);
}

// returns false if the lock already exists
function lock_acquire($name) {
  if (lock_exists($name)) {
    return false;
  }
  touch(LOCK_PREFIX . $name);
  return true;
}

// returns false if the lock does not exist
function lock_release($name) {
  if (!lock_exists($name)) {
    return false;
  }
  unlink(LOCK_PREFIX . $name);
  return true;
}

?>
