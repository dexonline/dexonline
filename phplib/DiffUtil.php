<?php
/**
 * Description of DiffUtil
 * @holds diff specific constants and functions
 */

require_once __DIR__ . '/third-party/FineDiff.php';

class DiffUtil {

  const DIFF_GRANULARITY_CHARACTER = 0;
  const DIFF_GRANULARITY_WORD = 1;
  const DIFF_GRANULARITY_SENTENCE = 2;
  const DIFF_GRANULARITY_PARAGRAPH = 3;

  const DIFF_ENGINE_FINEDIFF = 1;
  const DIFF_ENGINE_LDIFF = 2;

  public static $DIFF_ENGINE_NAMES = [
    self::DIFF_ENGINE_FINEDIFF => 'FineDiff',
    self::DIFF_ENGINE_LDIFF => 'LDiff',
  ];

  public static $DIFF_GRANULARITY_NAMES = [
    self::DIFF_GRANULARITY_CHARACTER => 'caracter',
    self::DIFF_GRANULARITY_WORD => 'cuvânt',
    self::DIFF_GRANULARITY_SENTENCE => 'propoziție',
    self::DIFF_GRANULARITY_PARAGRAPH => 'paragraf',
  ];

  // returns the equivalent FineDiff granularity of a DiffUtil granularity
  static function getFineDiffGranularity($granularity) {
    switch ($granularity) {
      case self::DIFF_GRANULARITY_CHARACTER: return FineDiff::$characterGranularity;
      case self::DIFF_GRANULARITY_WORD: return FineDiff::$wordGranularity;
      case self::DIFF_GRANULARITY_SENTENCE: return FineDiff::$sentenceGranularity;
      case self::DIFF_GRANULARITY_PARAGRAPH: return FineDiff::$paragraphGranularity;
      default: die("Granularitate necunoscută\n");
    }
  }
}
