<?php

class StringUtil {
  // prefixes which should be followed by 'î', not 'â'
  static $I_PREFIXES = [
    'auto',
    'bine',
    'bun',
    'cap',
    'co',
    'de',
    'dez', // false positive: "dezânoaie"
    'ex',
    'ne',
    'nemai',
    'ori',
    'prea',
    'pre',
    're',
    'semi',
    'sub',
    'supra',
    'ultra',
    // false negatives: "altîncotro"
  ];

  // Convert old (î) orthography to new (â) orthography.
  // Assumes $s uses diacritics (if needed).
  static function convertOrthography($s) {
    $s = preg_replace("/\bsînt(?=\b|em\b|\eți)/u", 'sunt', $s);
    $s = preg_replace("/\bSÎNT(?=\b|EM\b|\EȚI)/u", 'SUNT', $s);

    // replace î with â unless it's at the beginning, the end or after a prefix
    $r = '\b';
    foreach (self::$I_PREFIXES as $prefix) {
      $r .= "|\b" . $prefix;
    }
    $s = preg_replace("/(?<!{$r})î(?=.)/u", 'â', $s);

    // same, but for uppercase
    $r = '\b';
    foreach (self::$I_PREFIXES as $prefix) {
      $r .= "|\b" . mb_strtoupper($prefix);
    }
    $s = preg_replace("/(?<!{$r})Î(?=.)/u", 'Â', $s);
    return $s;
  }

  static function unicodeToLatin($s) {
    return iconv('UTF-8', 'ASCII//TRANSLIT', $s);
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
    return $s != self::unicodeToLatin($s);
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
    $query = str_replace(
      ['"', "'", 'ấ', 'Ấ', 'î́', 'Î́'],
      ["", "", 'â', 'Â', 'î', 'Î'],
      $query);
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
    $query = preg_replace("/\s+/", ' ', $query);
    $query = self::convertOrthography($query);
    $query = mb_substr($query, 0, 50);
    return $query;
  }

  static function dexRegexpToMysqlRegexp($s) {
    if (preg_match("/[|\[\]]/", $s)) {
      return "rlike '^(" . str_replace(['*', '?'], ['.*', '.'], $s) . ")$'";
    } else {
      return "like '" . str_replace(['*', '?'], ['%', '_'], $s) . "'";
    }
  }

  static function scrambleEmail($email) {
    return str_replace(['@', '.'], [' [AT] ', ' [DOT] '], $email);
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

  static function stripHtmlEscapeCodes($s) {
    return preg_replace("/&[^;]+;/", "", $s);
  }

  static function replace_st($tpl_output) {
    return str_replace(['ș', 'Ș', 'ț', 'Ț'], ['ş', 'Ş', 'ţ', 'Ţ'], $tpl_output);
  }

  static function replace_ai($tpl_output) {
    $char_map = [
      'â' => 'î',
      'Â' => 'Î',
      'ấ'  => 'î́',
      'Ấ' => 'Î́',
    ];

    foreach ($char_map as $a => $i) {
      // workaround for the fact that /\b{$a}\b/u doesn't work.
      // see http://stackoverflow.com/questions/2432868/php-regex-word-boundary-matching-in-utf-8
      $tpl_output = preg_replace("/(?<=[A-Za-zĂȘȚășț]){$a}(?=[A-Za-zĂȘȚășț])/",
                                 "$1{$i}$2", $tpl_output);
      $tpl_output = preg_replace("/(r[ou]m)$i(n)/i", "\${1}$a\${2}", $tpl_output);
    }

    // sunt(em,eți) -> sînt(em,eți)
    $tpl_output = preg_replace("/(\W)sunt(em|eți)?/i", "\${1}sînt\${2}", $tpl_output);

    // Handle some accented letters in paradigms. Accents are denoted by a class.
    $a = "<span class=\"tonic-accent\">â</span>";
    $i = "<span class=\"tonic-accent\">î</span>";
    $u = "<span class=\"tonic-accent\">u</span>";

    // â -> î
    $tpl_output = str_replace($a, $i, $tpl_output);
    
    // súnt(em,eți) -> s'înt(em,eți)
    $tpl_output = preg_replace("#(\W)s{$u}nt(em|eți)?#i", "\${1}s{$i}nt\${2}", $tpl_output);

    return $tpl_output;
  }

  /** Simple wrapper to call parse_str and return the array it produces **/
  static function parseStr($s) {
    $result = [];
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
    return (stristr($s, '[url=') !== false) ||
      (stristr($s, 'http://') !== false);
  }

  // Same as str_pad, but multibyte-safe
  static function pad($input, $padLength, $padString = ' ', $padType = STR_PAD_RIGHT) { 
    return str_pad($input, $padLength + strlen($input) - mb_strlen($input), $padString, $padType); 
  }

  /* Make a string portable across OS's by replacing '/' with DIRECTORY_SEPARATOR */
  static function portable($s) {
    return str_replace('/', DIRECTORY_SEPARATOR, $s);
  }

  /* Place a css class around the letter bearing the tonic accent */
  static function highlightAccent($s) {
    return preg_replace("/\'(.)/u",
                        "<span class=\"tonic-accent\">\$1</span>",
                        $s);
  }

  static function randomCapitalLetters($length) {
    $result = '';
    for ($i = 0; $i < $length; $i++) {
      $result .= chr(rand(0, 25) + ord('A'));
    }
    return $result;
  }

  /**
   * Returns the preposition needed for numerals ending with 20->99 
   * @param string $amount
   * @return string either ␣ or ␣de
   */
  // TODO - return singular (in case of 1) or plural (in every other cases, including 0) of $article for specified $amount
  // TODO - do we needed it for negative numbers?
  static function getAmountPreposition($amount, $article = null) {
    $de = substr($amount, -2);
    return ($de > 0 && $de < 20) ? "" : " de";
  }
}

?>
