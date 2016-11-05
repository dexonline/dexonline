<?php

/* A fragment is a part of a compound lexem */

class Fragment extends BaseObject implements DatedObject {
  public static $_table = 'Fragment';

  const DEC_FULL = 1;
  const DEC_NO_ARTICLE = 2;
  const DEC_NO_ARTICLE_NOMINATIVE = 3;
  const DEC_INVARIABLE_N_SG = 4;
  const DEC_INVARIABLE_N_PL = 5;
  const DEC_INVARIABLE_D_SG = 6;
  const DEC_INVARIABLE_D_PL = 7;

  public static $DEC_NAMES = [
    self::DEC_FULL  => 'flexiune completă',
    self::DEC_NO_ARTICLE  => 'nearticulat',
    self::DEC_NO_ARTICLE_NOMINATIVE  => 'nearticulat n.-ac.',
    self::DEC_INVARIABLE_N_SG  => 'invariabil n.-ac. sg.',
    self::DEC_INVARIABLE_N_PL  => 'invariabil n.-ac. pl.',
    self::DEC_INVARIABLE_D_SG  => 'invariabil d.-g. sg.',
    self::DEC_INVARIABLE_D_PL  => 'invariabil d.-g. pl.',
  ];

  // Helper table that translates compound inflections into fragment inflections.
  // "null" means "the fragment mimics the compound inflection".
  public static $INV_RULES = [
    self::DEC_FULL => [
      'gender' => null,
      'number' => null,
      'case' => null,
      'article' => null,
    ],
    self::DEC_NO_ARTICLE => [
      'gender' => null,
      'number' => null,
      'case' => null,
      'article' => Inflection::ARTICLE_NONE,
    ],
    self::DEC_NO_ARTICLE_NOMINATIVE => [
      'gender' => null,
      'number' => null,
      'case' => Inflection::CASE_NOMINATIVE,
      'article' => Inflection::ARTICLE_NONE,
    ],
    self::DEC_INVARIABLE_N_SG => [
      // no restrictions -- ordering by inflection rank should suffice
    ],
    self::DEC_INVARIABLE_N_PL => [
      'number' => Inflection::NUMBER_PLURAL,
      'case' => Inflection::CASE_NOMINATIVE,
      'article' => Inflection::ARTICLE_NONE,
    ],
    self::DEC_INVARIABLE_D_SG => [
      'number' => Inflection::NUMBER_SINGULAR,
      'case' => Inflection::CASE_DATIVE,
      'article' => Inflection::ARTICLE_DEFINITE,
    ],
    self::DEC_INVARIABLE_D_PL => [
      'number' => Inflection::NUMBER_PLURAL,
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
    $query = Model::factory('Inflection')
           ->table_alias('i')
           ->select('i.*')
           ->join('ModelType', ['i.modelType', '=', 'mt.canonical'], 'mt')
           ->where('mt.code', $partModelType);

    foreach (self::$INV_RULES[$declension] as $field => $value) {
      if ($value === null) {
        $value = $infl->$field; // mimic compund inflection
      }
      $query = $query->order_by_expr("(`{$field}` = {$value}) desc");
    }

    return $query->order_by_asc('i.rank')->find_one();
  }
}

?>
