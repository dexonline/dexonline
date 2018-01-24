<?php

class EntryLexeme extends Association implements DatedObject {
  public static $_table = 'EntryLexeme';
  static $classes = ['Entry', 'Lexeme'];
  static $fields = ['entryId', 'lexemeId'];
  static $ranks = ['entryRank', 'lexemeRank'];
}
