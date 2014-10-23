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
                                               "'y", "'Y", ':y', ':Y', '~z', '~Z',
                                               '‑', '—', ' ◊ ', ' ♦ ', '„', '”'),
                          'unicode' => array('ấ', 'Ấ', 'ấ', 'Ấ', 'ắ', 'Ắ', 'ắ', 'Ắ', 'ắ', 'Ắ',
                                             'ă', 'Ă', 'â', 'Â', 'á', 'Á', 'à', 'À', 'ä', 'Ä', 'å', 'Å',
                                             'ç', 'Ç', 'ć', 'Ć', 'č', 'Č', 'é', 'É', 'è', 'È', 'ê', 'Ê',
                                             'ë', 'Ë', 'ĕ', 'Ĕ', 'ğ', 'Ğ', 'î́', 'Î́', 'î́', 'Î́',
                                             'í', 'Í', 'ì', 'Ì', 'î', 'Î', 'ï', 'Ï', 'ĭ', 'Ĭ', 'ñ', 'Ñ',
                                             'ó', 'Ó', 'ò', 'Ò', 'ô', 'Ô', 'ö', 'Ö', 'õ', 'Õ', 'ř', 'Ř',
                                             'š', 'Š', 'ș', 'Ș', 'ț', 'Ț', 'ș', 'Ș', 'ț', 'Ț',
                                             'ú', 'Ú', 'ù', 'Ù', 'û', 'Û', 'ü', 'Ü', 'ŭ', 'Ŭ',
                                             'ý', 'Ý', 'ÿ', 'Ÿ', 'ž', 'Ž',
                                             '-', '-', ' * ', ' ** ', '"', '"'),
                          'latin' => array('a', 'A', 'a', 'A', 'a', 'A', 'a', 'A', 'a', 'a',
                                           'a', 'A', 'a', 'A', 'a', 'A', 'a', 'A', 'a', 'A', 'a', 'A',
                                           'c', 'C', 'c', 'C', 'c', 'C', 'e', 'E', 'e', 'E', 'e', 'E',
                                           'e', 'E', 'e', 'E', 'g', 'G', 'i', 'I', 'i', 'I',
                                           'i', 'I', 'i', 'I', 'i', 'I', 'i', 'I', 'i', 'I', 'n', 'N',
                                           'o', 'O', 'o', 'O', 'o', 'O', 'o', 'O', 'o', 'O', 'r', 'R',
                                           's', 'S', 's', 'S', 't', 'T', 's', 'S', 't', 'T',
                                           'u', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'U',
                                           'y', 'Y', 'y', 'Y', 'z', 'Z',
                                           '-', '-', ' * ', ' ** ', '"', '"'));

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
    $query = mb_substr($query, 0, 40);   // put a hard limit on query length
    $query = str_replace(array('"', "'"), array("", ""), $query);
    if (self::startsWith($query, 'a se ')) {
      $query = substr($query, 5);
    } else if (self::startsWith($query, 'a ')) {
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
    return str_replace(array("@", "."), array(" [AT] ", " [DOT] "), $email);
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

  static function isSpam($s) {
    return preg_match("/\\[url=/i", $s);
  }

  /** Like the standard explode(), but filters out zero-length components **/
  static function explode($delimiter, $s) {
    return array_values(array_filter(explode($delimiter, $s), 'strlen'));
  }


  /** Kudos http://www.php.net/manual/pt_BR/function.parse-url.php#107291 **/
  static function parseUtf8Url($url) {
    static $keys = array('scheme'=>0,'user'=>0,'pass'=>0,'host'=>0,'port'=>0,'path'=>0,'query'=>0,'fragment'=>0);
    if (is_string($url) && preg_match('~^((?P<scheme>[^:/?#]+):(//))?((\\3|//)?(?:(?P<user>[^:]+):(?P<pass>[^@]+)@)?(?P<host>[^/?:#]*))(:(?P<port>\\d+))?' .
                                      '(?P<path>[^?#]*)(\\?(?P<query>[^#]*))?(#(?P<fragment>.*))?~u', $url, $matches)) {
      return $matches;
    }
    return false;
  }

	/**
   * Cleans up a URL in various ways:
   * - trims any known index files and extensions (passed as arguments)
   * - replaces consecutive slashes with a single slash;
   * - trims any final slashes
   * Assumes the URL includes a protocol.
   * @param $indexFile Index file name (without extension)
   * @param $indexExt Array of index file extensions
   **/
	static function urlCleanup($url, $indexFile, $indexExt) {
    // Delete any fragment
    $pos = strrpos($url, '#');
    if ($pos !== false) {
      $url = substr($url, 0, $pos);
    }

    // Scroll through the extension list until we find one that matches
    $i = 0;
    $found = false;
    do {
      $target = $indexFile . '.' . $indexExt[$i];
			if (self::endsWith($url, $target)) {
        $url = substr($url, 0, -strlen($target));
        $found = true;
      }
      $i++;
    } while (($i < count($indexExt)) && !$found);

    // Save the protocol first
    $parts = explode('//', $url, 2);

    // Replace //+ by /
    $parts[1] = preg_replace('#//+#', '/', $parts[1]);

    // Delete any trailing slashes
    $parts[1] = rtrim($parts[1], '/');

    // Reassemble and return the URL
		return implode('//', $parts);
	}

  // Same as str_pad, but multibyte-safe
  static function pad($input, $padLength, $padString = ' ', $padType = STR_PAD_RIGHT) { 
    return str_pad($input, $padLength + strlen($input) - mb_strlen($input), $padString, $padType); 
  }

  /* Make a string portable across OS's by replacing '/' with DIRECTORY_SEPARATOR */
  static function portable($s) {
    return str_replace('/', DIRECTORY_SEPARATOR, $s);
  }
}

?>
