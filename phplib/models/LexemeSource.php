<?php

class LexemSource extends Association implements DatedObject {
  public static $_table = 'LexemSource';
  static $classes = ['Lexeme', 'Source'];
  static $fields = ['lexemeId', 'sourceId'];
  static $ranks = ['lexemRank', 'sourceRank'];
}
