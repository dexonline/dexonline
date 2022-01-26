<?php

/**
 * Helper class used to output various kinds of meaning mentions.
 **/

class MentionHtmlizer extends Htmlizer {
  // htmlize one instance of a meaning mention formatted as text[meaningID]
  function htmlize($match) {
    $text = $match[1];
    $meaningId = $match[2];
    $stars = strlen($match[3]);

    list($entry, $bc) = Preload::getMentionedMeaningEntry($meaningId);
    $bc = $bc ?: '?';

    switch ($stars) {
      case 0: $contents = $text; break;
      case 1: $contents = "$text (<b>$bc</b>)"; break;
      case 2: $contents = "(<b>$bc</b>)"; break;
    }

    $attributes = sprintf(
      'class="mention" ' .
      'title="%s"', $meaningId);
    $result = sprintf('<span %s>%s</span>', $attributes, $contents);

    if ($entry) {
      $href = sprintf('%sintrare/%s/%d/sinteza#meaning%d',
                      Config::URL_PREFIX,
                      $entry->getShortDescription(),
                      $entry->id,
                      $meaningId);
      $result = sprintf('<a href="%s" %s>%s</a>', $href, $attributes, $contents);
    }

    return $result;
  }
}
