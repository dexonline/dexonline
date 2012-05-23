<?php

class StringUtil {
  static $LETTERS = array('shorthand' => array("'^a", "'^A", "^'a", "^'A", "'~a", "'~A", "~'a", "~'A", "'ă", "'Ă",
                                               '~a', '~A', '^a', '^A', "'a", "'A", '`a', '`A', ':a', ':A', '°a', '°A',
                                               ',c', ',C', "'c", "'C", '~c', '~C', "'e", "'E", '`e', '`E', '^e', '^E',
                                               ':e', ':E', '~e', '~E', '~g', '~G', "'^i", "'^I", "^'i", "^'I",
                                               "'i", "'I", '`i', '`I', '^i', '^I', ':i', ':I', '~i', '~I', '~n', '~N',
                                               "'o", "'O", '`o', '`O', '^o', '^O', ':o', ':O', '~o', '~O', '~r', '~R',
                                               '~s', '~S', ',s', ',S', ',t', ',T', 'ş', 'Ş', 'ţ', 'Ţ',
                                               "'u", "'U", '`u', '`U', '^u', '^U', ':u', ':U', '~u', '~U',
                                               "'y", "'Y", ':y', ':Y', '~z', '~Z'),
                          'unicode' => array('ấ', 'Ấ', 'ấ', 'Ấ', 'ắ', 'Ắ', 'ắ', 'Ắ', 'ắ', 'Ắ',
                                             'ă', 'Ă', 'â', 'Â', 'á', 'Á', 'à', 'À', 'ä', 'Ä', 'å', 'Å',
                                             'ç', 'Ç', 'ć', 'Ć', 'č', 'Č', 'é', 'É', 'è', 'È', 'ê', 'Ê',
                                             'ë', 'Ë', 'ĕ', 'Ĕ', 'ğ', 'Ğ', 'î́', 'Î́', 'î́', 'Î́',
                                             'í', 'Í', 'ì', 'Ì', 'î', 'Î', 'ï', 'Ï', 'ĭ', 'Ĭ', 'ñ', 'Ñ',
                                             'ó', 'Ó', 'ò', 'Ò', 'ô', 'Ô', 'ö', 'Ö', 'õ', 'Õ', 'ř', 'Ř',
                                             'š', 'Š', 'ș', 'Ș', 'ț', 'Ț', 'ș', 'Ș', 'ț', 'Ț',
                                             'ú', 'Ú', 'ù', 'Ù', 'û', 'Û', 'ü', 'Ü', 'ŭ', 'Ŭ',
                                             'ý', 'Ý', 'ÿ', 'Ÿ', 'ž', 'Ž'),
                          'latin' => array('a', 'A', 'a', 'A', 'a', 'A', 'a', 'A', 'a', 'a',
                                           'a', 'A', 'a', 'A', 'a', 'A', 'a', 'A', 'a', 'A', 'a', 'A',
                                           'c', 'C', 'c', 'C', 'c', 'C', 'e', 'E', 'e', 'E', 'e', 'E',
                                           'e', 'E', 'e', 'E', 'g', 'G', 'i', 'I', 'i', 'I',
                                           'i', 'I', 'i', 'I', 'i', 'I', 'i', 'I', 'i', 'I', 'n', 'N',
                                           'o', 'O', 'o', 'O', 'o', 'O', 'o', 'O', 'o', 'O', 'r', 'R',
                                           's', 'S', 's', 'S', 't', 'T', 's', 'S', 't', 'T',
                                           'u', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'U',
                                           'y', 'Y', 'y', 'Y', 'z', 'Z'));

  public static $STOPWORDS = array("adj", "al", "ale", "art", "ca", "care", "ce", "cu", "de", "despre", "din", "dinspre", "după", "este",
                                   "etc", "expr", "face", "fi", "fig", "fr", "în", "îi", "îți", "lat", "la", "mai", "nu", "pe", "pentru",
                                   "pl", "pop", "pr", "prez", "prin", "refl", "reg", "sau", "să", "se", "sil", "sg", "suf", "și", "te",
                                   "tine", "tranz", "tu", "ți", "ție", "un", "unor", "unui", "var", "vb");
  private static $STOPWORDS_LATIN = null; // will be initialized lazily

