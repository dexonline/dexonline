<?php

class Str {

  // Convert old (î) orthography to new (â) orthography.
  // Assumes $s uses diacritics (if needed).
  static function convertOrthography($s) {
    $s = preg_replace("/\bsînt(?=\b|em\b|eți\b)/u", 'sunt', $s);
    $s = preg_replace("/\bSÎNT(?=\b|EM\b|EȚI\b)/u", 'SUNT', $s);

    // replace î with â unless it's at the beginning, the end or after a prefix
    $r = '\b';
    foreach (Constant::I_PREFIXES as $prefix) {
      $r .= "|\b" . $prefix;
    }
    $s = preg_replace("/(?<!{$r})î(?=.)/u", 'â', $s);

    // same, but for uppercase
    $r = '\b';
    foreach (Constant::I_PREFIXES as $prefix) {
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

  static function insert($string, $substring, $pos) {
    return substr_replace($string, $substring, $pos, 0);
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

  static function isAllUppercase($s) {
    return mb_strtoupper($s, 'utf-8') == $s; // maybe paranoid: mb_detect_encoding($s) as second argument
  }

  static function getUpperLowerString($s) {
    return mb_strtoupper($s) . mb_strtolower($s);
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
      ['"', 'ấ', 'Ấ', 'î́', 'Î́'],
      ['', 'â', 'Â', 'î', 'Î'],
      $query);

    // Allow a la carte, a la russe etc. even when spelled without the grave accent
    if (self::startsWith($query, 'a se ') && !Lexeme::get_by_formNoAccent($query)) {
      $query = substr($query, 5);
    } else if (self::startsWith($query, 'a ') && !Lexeme::get_by_formNoAccent($query)) {
      $query = substr($query, 2);
    }
    $query = trim($query);
    $query = strip_tags($query);
    $query = self::stripHtmlEscapeCodes($query);
    // Delete all kinds of illegal symbols, but use them as word delimiters.
    // Allow dots, dashes and spaces.
    $query = preg_replace("/[!@#$%&()_+=\\\\{}\":;<>,\/]/", ' ', $query);
    $query = preg_replace("/\s+/", ' ', $query);
    $query = self::convertOrthography($query);
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
      'ấ' => 'î́',
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

  static function shorten($s, $maxLength) {
    $l = mb_strlen($s);
    if ($l >= $maxLength + 3) {
      return mb_substr($s, 0, $maxLength - 3) . '...';
    }
    return $s;
  }

  /* Make a string portable across OS's by replacing '/' with DIRECTORY_SEPARATOR */
  static function portable($s) {
    return str_replace('/', DIRECTORY_SEPARATOR, $s);
  }

  /* Place a css class around the letter bearing the tonic accent */
  static function highlightAccent($s) {
    $s = preg_replace("/(?<!\\\\|\\')'(\p{L})/u",
                        "<span class=\"tonic-accent\">\$1</span>",
                        $s);
    return preg_replace("/(?<!\\\\)''(\p{L})/u",
                        "<span class=\"secondary-accent\">\$1</span>",
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

  // Generic purpose cleanup of a string. This should be true of all columns of all tables.
  static function cleanup($s, $apostrophes = true) {
    $s = trim($s);

    $from = array_keys(Constant::CLEANUP_PATTERNS);
    $to = array_values(Constant::CLEANUP_PATTERNS);
    $s = preg_replace($from, $to, $s);

    if ($apostrophes) {
      $from = array_keys(Constant::APOSTROPHE_CLEANUP_PATTERNS);
      $to = array_values(Constant::APOSTROPHE_CLEANUP_PATTERNS);
      $s = preg_replace($from, $to, $s);
    }

    // Replace \abcd with the Unicode character 0xABCD
    $s = preg_replace_callback(
      '/\\\\([\dabcdef]{4,5})/i',
      function ($matches) {
      return self::chr(hexdec($matches[0]));
      },
      $s);

    return $s;
  }

  // Sanitizes a definition or meaning. This is more elaborate than cleanup().
  // Returns an array of
  // - the sanitized string
  // - ambiguous abbreviations encountered
  static function sanitize($s, $sourceId = null, &$warnings = null) {
    $warnings = $warnings ?? [];

    $s = self::cleanup($s);
    $s = str_replace(['$$', '@@', '%%'], '', $s);
    $s = self::migrateFormatChars($s);

    if ($sourceId) {
      list($s, $ambiguousAbbrevs) = Abbrev::markAbbreviations($s, $sourceId);
    } else {
      $ambiguousAbbrevs = [];
    }

    self::reportSanitizationErrors($s, $warnings);

    $s = self::attributeFootnotes($s);

    return [$s, $ambiguousAbbrevs];
  }

  // Checks that various pairs of characters are nested properly in $s.
  // Some pairs contain the same character for opening and closing blocks (e.g. '@').
  // We cannot check the nesting of () due the use of ) in "a), b), c)".
  static function reportSanitizationErrors($s, &$errors) {
    $chars = self::unicodeExplode($s);
    self::sanitizationStackTest($chars, $errors, '@$%#', ['{}']);
    self::sanitizationStackTest($chars, $errors, '', ['[]']);
    self::sanitizationStackTest($chars, $errors, '"', ['«»']);
  }

  private static function sanitizationStackTest($chars, &$errors, $same, $pairs) {
    $match = [];
    foreach ($pairs as $p) {
      $match[$p[1]] = $p[0]; // e.g. '}' => '{'
    }

    $len = count($chars);
    $i = 0;
    $stack = [];
    $anyErrors = false;

    while (!$anyErrors && ($i < $len)) {
      $c = $chars[$i];
      if ($c == '\\') {                         // skip the next character
        $i++;
      } else if (strpos($same, $c) !== false) { // e.g. '@' or '$'
        if ($c == end($stack)) {
          array_pop($stack);
        } else {
          $stack[] = $c;
        }
      } else if (in_array($c, $match)) {        // e.g. '{' or '['
        $stack[] = $c;
      } else if (isset($match[$c])) {           // e.g. '}' or ']'
        if (end($stack) == $match[$c]) {
          array_pop($stack);
        } else {
          $anyErrors = true;
        }
      }
      $i++;
    }
    if ($anyErrors || !empty($stack)) {
      $distinct = $same . implode($pairs);
      $errors[] = "Unele dintre caracterele {$distinct} nu sunt împerecheate corect.";
    }
  }

  // Move closing formatting chars left past whitespace. Move opening formatting chars right
  // past whitespace. This simplifies the parser.
  static function migrateFormatChars($s) {

    $class = self::characterClasses($s);

    // reorder characters by class: closed formats, whitespace, open formats
    preg_match_all("/(?<!\\\\)[ \n@$%]+/m", $s, $matches, PREG_OFFSET_CAPTURE);
    foreach ($matches[0] as $match) {
      $len = strlen($match[0]);
      $offset = $match[1];

      $buf = '';
      for ($c = 1; $c <= 3; $c++) {
        for ($i = 0; $i < $len; $i++) {
          if ($class[$i + $offset] == $c) {
            $buf .= $s[$i + $offset];
          }
        }
      }
      $s = substr_replace($s, $buf, $offset, $len);
    }

    // collapse consecutive spaces and trim the string
    $s = trim(preg_replace('/  +/', ' ', $s));

    return $s;
  }

  // Examines nested substructures ({{footnotes}} and ▶hidden notes◀).
  // Returns an array containing the nesting level of every character in $s.
  private static function nestingLevels($s) {
    $len = strlen($s);

    // count nesting levels
    $nestRegexp = [
      '/(?<!\\\\)\{{2}.*(?<![+])\}{2}/sU',
      '/▶.*◀/sU',
    ];
    $level = $len ? array_fill(0, $len, 0) : [];
    foreach ($nestRegexp as $regexp) {
      preg_match_all($regexp, $s, $matches, PREG_OFFSET_CAPTURE);
      foreach ($matches[0] as $match) {
        $l = strlen($match[0]);
        $offset = $match[1];
        for ($i = 0; $i < $l; $i++) {
          $level[$i + $offset]++;
        }
      }
    }

    return $level;
  }

  // Returns an array of numbers for whitespace and formatting characters:
  // 1 = closing char, 2 = whitespace, 3 = opening char, 0 = other
  // Takes nested substructures into account.
  private static function characterClasses($s) {
    $level = self::nestingLevels($s);

    $len = strlen($s);
    $i = 0;
    $prevLevel = -1;
    $stack[] = 0; // stack of open/closed state for @, $ and % at the current nesting level

    $class = $len ? array_fill(0, $len, 0) : [];

    while ($i < $len) {
      if  ($level[$i] > $prevLevel) {
        // start a new substructure
        $stack[$level[$i]] = ['$' => false, '@' => false, '%' => false];
      }
      $c = $s[$i];
      switch ($c) {
        case '\\':
          $i++;
          break;

        case '@': case '$': case '%':
          $stack[$level[$i]][$c] = !$stack[$level[$i]][$c];
          $class[$i] = $stack[$level[$i]][$c] ? 3 : 1;
          break;

        case ' ': case "\n":
          $class[$i] = 2;
          break;
      }
      $prevLevel = $level[$i];
      $i++;
    }

    return $class;
  }

  // append /userId to {{footnotes}} that don't have one
  static function attributeFootnotes($s) {
    preg_match_all('/\{\{(.*)\}\}/U', $s, $matches, PREG_SET_ORDER | PREG_OFFSET_CAPTURE);
    foreach (array_reverse($matches) as $m) {
      if (!preg_match('/\\/\d+$/', $m[1][0])) { // does not end in /number
        $userId = User::getActiveId();
        $at = $m[1][1] + strlen($m[1][0]);
        $s = substr_replace($s, "/{$userId}", $at, 0);
      }
    }

    return $s;
  }

  /**
   * Converts $s to html.
   * @return Array an array of
   * - HTML result
   * - extracted footnotes
   */
  static function htmlize($s, $sourceId = null, &$errors = null, &$warnings = null) {
    $errors = $errors ?? [];
    $warnings = $warnings ?? [];

    $s = htmlspecialchars($s, ENT_NOQUOTES);

    self::findRedundantLinks($s, $warnings);

    // some things, e.g. footnotes, need to return extra information beside modifying $s
    $payloads = [];

    // various internal notations
    // preg_replace supports multiple patterns and replacements, but they may not overlap
    foreach (Constant::HTML_PATTERNS as $internal => $replacement) {
      if (is_string($replacement)) {
        $s = preg_replace($internal, $replacement, $s);
      } else if (is_array($replacement)) {
        $className = $replacement[0];
        $helper = new $className($sourceId, $errors, $warnings);

        $s = preg_replace_callback($internal, [$helper, 'htmlize'], $s);
        $s = $helper->postprocess($s);

        $key = $helper->getKey();
        if ($key) {
          $payloads[$key] = $helper->getPayload();
        }
      } else {
        die('Unknown value type in HTML_PATTERNS.');
      }
    }

    // t'onic 'accent
    $s = self::highlightAccent($s);

    // various substitutions
    $from = array_keys(Constant::HTML_REPLACEMENTS);
    $to = array_values(Constant::HTML_REPLACEMENTS);
    $s = str_replace($from, $to, $s);

    // finally, remove the escape character -- we no longer need it
    $s = preg_replace('/(?<!\\\\)\\\\/', '', $s);

    return [$s, $payloads['footnotes']];
  }

  // Prepare the string for printing inside an XML document.
  static function xmlize($s) {
    // Escape <, > and &
    $s = htmlspecialchars($s, ENT_NOQUOTES);

    // Replace backslashed characters with their XML escape code
    $s = preg_replace_callback(
      '/\\\\(.)/u',
      function ($matches) {
      return '&#x5c;' . '&#x' . dechex(self::ord($matches[1])) . ';';
      },
      $s);

    return $s;
  }

  // assumes that the string is trimmed
  static function capitalize($s) {
    if (!$s) {
      return $s;
    }
    return mb_strtoupper(self::getCharAt($s, 0)) . mb_substr($s, 1);
  }

  // capitalizes a string that may begin with an apostrophe
  static function capitalizeWithAccent($s) {
    if (Str::startsWith($s, "'")) {
      return "'" . Str::capitalize(substr($s, 1));
    } else {
      return Str::capitalize($s);
    }
  }

  static function chr($u) {
    return mb_convert_encoding(pack('N', $u), 'UTF-8', 'UCS-4BE');
  }

  static function ord($s) {
    $arr = unpack('N', mb_convert_encoding($s, 'UCS-4BE', 'UTF-8'));
    return $arr[1];
  }

  static function padRight($str, $length) {
    $len = strlen($str);
    $mbLen = mb_strlen($str);
    return str_pad($str, $length + ($len - $mbLen));
  }

  // Splits a multibyte string into characters. If we just extract each
  // character with mb_substr, the complexity is O(N^2). This code runs about
  // twice as fast as the one-liner:
  //
  //   return preg_split('//u', $s, null, PREG_SPLIT_NO_EMPTY);
  static function unicodeExplode($s) {
    $result = [];
    $len = strlen($s);
    $i = 0;

    while ($i < $len) {
      $c = ord($s[$i]);
      if ($c >> 7 == 0) {
        // 0vvvvvvv
        $result[] = $s[$i];
        $i++;
      } else if ($c >> 5 == 6) {
        // 110vvvvv 10vvvvvv
        $result[] = $s[$i] . $s[$i + 1];
        $i += 2;
      } else if ($c >> 4 == 14) {
        // 1110vvvv 10vvvvvv 10vvvvvv
        $result[] = $s[$i] . $s[$i + 1] . $s[$i + 2];
        $i += 3;
      } else if ($c >> 3 == 30) {
        // 11110vvv 10vvvvvv 10vvvvvv 10vvvvvv
        $result[] = $s[$i] . $s[$i + 1] . $s[$i + 2] . $s[$i + 3];
        $i += 4;
      } else {
        // dunno, skip it
        $i++;
      }
    }

    return $result;
  }

  // same as above, but takes one Unicode character and returns its code
  static function unicodeOrd($c) {
    $o = ord($c[0]);
    if ($o >> 7 == 0) {
      // 0vvvvvvv
      return $o;
    } else if ($o >> 5 == 6) {
      // 110vvvvv 10vvvvvv
      return (($o & 0x1f) << 6) | (ord($c[1]) & 0x3f);
    } else if ($o >> 4 == 14) {
      // 1110vvvv 10vvvvvv 10vvvvvv
      return (($o & 0xf) << 12) | ((ord($c[1]) & 0x3f) << 6) | (ord($c[2]) & 0x3f);
    } else if ($c >> 3 == 30) {
      // 11110vvv 10vvvvvv 10vvvvvv 10vvvvvv
      return (($o & 0x7) << 18) | ((ord($c[1]) & 0x3f) << 12) |
        ((ord($c[2]) & 0x3f) << 6) | (ord($c[3]) & 0x3f);
    } else {
      // dunno, skip it
      return null;
    }
  }

  static function removeAccents($s) {
    // remove graphic accents
    $s = str_replace(Constant::ACCENTS['accented'], Constant::ACCENTS['unaccented'], $s);

    // remove tonic accents
    $s = preg_replace("/(?<!\\\\)'/", '', $s);

    return $s;
  }
    /**
   * Replaces graphic accents (Á) with marked ones ('A)
   * Should be used carefully only with strings suitable for tonic accents
   */
  static function changeAccents($s) {
    $s = str_replace(Constant::ACCENTS['accented'], Constant::ACCENTS['marked'], $s);

    return $s;
  }

  // Returns the numeric value of a Roman numeral or null on errors
  static function romanToArabic($roman) {
    $roman = strtolower($roman);
    $len = strlen($roman);
    $oldValue = 100000;
    $result = 0;
    $values = ['i' => 1, 'v' => 5, 'x' => 10, 'l' => 50, 'c' => 100, 'd' => 500, 'm' => 1000,];

    for ($i = 0; $i < $len; $i++) {
      $c = substr($roman, $i, 1);
      if (!array_key_exists($c, $values)) {
        return null;
      }
      $value = $values[$c];
      $result += $value;
      if ($value > $oldValue) {
        $result -= 2 * $oldValue;
      }
      $oldValue = $value;
    }
    return $result;
  }

  // Converts an arabic number between 1 and 999 to its Roman notation
  static function arabicToRoman($arabic) {
    $bits = [
      ['', 'I', 'II', 'III', 'IV', 'V', 'VI', 'VII', 'VIII', 'IX'],
      ['', 'X', 'XX', 'XXX', 'XL', 'L', 'LX', 'LXX', 'LXXX', 'XC'],
      ['', 'C', 'CC', 'CCC', 'CD', 'D', 'DC', 'DCC', 'DCCC', 'CM'],
    ];
    return $bits[2][$arabic % 1000 / 100] . $bits[1][$arabic % 100 / 10] . $bits[0][$arabic % 10];
  }

  // Returns an array of redundant links found in the internalRep of a
  // definition. Called when editing a definition, and also used by patch 00238.
  // Each entry is an array of the form
  // (original_word, linked_lexeme, reason, short_reason).
  //
  // For more information, check out issue #632 and pull requeset #637.
  static function findRedundantLinks($internalRep, &$errors) {

    // Find all instances of |original_word|linked_lexeme|.
    preg_match_all("/\|([^\|]+)\|([^\|]+)\|/", $internalRep, $links, PREG_SET_ORDER);

    $processedLinks = [];

    foreach ($links as $l) {

      $linkAdded = false;

      // Remove formatting from around the words.
      // For example, @label@ becomes label, but im@]prieteni stays the same.
      $words = self::convertOrthography(trim($l[1], "$#@^_0123456789"));
      $definition_string = self::convertOrthography(trim($l[2], "$#@^_0123456789"));

      foreach (explode(" ", $words) as $word_string) {

        $word_lexeme_ids = Model::factory('InflectedForm')
          ->select('lexemeId')
          ->where('formNoAccent', $word_string)
          ->find_many();

        // Separate queries for formNoAccent and formUtf8General
        // since Idiorm does not support OR'ing WHERE clauses.
        $field = self::hasDiacritics($definition_string) ? 'formNoAccent' : 'formUtf8General';
        $def_lexeme_id_by_noAccent = Model::factory('Lexeme')
          ->select('id')
          ->where($field, $definition_string)
          ->find_one();

        $def_lexeme_id_by_utf8General = Model::factory('Lexeme')
          ->select('id')
          ->where('formUtf8General', $definition_string)
          ->find_one();

        // Linked lexeme was not found in the database.
        if (empty($def_lexeme_id_by_utf8General)) {
          $currentLink = [
            "original_word" => $l[1],
            "linked_lexeme" => $l[2],
            "reason" => "Trimiterea nu a fost găsită în baza de date.",
            "short_reason" => "no_link",
          ];
          $processedLinks[] = $currentLink;

          $linkAdded = true;
          break;
        }

        // Linking to base form.
        $found = false;
        foreach ($word_lexeme_ids as $word_lexeme_id) {
          if ($word_lexeme_id->lexemeId === $def_lexeme_id_by_noAccent->id) {
            $found = true;
          }
        }

        if ($found === true) {
          $currentLink = [
            "original_word" => $l[1],
            "linked_lexeme" => $l[2],
            "reason" => "Trimitere către forma de bază a cuvântului.",
            "short_reason" => "forma_baza",
          ];
          $processedLinks[] = $currentLink;

          $linkAdded = true;
          break;
        }

        // Infinitiv lung / adjectiv / participiu.
        $found = false;

        foreach ($word_lexeme_ids as $word_lexeme_id) {
          $lexeme_model = Model::factory('Lexeme')
            ->select('formNoAccent')
            ->select('modelType')
            ->select('modelNumber')
            ->where_id_is($word_lexeme_id->lexemeId)
            ->find_one();

          if ($lexeme_model->modelType === "IL" ||
            $lexeme_model->modelType === "PT" ||
            $lexeme_model->modelType === "A" ||
            ($lexeme_model->modelType === "F" &&
            ($lexeme_model->modelNumber === "107" ||
            $lexeme_model->modelNumber === "113"))) {
            $nextstep = Model::factory('InflectedForm')
              ->select('lexemeId')
              ->where('formNoAccent', $lexeme_model->formNoAccent)
              ->find_many();

            foreach ($nextstep as $one) {
              if ($one->lexemeId === $def_lexeme_id_by_noAccent->id) {
                $found = true;
                break;
              }
            }
          }
        }

        if ($found === true) {
          $currentLink = [
            "original_word" => $l[1],
            "linked_lexeme" => $l[2],
            "reason" => "Cuvântul este infinitiv lung.",
            "short_reason" => "inf_lung",
          ];
          $processedLinks[] = $currentLink;

          $linkAdded = true;
          break;
        }
      }

      if ($linkAdded === false) {
        $currentLink = [
          "original_word" => $l[1],
          "linked_lexeme" => $l[2],
          "reason" => "Trimiterea nu are nevoie de modificări.",
          "short_reason" => "nemodificat",
        ];
        $processedLinks[] = $currentLink;
      }
    }

    foreach ($processedLinks as $pl) {
      if ($pl['short_reason'] !== 'nemodificat') {
        $errors[] = sprintf('Legătura de la "%s" la "%s" este considerată redundantă (motiv: %s)',
                            $pl['original_word'], $pl['linked_lexeme'], $pl['reason']);
      }
    }

    return $processedLinks;
  }

  static function getEnglishMonthName($date) {
    $ts = strtotime($date);
    return date('F', $ts);
  }
}
