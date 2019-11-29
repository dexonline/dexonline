<?php

class DeleteHtmlizer extends Htmlizer {
  private $tags = "$@%";

  // htmlize one deleted chunk formatted as {-text-}
  function htmlize($match) {
    $match = str_replace(Constant::SPACES['regular'], Constant::OPENBOX . Constant::SPACES['hair'], $match);
    return sprintf('<del>%s</del>', $this->closeTags($match[1]));
  }

  // needed to circumvent html breaking
  function closeTags($s) {
    $reversedTags = strrev(preg_replace('/[^'.$this->tags.'\\\\]*(?:\\\\.[^'.$this->tags.'\\\\]*)*/u', '', $s));

    // recursively removing empty $tags
    $finalTags = preg_replace('/(['.$this->tags.'])([\s]*?|(?R))\1/miUs', '', $reversedTags);
    return $s.$finalTags;
  }

}
