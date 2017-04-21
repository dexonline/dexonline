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
    $results = array();
    for ($i = 0; $i < $len - self::$NGRAM_SIZE + 1; $i++) {
      $results[] = mb_substr($s, $i, self::$NGRAM_SIZE);
    }
    return $results;
  }

  static function searchNGram($cuv) {
    $cuv = self::canonicalize($cuv);
    $leng = mb_strlen($cuv);
    
    $hash = NGram::searchLexemIds($cuv);
    if (empty($hash)) {
      return [];
    }
    arsort($hash);
    $max = current($hash);
    $lexIds = array_keys($hash, $max);

    $lexems = [];
    foreach ($lexIds as $id) {
      $lexem = Model::factory('Lexem')
             ->where('id', $id)
             ->where_gte('charLength', $leng - self::$LENGTH_DIF)
             ->where_lte('charLength', $leng + self::$LENGTH_DIF)
             ->find_one();
      if ($lexem) {
        $lexems[] = $lexem;
        if (count($lexems) == self::$MAX_RESULTS) {
          break;
        }
      }
    }

    // Sort the lexems by their Levenshtein distance from $cuv
    $distances = [];
    foreach ($lexems as $lexem) {
      $distances[] = Levenshtein::dist($cuv, $lexem->formNoAccent);
    }
    array_multisort($distances, $lexems);

    // load the entries for each lexeme
    $entries = [];
    foreach ($lexems as $l) {
      $entries = array_merge($entries, $l->getEntries());
    }
    $entries = array_unique($entries, SORT_REGULAR);

    return $entries;
  }
  
  /* Find lexems with at least 50% matching n-grams */
  static function searchLexemIds($cuv) {
    $ngramList = self::split($cuv);
    $hash = array();
    foreach ($ngramList as $i => $ngram) {
      $lexemIdList = DB::getArray(sprintf("select lexemId from NGram where ngram = '%s' and pos between %d and %d",
                                         $ngram, $i - self::$MAX_MOVE, $i + self::$MAX_MOVE));
      $lexemIdList = array_unique($lexemIdList);
      foreach($lexemIdList as $lexemId) {
        if (!isset($hash[$lexemId])) {
          $hash[$lexemId] = 1;
        } else {
          $hash[$lexemId]++;
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

?>
