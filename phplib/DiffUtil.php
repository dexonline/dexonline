<?php
/**
 * Description of DiffUtil
 * @holds diff specific constants and functions
 */
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
  
}