  // Note: This does not handle the mixed case of old orthgraphy and no diacriticals (e.g. inminind instead of înmânând).
  // That case is inherently ambiguous. For example, if the query is 'gindind', the correct substitution is 'gândind',
  // where the second 'i' is left unchanged.
  public static function tryOldOrthography($cuv) {
    if (preg_match('/^sînt(em|eți)?$/', $cuv)) {
      return str_replace('î', 'u', $cuv);
    }

    if (mb_strlen($cuv) > 2) {
      $interior = mb_substr($cuv, 1, mb_strlen($cuv) - 2);
      if (mb_stripos($interior, 'î') !== FALSE) {
        return self::getCharAt($cuv, 0) . str_replace('î', 'â', $interior) . self::getLastChar($cuv);
      }
    }

    return NULL;
  }

  public static function unicodeToLatin($s) {
    return str_replace(self::$LETTERS['unicode'], self::$LETTERS['latin'], $s);
  }

  static function endsWith($string, $substring) {
    $lenString = strlen($string);
    $lenSubstring = strlen($substring);
    $endString = substr($string, $lenString - $lenSubstring, $lenSubstring);
    return $endString == $substring;
  }

  static function startsWith($string, $substring) {
    $startString = substr($string, 0, strlen($substring));
    return $startString == $substring;
  }

  /**
   * True if it contains any Unicode (but non-Latin) letters.
   */
  static function hasDiacritics($s) {
    $len = mb_strlen($s);
    for ($i = 0; $i < $len; $i++) {
      $char = self::getCharAt($s, $i);
      if (in_array($char, self::$LETTERS['unicode'])) {
        return true;
      }
    }
    return false;
  }

  static function hasRegexp($s) {
    $len = mb_strlen($s);
    $count = 0;
    $state = 0;
    for ($i = 0; $i < $len; $i++) {
      $char = self::getCharAt($s, $i);
      switch ($state) {
      case 0: // no special char seen so far
        if ($char == '[') {
          $count++;
          $state = 2;
        } else if ($char == ']') {
          return false;
        } else if ($char == '|' || $char == '?' || $char == '*') {
          $state = 1;
        }
        break;
      case 1: // normal state, specials were seen
        if ($char == '[') {
          $count++;
          $state = 2;
        } else if ($char == ']') {
          if ($count == 0) {
            return false;
          }
          $count--;
        }
        break;
      case 2: // after a [; won't count ]
        if ($char == '[') {
          $count++;
        } else if ($char == '^') {
          $state = 3;
        } else {
          $state = 1;
        }
        break;
      case 3: // after [^; still won't count ]
        if ($char == '[') {
          $count++;
          $state = 2;
        } else {
          $state = 1;
        }
        break;
      }
    }
    return $count == 0 && $state > 0;
  }

  static function isAllDigits($s) {
    $len = mb_strlen($s);
    for ($i = 0; $i < $len; $i++) {
      $char = self::getCharAt($s, $i);
      if (!ctype_digit($char)) {
        return false;
      }
    }
    return true;
  }

  static function isUppercase($s) {
    return $s != mb_strtolower($s);
  }

  static function getCharAt($s, $index) {
    return mb_substr($s, $index, 1);
  }

  static function getLastChar($s) {
    return self::getCharAt($s, mb_strlen($s) - 1);
  }

  static function dropLastChar($s) {
    return mb_substr($s, 0, mb_strlen($s) - 1);
  }

  static function cleanupQuery($query) {
    $query = str_replace(array('"', "'"), array("", ""), $query);
    if (self::startsWith($query, 'a ')) {
      $query = substr($query, 2);
    }
    $query = trim($query);
    $query = strip_tags($query);
    $query = self::stripHtmlEscapeCodes($query);
    // Delete all kinds of illegal symbols, but use them as word delimiters. Allow dots, dashes and spaces
    $query = preg_replace("/[!@#$%&()_+=\\\\{}'\":;<>,\/]/", " ", $query);
    $query = preg_replace("/\s+/", " ", $query);
    $query = mb_substr($query, 0, 50);
    return $query;
  }

