<?php

class AdminStringUtil {
  private static $HTML_SYMBOLS = [
    'internal' => [' - ', ' ** ', ' * '],
    'html' => [' &#x2013; ', ' &#x2666; ', ' &#x25ca; '],
  ];

  private static $ACCENTS = [
    'accented' => [
      'á', 'Á', 'ắ', 'Ắ', 'ấ', 'Ấ', 'é', 'É', 'í', 'Í', 'î́', 'Î́',
      'ó', 'Ó', 'ú', 'Ú', 'ǘ', 'Ǘ', 'ý', 'Ý',
    ],
    'unaccented' => [
      'a', 'A', 'ă', 'Ă', 'â', 'Â', 'e', 'E', 'i', 'I', 'î', 'Î',
      'o', 'O', 'u', 'U', 'ü', 'Ü', 'y', 'Y',
    ],
  ];

  private static $ILLEGAL_NAME_CHARS = '!@#$%^&*()_+=\\|[]{},.<>/?;:\'"`~0123456789';

  private static $ABBREV_INDEX = null; // These will be loaded lazily
  private static $ABBREVS = [];

  private static function process($s, $ops) {
    foreach ($ops as $op) {
      $s = call_user_func($op, $s);
    }
    return $s;
  }

  static function internalizeWordName($name) {
    return self::process($name, [
      'self::shorthandToUnicode',
      'self::removeAccents',
      'strip_tags',
      'mb_strtolower',
      'StringUtil::stripHtmlEscapeCodes',
      'self::stripIllegalCharacters',
    ]);
  }

  /**
   * Extracts the term that the definition is *probably* defining. That is,
   * more or less, the first word in the definition, but we have lots of special
   * cases to deal with the formatting.
   */
  static function extractLexicon($def) {
    if (preg_match('/^[^@]*@([^@,]+)/', $def->internalRep, $matches)) {
      $s = $matches[1];
    } else {
      return '';
    }

    $s = self::removeAccents($s);

    $s = preg_replace('# (-|\X)+/$#', '', $s); // strip pronunciations (MDN)
    $s = explode('/', $s)[0]; // ignore everything after other slashes
    $s = preg_split('/\. *[\r\n]/', $s)[0]; // DAS is formatted on multiple lines

    $s = preg_replace('/^[-!*]+/', '', $s);
    $s = str_replace("\\'", "'", $s);
    $s = str_replace(['$', '\\|', '|'], '', $s); // Onomastic uses |
    $s = preg_replace('/[_^]{?[0-9]+}?/', '', $s); // strip homonym numbering and subscripts

    // strip homonyms and asterisks (Scriban)
    $s = preg_replace('/^[-* 0-9).]+/', '', $s);

    if (in_array($def->sourceId, [7, 9, 38, 62])) {
      // Strip 'a ', 'a se ' etc. from verbs
      $s = preg_replace('/^(a se |a \(se\) |a-și |a )/i', '', $s);
    }

    if ($def->sourceId == 9) {
      // parts of expressions are followed by a ': '
      $s = explode(':', $s)[0];

      // throw away inflected forms
      preg_match('/^([-A-ZĂÂÎȘȚÜ^0-9 ]+)( [a-zăâîșț()\\\\~1. ]+)?$/', $s, $matches);
      if ($matches) {
        $s = $matches[1];
      }
    }

    if ($def->sourceId == 71) {
      $s = preg_split('/\. /', $s)[0]; // D. Epitete includes ". Epitete" in the title
    }

    $s = trim($s);
    $s = mb_strtolower($s);

    // remove parentheses preceded by a space
    $s = preg_split('/ [\[\(]/', $s)[0];

    // strip some more characters
    $s = preg_replace('/[-:]+$/', '', $s);
    $s = preg_replace('/ [1i]\.$/', '', $s);
    $s = str_replace(['(', ')', '®', '!', '#'], '', $s);

    // if there is only one final dot, strip it
    $s = preg_replace("/^([^.]+)\.$/", '$1', $s);

    return $s;
  }

  /**
   * Returns the last word in a string (i.e., the last sequence of ASCII and/or
   * unicode letters. If the string contains no letters, returns the empty
   * string.
   */
  static function getLastWord($text) {
    $len = mb_strlen($text);

    $end = $len - 1;
    while ($end >= 0 && !self::isUnicodeLetter(StringUtil::getCharAt($text, $end))) {
      $end--;
    }

    if ($end == -1) {
      return '';
    }

    $start = $end - 1;
    while ($start >= 0 && self::isUnicodeLetter(StringUtil::getCharAt($text, $start))) {
      $start--;
    }

    return mb_substr($text, $start + 1, $end - $start);
  }

