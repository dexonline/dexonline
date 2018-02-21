<?php

/**
 * Some formatting (footnotes, meaning mentions) need to perform more complex
 * operations on the string and/or return extra information beside the altered string.
 **/

abstract class Htmlizer {
  protected $sourceId;
  protected $errors;
  protected $warnings;

  function __construct($sourceId, &$errors, &$warnings) {
    $this->sourceId = $sourceId;
    $this->errors = &$errors;
    $this->warnings = &$warnings;
  }

  abstract function getKey();
  abstract function getPayload();

  // htmlize one instance of a match
  abstract function htmlize($match);
}
