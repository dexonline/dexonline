<?php

class PageIndex extends BaseObject {
  public static $_table = 'PageIndex';

  static function lookup($word, $sourceId) {
    $word = str_replace([' ', '-'], '', $word);
    $word = mb_strtolower($word);

    if (!$word) {
      return null;
    }

    // source-specific fixes
    if ($sourceId == 42) {
      // Șăineanu disregards diacritics when sorting, so convert the word as well.
      $word = Str::unicodeToLatin($word);
    }

    $pi = Model::factory('PageIndex')
        ->where('sourceId', $sourceId)
        ->where_lte('word', $word)
        ->order_by_desc('word')
        ->find_one();
    return $pi;
  }
}
