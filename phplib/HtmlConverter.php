<?php

/**
 * Static class that converts internal notations to HTML. Works with any
 * object that has an internalRep field. Collects errors and warnings
 * encountered in the process.
 *
 * Easier to implement as a static class since it is unclear how to
 * instantiate and reuse a converter.
 **/

class HtmlConverter {
  private static $errors = [];
  private static $warnings = [];

  static function convert($obj) {
    $sourceId = ($obj instanceof Definition) ? $obj->sourceId : 0;
    list($html, $footnotes)
      = Str::htmlize($obj->internalRep, $sourceId, false, self::$errors, self::$warnings);

    if ($obj instanceof Definition) {
      $obj->setFootnotes($footnotes);
    }
    return $html;
  }

  // Export errors and warnings as flash messages
  static function exportMessages() {
    foreach (self::$warnings as $w) {
      FlashMessage::add($w, 'warning');
    }

    foreach (self::$errors as $e) {
      FlashMessage::add($e);
    }
  }

}
