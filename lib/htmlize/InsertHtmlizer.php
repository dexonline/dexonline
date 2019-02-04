<?php

class InsertHtmlizer extends Htmlizer {

  // htmlize one inserted chunk formatted as {+text+}
  function htmlize($match) {
    $match = str_replace(Constant::SPACES['regular'], Constant::OPENBOX . Constant::SPACES['hair'], $match);
    return sprintf('<ins>%s</ins>', $match[1]);
  }
}
