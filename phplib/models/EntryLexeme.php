<?php

class EntryLexem extends Association implements DatedObject {
  public static $_table = 'EntryLexem';
  static $classes = ['Entry', 'Lexem'];
  static $fields = ['entryId', 'lexemId'];
  static $ranks = ['entryRank', 'lexemRank'];
}
