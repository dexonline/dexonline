<?php

class PageIndex extends BaseObject {
  public static $_table = 'PageIndex';

  static function create($sourceId, $volume, $page, $word, $number, $modUserId) {
    $a = Model::factory('PageIndex')->create();
    $a->sourceId = $sourceId;
    $a->volume = $volume;
    $a->page = $page;
    $a->word = $word;
    $a->number = $number;
    $a->modUserId = $modUserId;
    return $a;
  }

  static function lookup($word, $sourceId) {
    $word = str_replace([' ', '-'], '', $word);
    $word = mb_strtolower($word);

    if (!$word) {
      return null;
    }

    // source-specific fixes
    $sources = array(42, 82); // Șăineanu and IVO-III
    if (in_array($sourceId, $sources)) {
      // Șăineanu and other dictionaries disregard diacritics when sorting,
      // so convert the word as well.
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
