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

  /**
   * Override if the child class needs to return payload information.
   **/
  function getKey() {
    return null;
  }

  /**
   * Override if the child class needs to return payload information.
   **/
  function getPayload() {
    return null;
  }

  // htmlize one instance of a match
  abstract function htmlize($match);

  /**
   * Some htmlizers need to run some postprocessing. Default is to do nothing.
   **/
  function postprocess($s) {
    return $s;
  }
}