  static function dexRegexpToMysqlRegexp($s) {
    if (preg_match("/[|\[\]]/", $s)) {
      return "rlike '^(" . str_replace(array("*", "?"), array(".*", "."), $s) .
        ")$'";
    } else {
      return "like '" . str_replace(array("*", "?"), array("%", "_"), $s) . "'";
    }
  }

  /** Generates a set of clauses usable for counting or fetching results */
  static function analyzeQuery($query) {
    $hasDiacritics = self::hasDiacritics($query);
    $hasRegexp = self::hasRegexp($query);
    $isAllDigits = self::isAllDigits($query);

    return array($hasDiacritics, $hasRegexp, $isAllDigits);
  }

  static function scrambleEmail($email) {
    return str_replace(array("@", "."), array("AT", "DOT"), $email);
  }

  static function reverse($s) {
    $result = '';
    $len = mb_strlen($s);
    for ($i = 0; $i < $len; $i++) {
      $char = self::getCharAt($s, $i);
      $result = $char . $result;
    }
    return $result;
  }

  static function validateAlphabet($s, $alphabet) {
    $len = mb_strlen($s);
    for ($i = 0; $i < $len; $i++) {
      $c = self::getCharAt($s, $i);
      $found = mb_strpos($alphabet, $c);
      if ($found === false) {
        return false;
      }
    }
    return true;
  }

  static function isStopWord($word, $hasDiacritics) {
    if (!self::$STOPWORDS_LATIN) {
      self::$STOPWORDS_LATIN = self::unicodeToLatin(self::$STOPWORDS);
    }
    if (mb_strlen($word) == 1) {
      return true;
    }
    return $hasDiacritics
      ? in_array($word, self::$STOPWORDS)
      : in_array($word, self::$STOPWORDS_LATIN);
  }
  
  static function separateStopWords($words, $hasDiacritics) {
    $properWords = array();
    $stopWords = array();
    
    foreach ($words as $word) {
      if (self::isStopWord($word, $hasDiacritics)) {
        $stopWords[] = $word;
      } else {
        $properWords[] = $word;
      }
    }

    return array($properWords, $stopWords);
  }

  static function stripHtmlEscapeCodes($s) {
    return preg_replace("/&[^;]+;/", "", $s);
  }

  static function replace_st($tpl_output) {
    return str_replace(array('ș', 'Ș', 'ț', 'Ț'), array('ş', 'Ş', 'ţ', 'Ţ'), $tpl_output);
  }

  static function replace_ai($tpl_output) {
    $char_map = array(
                      'â' => 'î',
                      'Â' => 'Î',
                      'ấ'  => '\'î',
                      'Ấ' => '\'Î',
                      );

    foreach ($char_map as $a => $i) {
      $tpl_output = str_replace($a, $i, $tpl_output);
      $tpl_output = preg_replace("/(r(?:o|u)m)$i(n)/i", "\${1}$a\${2}", $tpl_output);
    }

    // sunt(em,eți) -> sînt(em,eți)
    // TODO: This still doesn't work well for the search 'fi' when the paradigm is expanded.
    // The paradigm contains some apostrophes which trip the regexp.
    $tpl_output = preg_replace("/(\W)sunt(em|eți)?/i", "\${1}sînt\${2}", $tpl_output);
    return $tpl_output;
  }

  /** Simple wrapper to call parse_str and return the array it produces **/
  static function parseStr($s) {
    $result = array();
    parse_str($s, $result);
    return $result;
  }

  static function shortenString($s, $maxLength) {
    $l = mb_strlen($s);
    if ($l >= $maxLength + 3) {
      return mb_substr($s, 0, $maxLength - 3) . '...';
    }
    return $s;
  }
}

?>
