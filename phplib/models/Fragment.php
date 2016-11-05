<?php

/* A fragment is a part of a compound lexem */

class Fragment extends BaseObject implements DatedObject {
  public static $_table = 'Fragment';

  const DEC_FULL = 1;
  const DEC_ARTICLE = 2;
  const DEC_NO_ARTICLE = 3;
  const DEC_INVARIABLE = 4;
  const DEC_INVARIABLE_PLURAL = 5;
  const DEC_INVARIABLE_DATIVE = 6;

  public static $DEC_NAMES = [
    self::DEC_FULL  => 'flexiune completă',
    self::DEC_ARTICLE  => 'articulat',
    self::DEC_NO_ARTICLE  => 'nearticulat',
    self::DEC_INVARIABLE  => 'invariabil',
    self::DEC_INVARIABLE_PLURAL  => 'invariabil plural',
    self::DEC_INVARIABLE_DATIVE  => 'invariabil dativ',
  ];

  public static $INV_RULES = [
    self::DEC_INVARIABLE => [
      // no other restrictions
    ],
    self::DEC_INVARIABLE_PLURAL => [
      'number' => Inflection::NUMBER_PLURAL,
      'case' => Inflection::CASE_NOMINATIVE,
      'article' => Inflection::ARTICLE_NONE,
    ],
    self::DEC_INVARIABLE_DATIVE => [
      'number' => Inflection::NUMBER_SINGULAR,
      'case' => Inflection::CASE_DATIVE,
      'article' => Inflection::ARTICLE_DEFINITE,
    ],
  ];

  static function create($partId, $declension, $capitalized, $rank) {
    $f = Model::factory('Fragment')->create();
    $f->partId = $partId;
    $f->declension = $declension;
    $f->capitalized = $capitalized;
    $f->rank = $rank;
    return $f;
  }

  // Given
  //
  // * the desired inflection for the compound lexeme,
  // * the model type of the part lexeme,
  // * and the declension type for the part lexeme,
  //
  // decide which inflection of the part lexeme we need to look at
  static function getInflection($infl, $partModelType, $declension) {
    if (array_key_exists($declension, self::$INV_RULES)) {
      // return the lowest rank inflection
      $query =  Model::factory('Inflection')
             ->table_alias('i')
             ->select('i.*')
             ->join('ModelType', ['i.modelType', '=', 'mt.canonical'], 'mt')
             ->where('mt.code', $partModelType);

      foreach (self::$INV_RULES[$declension] as $field => $value) {
        $query = $query->where($field, $value);
      }

      return $query->order_by_asc('i.rank')->find_one();
    }

    // obey DEC_ARTICLE and DEC_NO_ARTICLE
    switch ($declension) {
      case self::DEC_FULL: $desiredArticle = $infl->article; break;
      case self::DEC_ARTICLE: $desiredArticle = Inflection::ARTICLE_DEFINITE; break;
      case self::DEC_NO_ARTICLE: $desiredArticle = Inflection::ARTICLE_NONE; break;
    }

    return Model::factory('Inflection')
      ->table_alias('i')
      ->select('i.*')
      ->join('ModelType', ['i.modelType', '=', 'mt.canonical'], 'mt')
      ->where('mt.code', $partModelType)
      ->order_by_expr("(gender = {$infl->gender}) desc")
      ->order_by_expr("(number = {$infl->number}) desc")
      ->order_by_expr("(`case` = {$infl->case}) desc")
      ->order_by_expr("(article = {$desiredArticle}) desc")
      ->order_by_asc('i.rank')
      ->find_one();
  }
}

?>
