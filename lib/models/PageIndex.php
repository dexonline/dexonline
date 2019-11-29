<?php

class PageIndex extends BaseObject implements DatedObject {
  public static $_table = 'PageIndex';

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

    $pi = Model::factory(static::$_table)
        ->where('sourceId', $sourceId)
        ->where_lte('word', $word)
        ->order_by_desc('word')
        ->find_one();
    return $pi;
  }

  static function create(
    $sourceId, $volume, $page, $word, $number, $modUserId
  ) {
    $pi = Model::factory(static::$_table)->create();
    $pi->sourceId = $sourceId;
    $pi->volume = $volume;
    $pi->page = $page;
    $pi->word = $word;
    $pi->number = $number;
    $pi->modUserId = $modUserId;
    return $pi;
  }

  /**
   * Returns, with constraints, first find pageindex 
   *
   * @param   int     $volume    volume for word
   * @param   int     $page      page for word
   * @param   int     $sourceId  source to search
   * @return  ORMWrapper
   */
  static function getDuplicate($volume, $page, $sourceId) {
    return Model::factory(static::$_table)
        ->where('sourceId', $sourceId)
        ->where('volume', $volume)
        ->where('page', $page)
        ->find_one();
  }
}
