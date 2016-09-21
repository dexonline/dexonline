<?php

/**
 * This class reads request parameters.
 **/
class Request {
  /* Reads a request parameter. */
  static function get($name, $default = null) {
    return array_key_exists($name, $_REQUEST)
      ? $_REQUEST[$name]
      : $default;
  }

  /* Reads a file record from $_FILES. */
  static function getFile($name, $default = null) {
    return array_key_exists($name, $_FILES)
      ? $_FILES[$name]
      : $default;
  }

  /* Reads a present-or-not parameter (checkbox, button etc.). */
  static function isset($name) {
    return array_key_exists($name, $_REQUEST);
  }

  /* Returns an array of values from a parameter in CSV format */
  static function getCsv($name) {
    return explode(',', self::get($name, []));
  }
}

?>
