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
    $text = $match[1];
    $meaningId = $match[2];
    $stars = strlen($match[3]);

    $m = Meaning::get_by_id($meaningId);
    $bc = $m ? $m->breadcrumb : '?';

    switch ($stars) {
      case 0: $contents = "$text (<b>$bc</b>)"; break;
      case 1: $contents = $text; break;
      case 2: $contents = "(<b>$bc</b>)"; break;
    }

    $result = sprintf(
      '<span data-toggle="popover" data-html="true" data-placement="auto right" ' .
      'class="mention" title="%s">%s</span>',
      $meaningId, $contents);
    return $result;
  }
}
