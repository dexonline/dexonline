<?php

class StringUtil {
  static $LETTERS = [
    'shorthand' => ["'a", "'A", "'ă", "'Ă", "'â", "'Â", "'e", "'E", "'i", "'I",
                    "'î", "'Î", "'o", "'O", "'ö", "'Ö", "'u", "'U", "'y", "'Y",
                    '‑', '—', ' ◊ ', ' ♦ ', '„', '”'],
    'unicode' => ['á', 'Á', 'ắ', 'Ắ', 'ấ', 'Ấ', 'é', 'É', 'í', 'Í',
                  'î́', 'Î́', 'ó', 'Ó', "ö́", "Ö́", 'ú', 'Ú', 'ý', 'Ý',
                  '-', '-', ' * ', ' ** ', '"', '"'],
  ];

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
    if (preg_match('/^sînt(em|eți)?$/ui', $s)) {
      return str_replace(['î', 'Î'], ['u', 'U'], $s);
    } else {
      // replace î with â unless it's at the beginning, the end or after a prefix
      $r = '';
      foreach (self::$I_PREFIXES as $p) {
        $r .= "(?<!^{$p})";
      }
      $s = preg_replace("/(?<!^){$r}î(?=.)/u", 'â', $s);
      $s = preg_replace("/(?<!^){$r}Î(?=.)/u", 'Â', $s);
      return $s;
    }
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

  static function stripHtmlEscapeCodes($s) {
    return preg_replace("/&[^;]+;/", "", $s);
  }

  static function replace_st($tpl_output) {
    return str_replace(array('ș', 'Ș', 'ț', 'Ț'), array('ş', 'Ş', 'ţ', 'Ţ'), $tpl_output);
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
    $a = "<span class=\"accented\">â</span>";
    $i = "<span class=\"accented\">î</span>";
    $u = "<span class=\"accented\">u</span>";

    // â -> î
    $tpl_output = str_replace($a, $i, $tpl_output);
    
    // súnt(em,eți) -> s'înt(em,eți)
    $tpl_output = preg_replace("#(\W)s{$u}nt(em|eți)?#i", "\${1}s{$i}nt\${2}", $tpl_output);

    return $tpl_output;
  }

  // Post-filter for April Fools' Day, 2015
  static function iNoGrammer($s) {
    // Getting ready to remove hyphens
    $s = preg_replace("/(\w)-(\w)/u", "\${1}###\${2}", $s);
    $s = preg_replace("/(\W)ca(\W)/u", "\${1}k\${2}", $s);
    $s = preg_replace("/(\W)sau(\W)/u", "\${1}s-au\${2}", $s);
    $s = preg_replace("/(\W)la(\W)/u", "\${1}l-a\${2}", $s);
    $s = preg_replace("/(\W)pe care(\W)/u", "\${1}care\${2}", $s);
    $s = preg_replace("/(\W)de pe(\W)/u", "\${1}dupe\${2}", $s);
    $s = preg_replace("/(\W)după(\W)/u", "\${1}dupe\${2}", $s);
    $s = preg_replace("/(\w)uoas(\w)/u", "\${1}oas\${2}", $s); // respectuoasă
    $s = preg_replace("/ua/", "oa", $s);
    $s = preg_replace("/(\W)fi(\W)/u", "\${1}fiii\${2}", $s); // one of the i's will be removed below
    $s = preg_replace("/(\w)ii/u", "\${1}i", $s);
    $s = preg_replace("/înn(\w)/u", "în\${1}", $s);
    $s = preg_replace("/(\W)sunt(em|eți)?/i", "\${1}sânt\${2}", $s);
    $s = preg_replace("/(\W)numai(\W)/i", "\${1}decât\${2}", $s);
    $s = preg_replace("/(\W)doar(\W)/i", "\${1}decât\${2}", $s);

    if (rand(1,20) <= 10) {
      $s = str_replace(array("ă", "â", "î", "ș", "ț"), array("a", "a", "i", "s", "t"), $s);
    }

     // Now remove hyphens, but keep the ones we added ourselves
    $s = preg_replace("/(\w)###(\w)/u", "\${1}\${2}", $s);
    return $s;
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
    return (stristr($s, '[url=') !== false) ||
      (stristr($s, 'http://') !== false);
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

  /* Place a css class around the accented letter */
  static function highlightAccent($s) {
    return preg_replace("/\'(a|e|i|o|u|ă|î|â)/i",
                        "<span class=\"accented\">\$1</span>",
                        $s);
  }

  static function randomCapitalLetters($length) {
    $result = '';
    for ($i = 0; $i < $length; $i++) {
      $result .= chr(rand(0, 25) + ord('A'));
    }
    return $result;
  }

  static function formatNumber($n, $decimals) {
    return number_format($n, $decimals, ',', '.');
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
