<?php

/**
 * Helper class used to accumulate footnotes as we htmlize a string.
 **/

class FootnoteHtmlizer {
  private $sourceId;
  private $footnotes;
  private $errors;
  private $warnings;

  function __construct($sourceId, &$errors, &$warnings) {
    $this->sourceId = $sourceId;
    $this->errors = &$errors;
    $this->warnings = &$warnings;
    $this->footnotes = [];
  }

  function getKey() {
    return 'footnotes';
  }

  function getPayload() {
    return $this->footnotes;
  }

  // htmlize one instance of a footnote formatted as {{contents}}
  function htmlize($match) {
    $contents = $match[1];
    $f = Model::factory('Footnote')->create();
    $pipePos = strrpos($contents, '/');
    if ($pipePos !== null) { // sanitize should have done this, but paranoia is good
      $f->userId = substr($contents, $pipePos + 1);
      $contents = substr($contents, 0, $pipePos);
    }

    // ignore footnotes within footnotes... so help us God
    list($f->htmlRep, $ignored)
      = Str::htmlize($contents, $this->sourceId, false, $this->errors, $this->warnings);

    $f->rank = 1 + count($this->footnotes);
    $this->footnotes[] = $f;

    // return the replacement
    $result = sprintf('<sup class="footnote">[%s]</sup>', $f->rank);
    return $result;
  }
}
