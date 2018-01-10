<?php

class AdminStringUtil {

  private static $ABBREV_INDEX = null; // These will be loaded lazily
  private static $ABBREVS = [];

  // Generic purpose cleanup of a string. This should be true of all columns of all tables.
  static function cleanup($s) {
    $s = trim($s);

    $from = array_keys(Constant::CLEANUP_REPLACEMENTS);
    $to = array_values(Constant::CLEANUP_REPLACEMENTS);
    $s = str_replace($from, $to, $s);

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
  static function sanitize($s, $sourceId = null, &$ambiguousMatches = null) {
    $s = self::cleanup($s);
    $s = str_replace([ '$$', '@@', '%%' ], '', $s);

    $s = self::migrateFormatChars($s);
    if ($sourceId) {
      $s = self::markAbbreviations($s, $sourceId, $ambiguousMatches);
    }

    return $s;
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
    $s = StringUtil::highlightAccent($s);

    if ($obeyNewlines) {
      $s = str_replace("\n", "<br>\n", $s);
    }

    // various substitutions
    $from = array_keys(Constant::HTML_REPLACEMENTS);
    $to = array_values(Constant::HTML_REPLACEMENTS);
    $s = str_replace($from, $to, $s);

    $s = self::htmlizeAbbreviations($s, $sourceId, $errors);

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
    return mb_strtoupper(StringUtil::getCharAt($s, 0)) . mb_substr($s, 1);
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
    return str_replace(Constant::ACCENTS['accented'], Constant::ACCENTS['unaccented'], $s);
  }

  /**
   * Creates a map of $sourceId => array of sections to use.
   * Each section resides in a file named docs/abbrev/<section>.conf (these are loaded lazily).
   */
  static function loadAbbreviationsIndex() {
    if (!self::$ABBREV_INDEX) {
      self::$ABBREV_INDEX = [];
      $raw = parse_ini_file(Core::getRootPath() . "docs/abbrev/abbrev.conf", true);
      foreach ($raw['sources'] as $sourceId => $sectionList) {
        self::$ABBREV_INDEX[$sourceId] = preg_split('/, */', $sectionList);
      }
    }
    return self::$ABBREV_INDEX;
  }

  static function getAbbrevSectionNames() {
    self::loadAbbreviationsIndex();
    $sections = [];
    foreach (self::$ABBREV_INDEX as $sectionList) {
      foreach ($sectionList as $s) {
        $sections[$s] = true;
      }
    }
    return array_keys($sections);
  }

  /**
   * Creates and caches a map($from, pair($to, $ambiguous)) for this sourceId.
   * That is, for each sourceId and abbreviated text, we store the expanded text and whether the abbreviation is ambiguous.
   * An ambiguous abbreviation such as "top" or "gen" also has a meaning as an inflected form.
   * Ambiguous abbreviations should be expanded carefully, or with human approval.
   */
  private static function loadAbbreviations($sourceId) {
    if (!array_key_exists($sourceId, self::$ABBREVS)) {
      self::loadAbbreviationsIndex();
      $result = [];

      if (array_key_exists($sourceId, self::$ABBREV_INDEX)) {
        $list = [];
        foreach (self::$ABBREV_INDEX[$sourceId] as $section) {
          $raw = parse_ini_file(Core::getRootPath() . "docs/abbrev/{$section}.conf", true);
          // If an abbreviation is defined in several sections, use the one that's defined later
          $list = array_merge($list, $raw[$section]);
        }

        foreach ($list as $from => $to) {
          $ambiguous = ($from[0] == '*');
          if ($ambiguous) {
            $from = substr($from, 1);
          }
          $numWords = 1 + substr_count($from, ' ');
          $regexp = str_replace(['.', ' '], ["\\.", ' *'], $from);
          $pattern = "\W({$regexp})(\W|$)";
          $hasCaps = ($from !== mb_strtolower($from));
          $result[$from] = [
            'to' => $to,
            'ambiguous' => $ambiguous,
            'regexp' => $pattern,
            'numWords' => $numWords,
            'hasCaps' => $hasCaps,
          ];
        }

        // Sort the list by number of words, then by ambiguous
        uasort($result, 'self::abbrevCmp');
      }
      self::$ABBREVS[$sourceId] = $result;
    }
    return self::$ABBREVS[$sourceId];
  }

  private static function abbrevCmp($a, $b) {
    if ($a['numWords'] < $b['numWords']) {
      return 1;
    } else if ($a['numWords'] > $b['numWords']) {
      return -1;
    } else {
      return $a['ambiguous'] - $b['ambiguous'];
    }
  }

  static function markAbbreviations($s, $sourceId, &$ambiguousMatches = null) {
    $abbrevs = self::loadAbbreviations($sourceId);
    $hashMap = self::constructHashMap($s);
    // Do not report two ambiguities at the same position, for example M. and m.
    $positionsUsed = [];
    foreach ($abbrevs as $from => $tuple) {
      $matches = [];
      // Perform a case-sensitive match if the pattern contains any uppercase, case-insensitive otherwise
      $modifier = $tuple['hasCaps'] ? "" : "i";
      preg_match_all("/{$tuple['regexp']}/u$modifier", $s, $matches, PREG_OFFSET_CAPTURE); // We always add the /u modifier for Unicode
      if (count($matches[1])) {
        foreach (array_reverse($matches[1]) as $match) {
          $orig = $match[0];
          $position = $match[1];

          if (!$hashMap[$position]) { // Don't replace anything if we are already between hash signs
            if ($tuple['ambiguous']) {
              if ($ambiguousMatches !== null && !array_key_exists($position, $positionsUsed)) {
                $ambiguousMatches[] = [
                  'abbrev' => $from,
                  'position' => $position,
                  'length' => strlen($orig),
                ];
                $positionsUsed[$position] = true;
              }
            } else {
              $replacement = StringUtil::isUppercase(StringUtil::getCharAt($orig, 0)) ? self::capitalize($from) : $from;
              $s = substr_replace($s, "#$replacement#", $position, strlen($orig));
              array_splice($hashMap, $position, strlen($orig), array_fill(0, 2 + strlen($replacement), true));
            }
          }
        }
      }
    }
    return $s;
  }

  /** Returns a parallel array of booleans. Each element is true if $s[$i] lies inside a pair of hash signs, false otherwise **/
  private static function constructHashMap($s) {
    $inHash = false;
    $result = [];
    for ($i = 0; $i < strlen($s); $i++) {
      $c = $s[$i];
      if ($c == '#') {
        $result[] = true;
        $inHash = !$inHash;
      } else {
        $result[] = $inHash;
      }
    }
    return $result;
  }

  // Similar to array_key_exists, but better handling of capitalization.
  // E.g. the array keys can include both "Ed." (Editura) and "ed." (ediție), or
  // we may look for a specific capitalization (BWV, but not bwv; AM, but not am)
  private static function bestAbbrevMatch($s, $abbrevList) {
    $lowS = mb_strtolower($s);
    $bestMatch = null;
    foreach ($abbrevList as $from => $tuple) {
      if ($tuple['hasCaps'] && ($from == $s)) {
        return $from;
      } else if (!$tuple['hasCaps'] && ($from == $lowS)) {
        $bestMatch = $from;
      }
    }
    return $bestMatch;
  }

  private static function htmlizeAbbreviations($s, $sourceId, &$errors = null) {
    $abbrevs = self::loadAbbreviations($sourceId);
    $matches = [];
    preg_match_all("/#([^#]*)#/", $s, $matches, PREG_OFFSET_CAPTURE);
    if (count($matches[1])) {
      foreach (array_reverse($matches[1]) as $match) {
        $from = $match[0];
        $matchingKey = self::bestAbbrevMatch($from, $abbrevs);
        $position = $match[1];
        if ($matchingKey) {
          $hint =  $abbrevs[$matchingKey]['to'];
        } else {
          $hint =  'abreviere necunoscută';
          if ($errors !== null) {
            $errors[] = "Abreviere necunoscută: «{$from}». Verificați că după fiecare punct există un spațiu.";
          }
        }
        $s = substr_replace($s, "<abbr class=\"abbrev\" title=\"$hint\">$from</abbr>", $position - 1, 2 + strlen($from));
      }
    }
    return $s;
  }

  static function expandAbbreviations($s, $sourceId) {
    $abbrevs = self::loadAbbreviations($sourceId);
    $matches = [];
    preg_match_all("/#([^#]*)#/", $s, $matches, PREG_OFFSET_CAPTURE);
    if (count($matches[1])) {
      foreach (array_reverse($matches[1]) as $match) {
        $from = $match[0];
        $matchingKey = self::bestAbbrevMatch($from, $abbrevs);
        $position = $match[1];
        if ($matchingKey) {
          $to =  $abbrevs[$matchingKey]['to'];
          $s = substr_replace($s, $to, $position - 1, 2 + strlen($from));
        }
      }
    }
    return $s;
  }

  static function getAbbreviation($sourceId, $short) {
    $abbrevs = self::loadAbbreviations($sourceId);
    return array_key_exists($short, $abbrevs) ? $abbrevs[$short]['to'] : null;
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
      $words = StringUtil::convertOrthography(trim($l[1], "$#@^_0123456789"));
      $definition_string = StringUtil::convertOrthography(trim($l[2], "$#@^_0123456789"));

      foreach (explode(" ", $words) as $word_string) {

        $word_lexem_ids = Model::factory('InflectedForm')
                        ->select('lexemId')
                        ->where('formNoAccent', $word_string)
                        ->find_many();

        // Separate queries for formNoAccent and formUtf8General
        // since Idiorm does not support OR'ing WHERE clauses.
        $field = StringUtil::hasDiacritics($definition_string) ? 'formNoAccent' : 'formUtf8General';
        $def_lexem_id_by_noAccent = Model::factory('Lexem')
                                  ->select('id')
                                  ->where($field, $definition_string)
                                  ->find_one();

        $def_lexem_id_by_utf8General = Model::factory('Lexem')
                                     ->select('id')
                                     ->where('formUtf8General', $definition_string)
                                     ->find_one();

        // Linked lexem was not found in the database.
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
          if ($word_lexem_id->lexemId === $def_lexem_id_by_noAccent->id) {
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
          $lexem_model = Model::factory('Lexem')
                       ->select('formNoAccent')
                       ->select('modelType')
                       ->select('modelNumber')
                       ->where_id_is($word_lexem_id->lexemId)
                       ->find_one();

          if ($lexem_model->modelType === "IL" ||
              $lexem_model->modelType === "PT" ||
              $lexem_model->modelType === "A" ||
              ($lexem_model->modelType === "F" &&
               ($lexem_model->modelNumber === "107" ||
                $lexem_model->modelNumber === "113"))) {
            $nextstep = Model::factory('InflectedForm')
                      ->select('lexemId')
                      ->where('formNoAccent', $lexem_model->formNoAccent)
                      ->find_many();

            foreach ($nextstep as $one) {
              if ($one->lexemId === $def_lexem_id_by_noAccent->id) {
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
