<?php

class LexemeSource extends Association implements DatedObject {
  public static $_table = 'LexemeSource';
  static $classes = ['Lexeme', 'Source'];
  static $fields = ['lexemeId', 'sourceId'];
  static $ranks = ['lexemeRank', 'sourceRank'];
}
