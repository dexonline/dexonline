<?php

class Str {
  // Convert old (î) orthography to new (â) orthography.
  // Assumes $s uses diacritics (if needed).
  static function convertOrthography($s) {
    $s = preg_replace("/\bsînt(?=\b|em\b|\eți)/u", 'sunt', $s);
    $s = preg_replace("/\bSÎNT(?=\b|EM\b|\EȚI)/u", 'SUNT', $s);

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
    return preg_replace("/(?<!\\\\)'(.)/u",
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

  // Generic purpose cleanup of a string. This should be true of all columns of all tables.
  static function cleanup($s) {
    $s = trim($s);

    $from = array_keys(Constant::CLEANUP_PATTERNS);
    $to = array_values(Constant::CLEANUP_PATTERNS);
    $s = preg_replace($from, $to, $s);

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
  static function sanitize($s, $sourceId = null, &$errors = null, &$ambiguousMatches = null) {
    $s = self::cleanup($s);
    $s = str_replace([ '$$', '@@', '%%' ], '', $s);

    $s = self::migrateFormatChars($s);
    if ($sourceId) {
      $s = Abbrev::markAbbreviations($s, $sourceId, $ambiguousMatches);
    }

    if (is_array($errors)) {
      self::reportSanitizationErrors($s, $errors);
    }

    return $s;
  }

  // Checks that varios pairs of characters are nested properly in $s.
  // Some pairs contain the same character for opening and closing blocks (e.g. '@').
  // We cannot check the nesting of () due the use of ) in "a), b), c)".
  static function reportSanitizationErrors($s, &$errors) {
    $chars = self::unicodeExplode($s);
    self::sanitizationStackTest($chars, $errors, '@$%#', [ '{}' ]);
    self::sanitizationStackTest($chars, $errors, '', [ '[]' ]);
    self::sanitizationStackTest($chars, $errors, '"', [ '«»' ]);
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

  static function migrateFormatChars($s) {
    // First, check that all format chars come in pairs
    $len = strlen($s);
    $i = 0;
    $state = [ '$' => false, '@' => false, '%' => false ];

    // 0 = punctuation (.,;:), 1 = closing char, 2 = whitespace, 3 = opening char, 4 = other
    $value = $len ? array_fill(0, $len, 4) : [];

    while ($i < $len) {
      $c = $s[$i];
      if ($c == '\\') {
        $i++;
      } else if (array_key_exists($c, $state)) {
        $state[$c] = !$state[$c];
        $value[$i] = $state[$c] ? 3 : 1;
      } else if ($c && strpos('.,;:', $c) !== false) {
        $value[$i] = 0;
      } else if ($c == ' ') {
        $value[$i] = 2;
      }
      $i++;
    }
    foreach ($state as $char => $bool) {
      if ($bool) {
        $s .= $char;
        $value[] = 1;
      }
    }

    // Now put all format chars in the right positions.
    // - opening chars need to more right past whitespace and punctuation (.,;:)
    // - closing chars need to move left past whitespace
    // Therefore, take every string consisting of (w)hitespace, (p)unctuation, (o)pening chars
    // and (c)losing chars and rearrange it as p,c,w,o
    $matches = [];
    preg_match_all('/[ .,;:@$%]+/', $s, $matches, PREG_OFFSET_CAPTURE);
    if (count($matches)) {
      foreach ($matches[0] as $match) {
        $chars = str_split($match[0]);
        $offset = $match[1];
        if ($offset && $s[$offset - 1] == '\\') {
          $chars = array_slice($chars, 1);
          $offset++;
        }
        $len = count($chars);
        $sopen = array_slice($value, $offset, $len);

        $changes = true;
        for ($i = 0; $i < $len - 1 && $changes; $i++) { // We need a stable algorithm, so bubblesort...
          $changes = false;
          for ($j = 0; $j < $len - 1; $j++) {
            if ($sopen[$j] > $sopen[$j + 1]) {
              $t = $chars[$j]; $chars[$j] = $chars[$j + 1]; $chars[$j + 1] = $t;
              $t = $sopen[$j]; $sopen[$j] = $sopen[$j + 1]; $sopen[$j + 1] = $t;
              $changes = true;
            }
          }
        }
        $s = substr($s, 0, $offset) . implode('', $chars) . substr($s, $offset + count($chars));
      }
      // Collapse consecutive spaces and trim the string
      $s = trim(preg_replace('/  +/', ' ', $s));
    }
    return $s;
  }

  // Converts $s to html. If $obeyNewlines is true, replaces \n with
  // <br>\n; otherwise leaves \n as \n. Collects unrecoverable errors in $errors.
  static function htmlize($s, $sourceId, &$errors = null, $obeyNewlines = false) {
    $s = htmlspecialchars($s, ENT_NOQUOTES);

    // various internal notations
    // preg_replace supports multiple patterns and replacements, but they may not overlap
    foreach (Constant::HTML_PATTERNS as $internal => $html) {
      $s = preg_replace($internal, $html, $s);
    }

    // __emphasized__ text
    $count = 0;
    $s = preg_replace('/__(.*?)__/', '<span class="emph">$1</span>', $s, -1, $count);
    if ($count) {
      $s = "<span class=\"deemph\">$s</span>";
    }

    // t'onic 'accent
    $s = self::highlightAccent($s);

    if ($obeyNewlines) {
      $s = str_replace("\n", "<br>\n", $s);
    }

    // various substitutions
    $from = array_keys(Constant::HTML_REPLACEMENTS);
    $to = array_values(Constant::HTML_REPLACEMENTS);
    $s = str_replace($from, $to, $s);

    $s = Abbrev::htmlizeAbbreviations($s, $sourceId, $errors);

    // finally, remove the escape character -- we no longer need it
    $s = preg_replace('/(?<!\\\\)\\\\/', '', $s);

    return $s;
  }

  // Prepare the string for printing inside an XML document.
  static function xmlize($s) {
    // Escape <, > and &
    $s = htmlspecialchars($s, ENT_NOQUOTES);

    // Replace backslashed characters with their XML escape code
    $s = preg_replace_callback(
      '/\\\\(.)/',
      function ($matches) {
        return '&#x5c;' . '&#x' . dechex(self::ord($matches[1])) . ';';
      },
      $s);

    return $s;
  }

  // Assumes that the string is trimmed
  static function capitalize($s) {
    if (!$s) {
      return $s;
    }
    return mb_strtoupper(self::getCharAt($s, 0)) . mb_substr($s, 1);
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

  // If you just extract each character with mb_substr, the complexity is O(N^2).
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
        $result[] = $s[$i] . $s[$i + 1] . $s[$i + 2] + $s[$i + 3];
        $i += 4;
      } else {
        // dunno, skip it
        $i++;
      }
    }

    return $result;
  }

  static function removeAccents($s) {
    // remove graphic accents
    $s = str_replace(Constant::ACCENTS['accented'], Constant::ACCENTS['unaccented'], $s);

    // remove tonic accents
    $s = preg_replace("/(?<!\\\\)'/", '', $s);

    return $s;
  }

  // Returns the numeric value of a Roman numeral or null on errors
  static function romanToArabic($roman) {
    $roman = strtolower($roman);
    $len = strlen($roman);
    $oldValue = 100000;
    $result = 0;
    $values = [ 'i' => 1, 'v' => 5, 'x' => 10, 'l' => 50, 'c' => 100, 'd' => 500, 'm' => 1000, ];

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
  // (original_word, linked_lexem, reason, short_reason).
  //
  // For more information, check out issue #632 and pull requeset #637.
  static function findRedundantLinks($internalRep) {

    // Find all instances of |original_word|linked_lexem|.
    preg_match_all("/\|([^\|]+)\|([^\|]+)\|/", $internalRep, $links, PREG_SET_ORDER);

    $processedLinks = [];

    foreach ($links as $l) {

      $linkAdded = false;

      // Remove formatting from around the words.
      // For example, @label@ becomes label, but im@]prieteni stays the same.
      $words = self::convertOrthography(trim($l[1], "$#@^_0123456789"));
      $definition_string = self::convertOrthography(trim($l[2], "$#@^_0123456789"));

      foreach (explode(" ", $words) as $word_string) {

        $word_lexem_ids = Model::factory('InflectedForm')
                        ->select('lexemeId')
                        ->where('formNoAccent', $word_string)
                        ->find_many();

        // Separate queries for formNoAccent and formUtf8General
        // since Idiorm does not support OR'ing WHERE clauses.
        $field = self::hasDiacritics($definition_string) ? 'formNoAccent' : 'formUtf8General';
        $def_lexem_id_by_noAccent = Model::factory('Lexeme')
                                  ->select('id')
                                  ->where($field, $definition_string)
                                  ->find_one();

        $def_lexem_id_by_utf8General = Model::factory('Lexeme')
                                     ->select('id')
                                     ->where('formUtf8General', $definition_string)
                                     ->find_one();

        // Linked lexeme was not found in the database.
        if (empty($def_lexem_id_by_utf8General)) {
          $currentLink = [
            "original_word" => $l[1],
            "linked_lexem" => $l[2],
            "reason" => "Trimiterea nu a fost găsită în baza de date.",
            "short_reason" => "no_link",
          ];
          $processedLinks[] = $currentLink;

          $linkAdded = true;
          break;
        }

        // Linking to base form.
        $found = false;
        foreach ($word_lexem_ids as $word_lexem_id) {
          if ($word_lexem_id->lexemeId === $def_lexem_id_by_noAccent->id) {
            $found = true;
          }
        }

        if ($found === true) {
          $currentLink = [
            "original_word" => $l[1],
            "linked_lexem" => $l[2],
            "reason" => "Trimitere către forma de bază a cuvântului.",
            "short_reason" => "forma_baza",
          ];
          $processedLinks[] = $currentLink;

          $linkAdded = true;
          break;
        }

        // Infinitiv lung / adjectiv / participiu.
        $found = false;

        foreach ($word_lexem_ids as $word_lexem_id) {
          $lexem_model = Model::factory('Lexeme')
                       ->select('formNoAccent')
                       ->select('modelType')
                       ->select('modelNumber')
                       ->where_id_is($word_lexem_id->lexemeId)
                       ->find_one();

          if ($lexem_model->modelType === "IL" ||
              $lexem_model->modelType === "PT" ||
              $lexem_model->modelType === "A" ||
              ($lexem_model->modelType === "F" &&
               ($lexem_model->modelNumber === "107" ||
                $lexem_model->modelNumber === "113"))) {
            $nextstep = Model::factory('InflectedForm')
                      ->select('lexemeId')
                      ->where('formNoAccent', $lexem_model->formNoAccent)
                      ->find_many();

            foreach ($nextstep as $one) {
              if ($one->lexemeId === $def_lexem_id_by_noAccent->id) {
                $found = true;
                break;
              }
            }
          }
        }

        if ($found === true) {
          $currentLink = [
            "original_word" => $l[1],
            "linked_lexem" => $l[2],
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
          "linked_lexem" => $l[2],
          "reason" => "Trimiterea nu are nevoie de modificări.",
          "short_reason" => "nemodificat",
        ];
        $processedLinks[] = $currentLink;
      }
    }

    return $processedLinks;
  }
}
