<?php

class Lock {
  private static $LOCK_PREFIX = '/lock_';

  static function exists($name) {
    return file_exists(Config::get('global.tempDir') . self::$LOCK_PREFIX . $name);
  }

  // returns false if the lock already exists
  static function acquire($name) {
    if (self::exists($name)) {
      return false;
    }
    touch(Config::get('global.tempDir') . self::$LOCK_PREFIX . $name);
    return true;
  }

  // returns false if the lock does not exist
  static function release($name) {
    if (!self::exists($name)) {
      return false;
    }
    unlink(Config::get('global.tempDir') . self::$LOCK_PREFIX . $name);
    return true;
  }
}

?>
