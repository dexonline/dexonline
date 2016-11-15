<?php

class LexemSource extends Association implements DatedObject {
  public static $_table = 'LexemSource';
  static $classes = ['Lexem', 'Source'];
  static $fields = ['lexemId', 'sourceId'];
}

?>
