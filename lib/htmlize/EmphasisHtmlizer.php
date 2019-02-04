<?php

class EmphasisHtmlizer extends Htmlizer {
  private $any = false;

  // htmlize one emphasized chunk formatted as __text__
  function htmlize($match) {
    $this->any = true;
    return sprintf('<span class="emph">%s</span>', $match[1]);
  }

  // when there are any emphasized chunks, deemphasize the entire definition
  function postprocess($s) {
    return $this->any
      ? sprintf('<span class="deemph">%s</span>', $s)
      : $s;
  }
}
