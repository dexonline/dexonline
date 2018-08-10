<?php

/**
 * Utility class that maintains state information while we traverse a parser tree.
 **/
class ParserState {

  private $inBold;
  private $inItalic;
  private $position; // in bytes, not multibytes
  private $ruleStack;
  private $errors;

  function __construct() {
    $this->inBold = false;
    $this->inItalic = false;
    $this->position = 0;
    $this->ruleStack = [];
    $this->errors = [];
  }

  function isBold() {
    return $this->inBold;
  }

  function isItalic() {
    return $this->inItalic;
  }

  function processLeaf($str) {
    $this->inBold ^= (substr_count($str, '@') & 1);
    $this->inItalic ^= (substr_count($str, '$') & 1);
    $this->position += strlen($str);
    // printf("[%s] [%s]\n", implode(' / ', $this->ruleStack), $str);
  }

  function pushRule($rule) {
    array_push($this->ruleStack, $rule);
  }

  function popRule() {
    array_pop($this->ruleStack);
  }

  function getCurrentRule() {
    return end($this->ruleStack);
  }

  function getPosition() {
    return $this->position;
  }

}
