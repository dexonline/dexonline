<?php

/* A fragment is a part of a compound lexeme */

class Fragment extends BaseObject implements DatedObject {
  public static $_table = 'Fragment';

  const DEC_FULL = 1;
  const DEC_NO_ARTICLE = 2;
  const DEC_NO_ARTICLE_NOMINATIVE = 3;
  const DEC_INVARIABLE = 4;
  const DEC_NOMINATIVE = 5;

  public static $DEC_NAMES = [
    self::DEC_INVARIABLE => 'invariabil',
    self::DEC_FULL => 'flexiune completă',
    self::DEC_NO_ARTICLE => 'nearticulat',
    self::DEC_NOMINATIVE => 'n.-ac.',
    self::DEC_NO_ARTICLE_NOMINATIVE => 'nearticulat n.-ac.',
  ];

  // Helper table that translates compound inflections into fragment inflections.
  // "null" means "the fragment mimics the compound inflection".
  public static $INV_RULES = [
    self::DEC_INVARIABLE => [
      // special case, handled in Lexem.php
    ],
    self::DEC_FULL => [
      'gender' => null,
      'number' => null,
      'article' => null,
      'case' => null,
    ],
    self::DEC_NO_ARTICLE => [
      'gender' => null,
      'number' => null,
      'article' => Inflection::ARTICLE_NONE,
      'case' => null,
    ],
    self::DEC_NOMINATIVE => [
      'gender' => null,
      'number' => null,
      'article' => null,
      'case' => Inflection::CASE_NOMINATIVE,
    ],
    self::DEC_NO_ARTICLE_NOMINATIVE => [
      'gender' => null,
      'number' => null,
      'article' => Inflection::ARTICLE_NONE,
      'case' => Inflection::CASE_NOMINATIVE,
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
  // decide which inflection of the part lexeme we need to look at.
  // Returns all the legal inflections in decreasing order of desirability.
  // This helps with lexemes that don't have all the forms (e.g. vocative is missing).
  static function getInflections($infl, $partModelType, $declension) {
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

    return $query->order_by_asc('i.rank')->find_many();
  }
}
