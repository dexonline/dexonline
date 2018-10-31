<?php

class InsertHtmlizer extends Htmlizer {

  // htmlize one inserted chunk formatted as {+text+}
  function htmlize($match) {
    $match = str_replace(" ","␣", $match);
    return sprintf('<ins>%s</ins>', $match[1]);
  }
}
