<?php

class Lock {
  private static $LOCK_PREFIX = '/tmp/lock_';

  static function exists($name) {
    return file_exists(self::$LOCK_PREFIX . $name);
  }

  // returns false if the lock already exists
  static function acquire($name) {
    if (self::exists($name)) {
      return false;
    }
    touch(self::$LOCK_PREFIX . $name);
    return true;
  }

  // returns false if the lock does not exist
  static function release($name) {
    if (!self::exists($name)) {
      return false;
    }
    unlink(self::$LOCK_PREFIX . $name);
    return true;
  }
}

?>
