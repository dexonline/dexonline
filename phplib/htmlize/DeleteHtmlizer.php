<?php

class DeleteHtmlizer extends Htmlizer {
  private $tags = "$@%";

  // htmlize one deleted chunk formatted as {-text-}
  function htmlize($match) {
    $match = str_replace(" ","␣", $match);
    return sprintf('<del>%s</del>', $this->closeTags($match[1]));
  }

  function closeTags($s) {
    $reversedTags = strrev(preg_replace('/[^'.$this->tags.']/u', '', $s));
    return $s.$reversedTags;
  }
}
