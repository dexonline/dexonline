<?php

class NGram extends BaseObject {

  public static $_table = 'NGram';
  public static $NGRAM_SIZE = 3;
  public static $MAX_MOVE = 2; // Maximum distance an n-gram is allowed to migrate
  public static $LENGTH_DIF = 2; // Maximum length difference between the searched word and the suggested one
  public static $MAX_RESULTS = 20; //Maximum number of suggestions

  private static function canonicalize($s) {
    // Remove spaces and dashes from the word
    $s = str_replace([' ', '-'], '', $s);
    return $s;
  }

  // Returns an array of n-grams.
  static function split($s) {
    $s = self::canonicalize($s);
    $s = str_repeat('#', self::$NGRAM_SIZE - 1) . $s . str_repeat('%', self::$NGRAM_SIZE - 1);
    $len = mb_strlen($s);
    $results = [];
    for ($i = 0; $i < $len - self::$NGRAM_SIZE + 1; $i++) {
      $results[] = mb_substr($s, $i, self::$NGRAM_SIZE);
    }
    return $results;
  }

  static function searchNGram($cuv) {
    $cuv = self::canonicalize($cuv);
    $leng = mb_strlen($cuv);
    
    $hash = NGram::searchLexemeIds($cuv);
    if (empty($hash)) {
      return [];
    }
    arsort($hash);
    $max = current($hash);
    $lexIds = array_keys($hash, $max);

    $lexemes = [];
    foreach ($lexIds as $id) {
      $lexeme = Model::factory('Lexeme')
             ->where('id', $id)
             ->where_gte('charLength', $leng - self::$LENGTH_DIF)
             ->where_lte('charLength', $leng + self::$LENGTH_DIF)
             ->find_one();
      if ($lexeme) {
        $lexemes[] = $lexeme;
        if (count($lexemes) == self::$MAX_RESULTS) {
          break;
        }
      }
    }

    // Sort the lexemes by their Levenshtein distance from $cuv
    $distances = [];
    foreach ($lexemes as $lexeme) {
      $distances[] = Levenshtein::dist($cuv, $lexeme->formNoAccent);
    }
    array_multisort($distances, $lexemes);

    // load the entries for each lexeme
    $entries = [];
    foreach ($lexemes as $l) {
      $entries = array_merge($entries, $l->getEntries());
    }
    $entries = array_unique($entries, SORT_REGULAR);

    return $entries;
  }
  
  /* Find lexemes with at least 50% matching n-grams */
  static function searchLexemeIds($cuv) {
    $ngramList = self::split($cuv);
    $hash = [];
    foreach ($ngramList as $i => $ngram) {
      $lexemeIdList = DB::getArray(sprintf("select lexemeId from NGram where ngram = '%s' and pos between %d and %d",
                                         $ngram, $i - self::$MAX_MOVE, $i + self::$MAX_MOVE));
      $lexemeIdList = array_unique($lexemeIdList);
      foreach($lexemeIdList as $lexemeId) {
        if (!isset($hash[$lexemeId])) {
          $hash[$lexemeId] = 1;
        } else {
          $hash[$lexemeId]++;
        }
      }
    }

    $minLength = mb_strlen($cuv) / 2;
    $hash = array_filter($hash, function($val) use($minLength) {
        return ($val >= $minLength);
      });
    return $hash;
  }
}