  private static function isUnicodeLetter($char) {
    // according to http://php.net/manual/en/regexp.reference.unicode.php
    return preg_match('/^\p{L}*$/u', $char);
  }

  static function formatLexem($s) {
    return self::process($s, ['trim', 'strip_tags', 'StringUtil::stripHtmlEscapeCodes']);
  }

  // If preserveAccent is true, then c'asă is not converted to cásă.
  static function internalize($text, $preserveAccent) {
    if ($preserveAccent) {
      $text = str_replace("'", "*****", $text);
    }
    $text = self::process($text, array('self::shorthandToUnicode', 'trim', 'strip_tags', 'self::unixNewlines'));
    if ($preserveAccent) {
      $text = str_replace("*****", "'", $text);
    }
    return $text;
  }

  // Generic purpose cleanup of a string. This should be true of all columns of all tables.
  static function cleanup($s) {
    $s = trim($s);    
    $s = str_replace([ 'ş', 'Ş', 'ţ', 'Ţ' ],
                     [ 'ș', 'Ș', 'ț', 'Ț' ],
                     $s);
    return $s;
  }

  // Sanitizes a definition or meaning. This is more elaborate than cleanup().
  static function sanitize($s, $sourceId = null, &$ambiguousMatches = null) {
    $s = self::cleanup($s);
    $s = str_replace([ '$$', '@@', '%%' ], '', $s);
    $s = self::shorthandToUnicode($s);
    $s = self::migrateFormatChars($s);
    // Do not strip tags here. strip_tags will strip them even if they're not
    // closed, so things like "< fr." will get stripped.
    if ($sourceId) {
      $s = self::markAbbreviations($s, $sourceId, $ambiguousMatches);
    }
    return self::internalizeAllReferences($s);
  }

