<?php

class Visual extends BaseObject implements DatedObject {
  public static $_table = 'Visual';
  public static $parentDir = 'visual';

  /** Retrieves the path relative to the visual folder */
  public static function getPath($givenPath) {
    $regex = '/' . self::$parentDir . '\/.+$/';
    preg_match($regex, $givenPath, $matches);
    
    return $matches[0];
  }
  
  /** Adds an extra backslash before each slash in the string.
    * Useful when replacing with regex. */
  public static function escapeSlashes($string) {
    return preg_replace('/\//', '\/', $string);
  }
}

?>
