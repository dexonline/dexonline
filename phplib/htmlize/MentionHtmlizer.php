<?php

/**
 * Helper class used to output various kinds of meaning mentions.
 **/

class MentionHtmlizer extends Htmlizer {
  function getKey() {
    return 'mentions';
  }

  function getPayload() {
    return null; // we don't really need to return anything
  }

  // htmlize one instance of a meaning mention formatted as text[meaningID]
  function htmlize($match) {
    $result = sprintf(
      '<span data-toggle="popover" data-html="true" data-placement="auto right" ' .
      'class="mention" title="%s">%s</span>',
      $match[2], $match[1]);
    return $result;
  }
}