  static function migrateFormatChars($s) {
    // First, check that all format chars come in pairs
    $len = strlen($s);
    $i = 0;
    $state = [ '$' => false, '@' => false, '%' => false ];
    $value = $len ? array_fill(0, $len, 4) : array(); // 0 = punctuation (.,;:), 1 = closing char, 2 = whitespace, 3 = opening char, 4 = other
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
    // Therefore, take every string consisting of (w)hitespace, (p)unctuation, (o)pening chars and (c)losing chars and rearrange it as p,c,w,o
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

  /**
   * Convert all user-entered references to the internal format, e.g.
   * |foo|bar| -> |foo|bar| (unchanged)
   * |foo moo|bar| -> |foo moo|bar| (unchanged)
   * |foo moo (@1@)|bar| -> |foo moo (@1@)|bar| (unchanged)
   * |foo|| -> |foo|foo|
   * |foo moo|| -> |foo moo|moo|
   * |foo moo (@1@)|| -> |foo moo (@1@)|moo|
   * |dealului|-| -> deal
   */
  static function internalizeAllReferences($s) {
    $result = '';
    $text = '';
    $ref = '';
    $prevChar = '';
    $mode = 0; // 0 = not between bars; 1 = text; 2 = reference
    for ($i = 0; $i < strlen($s); $i++) {
      $char = $s[$i];
      if ($char == '|' && $prevChar != "\\") {
        if ($mode == 2) {
          $newRef = self::internalizeReference($text, $ref);
          $result .= "|$text|$newRef|";
          $text = '';
          $ref = '';
        }
        $mode = ($mode + 1) % 3;
      } else {
        switch ($mode) {
        case 0: $result .= $char; break;
        case 1: $text .= $char; break;
        case 2: $ref .= $char;
        }
      }
      $prevChar = $char;
    }

    // If the number of pipes is not a multiple of three, escape the remaining pipes.
    switch ($mode) {
    case 0: break; // all good
    case 1: $result .= "\\|" . $text; break;
    case 2: $result .= "\\|" . $text . "\\|" . $ref; break;
    }
    return $result;
  }

  private static function internalizeReference($text, $ref) {
    if ($ref == '-') {
      return self::removeKnownSuffixes($text);
    } else if ($ref == '') {
      return self::getLastWord($text);
    } else {
      return $ref;
    }
  }

  /**
   * Strips out the suffix from a word, if possible. If no suffix matches,
   * returns the original word.
   */
  static function removeKnownSuffixes($word) {
    $suffixes = array('iei' => 'ie',
                      'ului' => '',
                      'ul' => '',
                      'uri' => '',
                      'urilor' => '',
                      'ilor' => '',
                      'i' => '',
                      'ă' => '',
                      'e' => '');
    foreach ($suffixes as $suffix => $replacement) {
      if (StringUtil::endsWith($word, $suffix)) {
        return substr($word, 0, strlen($word) - strlen($suffix)) . $replacement;
      }
    }
    return $word;
  }

  private static function _unicodeReplace($matches) {
    return self::chr(hexdec($matches[0]));
  }

  /**
   * Replace shorthand notations like 'a with Unicode symbols like á.
   * These are convenience symbols the user might type in, but we don't
   * want to store them as such in the database.
   */
  static function shorthandToUnicode($s) {
    // Replace \abcd with the Unicode character 0xABCD
    $s = preg_replace_callback('/\\\\([\dabcdefABCDEF]{4,5})/', 'self::_unicodeReplace', $s);

    // Remove non-breaking spaces and soft hyphens.
    $s = str_replace(chr(0xc2) . chr(0xa0), ' ', $s);
    $s = str_replace(chr(0xc2) . chr(0xad), '', $s);
  
    // A bit of a hack: We should not replace \'a with \á, therefore we isolate
    // the \' compound first and restore it at the end.
    $s = preg_replace('/\\\\(.)/', '[[[$1]]]', $s);
    $s = str_replace(StringUtil::$LETTERS['shorthand'], StringUtil::$LETTERS['unicode'], $s);
    $s = preg_replace('/\\[\\[\\[(.)\\]\\]\\]/', '\\\\$1', $s);
    return $s;
  }

  static function unixNewlines($s) {
    $s = str_replace("\r\n", "\n", $s);
    return $s;
  }

  // Converts the text to html. If $obeyNewlines is true, replaces \n with
  // <br>\n; otherwise leaves \n as \n. Collects unrecoverable errors in $errors.
  static function htmlize($s, $sourceId, &$errors = null, $obeyNewlines = false) {
    $s = htmlspecialchars($s, ENT_NOQUOTES);
    $s = self::convertReferencesToHtml($s);
    $s = self::convertMeaningMentionsToHtml($s);
    $s = self::insertSuperscripts($s);
    $s = self::internalToHtml($s, $obeyNewlines);
    $s = self::emphasize($s);
    $s = self::htmlizeAbbreviations($s, $sourceId, $errors);
    $s = self::minimalInternalToHtml($s);
    return $s;
  }

  /**
   * Runs a simple set of substitutions from the internal notations to HTML.
   * For example, replaces ** with &diams;. Does not look at bold/italic/spaced
   * characters.
   */
  static function minimalInternalToHtml($s) {
    return str_replace(self::$HTML_SYMBOLS['internal'], self::$HTML_SYMBOLS['html'], $s);
  }
  
  static function convertReferencesToHtml($s) {
    // Require that the first pipe character is not escaped (preceded by a backslash)
    return preg_replace('/([^\\\\])\|([^|]*)\|([^|]*)\|/', '$1<a class="ref" href="/definitie/$3">$2</a>', $s);
  }

  static function convertMeaningMentionsToHtml($s) {
    $s = preg_replace(
      '/([-a-zăâîșț]+)\[\[([0-9]+)\]\]/i',
      '<span data-toggle="popover" data-html="true" class="treeMention" title="$2">$1</span>',
      $s);
    $s = preg_replace(
      '/([-a-zăâîșț]+)\[([0-9]+)\]/i',
      '<span data-toggle="popover" data-html="true" class="mention" title="$2">$1</span>',
      $s);
    return $s;
  }

  /**
   * Replaces \^[-+]?[0-9]+ with <sup>...</sup>.
   * Replaces \_[-+]?[0-9]+ with <sub>...</sub>.
   */
  static function insertSuperscripts($text) {
    $patterns = array("/\^(\d)/", "/_(\d)/",
                      "/\^\{([^}]*)\}/", "/_\{([^}]*)\}/");
    $replace = array("<sup>$1</sup>", "<sub>$1</sub>",
                     "<sup>$1</sup>", "<sub>$1</sub>");
    return preg_replace($patterns, $replace, $text);
  }

  /**
   * The bulk of the HTML conversion. A few things happen in other places, such
   * as insertSuperscripts(). This must be called before
   * insertSuperscripts, or it may replace x^123 with x ^ 1 2 3.
   */
  static function internalToHtml($s, $obeyNewlines) {
    // We can't have user-entered tags since we have called htmlspecialchars
    // already. However, we can have tags like <sup> and <a>.
    $inTag = false;
    $inBold = false;
    $inItalic = false;
    $inQuotes = false;
    $inSpaced = false;
    $inDeleted = false;
    $inInserted = false;

    $result = '';
    $len = mb_strlen($s);
    for ($i = 0; $i < $len; $i++) {
      $c = StringUtil::getCharAt($s, $i);
      if ($c == '<') {
        $inTag = true;
      } else if ($c == '>') {
        $inTag = false;
      }

      if ($inTag) {
        // Don't touch ANYTHING between < and >
        $result .= $c;
      } else  if ($c == '\\') {
        // Next character is escaped
        $i++;
        if ($i < $len) {
          $result .= StringUtil::getCharAt($s, $i);
        }
      } else if ($c == '"') {
        $inQuotes = !$inQuotes;
        $result .= $inQuotes ? '„' : '”';
      } else if ($c == '@') {
        $inBold = !$inBold;
        $result .= $inBold ? '<b>' : '</b>';
      } else if ($c == '$') {
        $inItalic = !$inItalic;
        $result .= $inItalic ? '<i>' : '</i>';
      } else if ($c == "\n") {
        $result .= $obeyNewlines ? "<br>\n" : "\n";
      } else if ($c == '%') {
        $inSpaced = !$inSpaced;
        $result .= $inSpaced ? '<span class="spaced">' : '</span>';
      } else if ($c == "†") {
        $inDeleted = !$inDeleted;
        $result .= $inDeleted ? "<del>" : "</del>";
      } else if ($c == "‡") {
        $inInserted = !$inInserted;
        $result .= $inInserted ? "<ins>" : "</ins>";
      } else {
        $result .= $c;
      }
    }
    return $result;
  }

  static function emphasize($s) {
    $count = 0;
    $s = preg_replace('/__(.*?)__/', '<span class="emph">$1</span>', $s, -1, $count);
    if ($count) {
      $s = "<span class=\"deemph\">$s</span>";
    }
    return $s;
  }

  static function xmlizeOptional($s) {
    return self::minimalInternalToHtml($s);
  }

  private static function _ordReplace($matches) {
    return '\\' . '&#x' . dechex(self::ord($matches[1])) . ';';
  }

  static function xmlizeRequired($s) {
    // Escape <, > and &
    $s = htmlspecialchars($s, ENT_NOQUOTES);
    // Replace backslashed characters with their XML escape code
    $s = preg_replace_callback('/\\\\(.)/', 'self::_ordReplace', $s);
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
    $result = array();
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
    return str_replace(self::$ACCENTS['accented'], self::$ACCENTS['unaccented'], $s);
  }

  private static function stripIllegalCharacters($s) {
    $result = '';
    $len = mb_strlen($s);
    for ($i = 0; $i < $len; $i++) {
      $c = StringUtil::getCharAt($s, $i);
      if (strstr(self::$ILLEGAL_NAME_CHARS, $c) === false) {
        $result .= $c;
      }
    }
    return $result;
  }

  /**
   * Creates a map of $sourceId => array of sections to use.
   * Each section resides in a file named docs/abbrev/<section>.conf (these are loaded lazily).
   */
  static function loadAbbreviationsIndex() {
    if (!self::$ABBREV_INDEX) {
      self::$ABBREV_INDEX = array();
      $raw = parse_ini_file(Core::getRootPath() . "docs/abbrev/abbrev.conf", true);
      foreach ($raw['sources'] as $sourceId => $sectionList) {
        self::$ABBREV_INDEX[$sourceId] = preg_split('/, */', $sectionList);
      }
    }
    return self::$ABBREV_INDEX;
  }

  static function getAbbrevSectionNames() {
    self::loadAbbreviationsIndex();
    $sections = array();
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
      $result = array();

      if (array_key_exists($sourceId, self::$ABBREV_INDEX)) {
        $list = array();
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
          $regexp = str_replace(array('.', ' '), array("\\.", ' *'), $from);
          $pattern = "\W({$regexp})(\W|$)";
          $hasCaps = ($from !== mb_strtolower($from));
          $result[$from] = array('to' => $to, 'ambiguous' => $ambiguous, 'regexp' => $pattern, 'numWords' => $numWords, 'hasCaps' => $hasCaps);
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
    $positionsUsed = array();
    foreach ($abbrevs as $from => $tuple) {
      $matches = array();
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
                $ambiguousMatches[] = array('abbrev' => $from, 'position' => $position, 'length' => strlen($orig));
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
    $result = array();
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
    $matches = array();
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
    $matches = array();
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
    $values = array('i' => 1, 'v' => 5, 'x' => 10, 'l' => 50, 'c' => 100, 'd' => 500, 'm' => 1000);

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
    $bits = array(array('', 'I', 'II', 'III', 'IV', 'V', 'VI', 'VII', 'VIII', 'IX'),
                  array('', 'X', 'XX', 'XXX', 'XL', 'L', 'LX', 'LXX', 'LXXX', 'XC'),
                  array('', 'C', 'CC', 'CCC', 'CD', 'D', 'DC', 'DCC', 'DCCC', 'CM'));
    return $bits[2][$arabic % 1000 / 100] . $bits[1][$arabic % 100 / 10] . $bits[0][$arabic % 10];
  }

}
?>
