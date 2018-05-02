<?php

/**
 * Helper class used to accumulate footnotes as we htmlize a string.
 **/

class FootnoteHtmlizer extends Htmlizer {
  private $footnotes = [];

  function getKey() {
    return 'footnotes';
  }

  function getPayload() {
    return $this->footnotes;
  }

  // htmlize one instance of a footnote formatted as {{contents}}
  function htmlize($match) {
    $contents = $match[1];
    $userId = null;

     // Str::sanitize() should have done this, but paranoia is good
    $pipePos = strrpos($contents, '/');
    if ($pipePos !== null) {
      $userId = substr($contents, $pipePos + 1);
      $contents = substr($contents, 0, $pipePos);
    }

    // ignore footnotes within footnotes... so help us God
    list($html, $ignored)
      = Str::htmlize($contents, $this->sourceId, false, $this->errors, $this->warnings);

    $this->footnotes[] = new Footnote($html, $userId);

    // return the replacement
    $result = sprintf('<sup class="footnote">[%s]</sup>', count($this->footnotes));
    return $result;
  }
}
