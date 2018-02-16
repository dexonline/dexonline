<?php

/**
 * String manipulation functions related to abbreviations.
 * */
class Abbrev {

  // do not mark abbreviations automatically unless preceded and followed by one of these
  const LEADERS = '[ @$%(\["<\n-]';
  const FOLLOWERS = '([ @$%,;:)\]*"\/\n-]|$)';

  private static $ABBREV_INDEX = null; // These will be loaded lazily
  private static $ABBREVS = [];

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
      //self::loadAbbreviationsIndex();
      $abbrevs = [];

      $results = Model::factory('Abbreviation')
        ->where_like('sourceId', $sourceId)
        ->order_by_asc('short')
        ->find_array();

      if (!empty($results)) {
        foreach ($results as $abbrev) {
          $numWords = 1 + substr_count($abbrev['short'], ' ');

          // maybe there's no need for manually escaping dot - it will be done later with preg_quote
          $regexp = str_replace([' '], [' *'], $abbrev['short']);

          // must escape main capturing group $regexp as it may containg regexp syntax!!
          $regexp = preg_quote($regexp);

          if ($abbrev['caseSensitive'] != '1') {
            $matches = [];
            preg_match('/\p{L}/u', $regexp, $matches, PREG_OFFSET_CAPTURE);

            if (!Str::isAllUppercase($regexp)) {
              if (!empty($matches)) { // first letter of abbrev will become [Xx] -> geol. will match [Gg]eol.
                $regexp = sprintf('%s[%s]%s', 
                  mb_substr($regexp, 0, $matches[0][1]), 
                  Str::getUpperLowerString($matches[0][0]), 
                  mb_substr($regexp, $matches[0][1] + 1));
              }
            }
          }

          $regexp = sprintf('(?<=%s)(%s)(?=%s)', self::LEADERS, $regexp, self::FOLLOWERS);

          $abbrevs[$abbrev['short']] = [
            'id' => $abbrev['id'],
            'to' => $abbrev['long'],
            'enforced' => $abbrev['enforced'] == '1',
            'ambiguous' => $abbrev['ambiguous'] == '1',
            'caseSensitive' => $abbrev['caseSensitive'] == '1',
            'regexp' => $regexp,
            'numWords' => $numWords,
          ];
        }
        // Sort the list by number of words, then by ambiguous
        uasort($abbrevs, 'self::abbrevCmp');
      }
      self::$ABBREVS[$sourceId] = $abbrevs;
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
      $regexp = sprintf('/%s/u', $tuple['regexp']);
      preg_match_all($regexp, $s, $matches, PREG_OFFSET_CAPTURE);
      if (count($matches[1])) {
        foreach (array_reverse($matches[1]) as $match) {
          $orig = $match[0];
          $position = $match[1];

          if (!$hashMap[$position]) { // Don't replace anything if we are already between hash signs
            if ($tuple['ambiguous']) {
              if (!array_key_exists($position, $positionsUsed)) {
                $ambiguous[] = [
                  'abbrev' => $from,
                  'position' => $position,
                  'length' => strlen($orig),
                ];
                $positionsUsed[$position] = true;
              }
            } else {
              $replacement = $tuple['enforced'] ? $from : $orig;
              $s = substr_replace($s, "#$replacement#", $position, strlen($orig));
              array_splice($hashMap, $position, strlen($orig), array_fill(0, 2 + strlen($replacement), true));
            }
          }
        }
      }
    }
    return [$s, $ambiguous];
  }

  /** Returns a parallel array of booleans. Each element is true if $s[$i] lies inside a pair of hash signs, false otherwise * */
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
    $key = array_search($s, $abbrevList, true);
    if (!is_numeric($key)) {
      $abbrevListLower = array_map('mb_strtolower', $abbrevList);
      $key = array_search($s, $abbrevListLower, true);
      if (!is_numeric($key)) {
        $s = mb_strtolower($s);
        $key = array_search($s, $abbrevListLower, true);
        if (!is_numeric($key)) {
          return null; // every type of search failed
        }
      }
    }
    return $abbrevList[$key];
  }

  static function htmlizeAbbreviations($s, $sourceId, &$errors) {
    $abbrevs = self::loadAbbreviations($sourceId);
    $matches = [];
    preg_match_all("/(?<!\\\\)#([^#]*)#/", $s, $matches, PREG_OFFSET_CAPTURE);
    if (count($matches[1])) {
      foreach (array_reverse($matches[1]) as $match) {
        $from = $match[0];
        $matchingKey = self::bestAbbrevMatch($from, array_keys($abbrevs));
        $position = $match[1];
        if ($matchingKey) {
          $hint = $abbrevs[$matchingKey]['to'];
        } else {
          $hint = 'abreviere necunoscută';
          if ($errors !== null) {
            $errors[] = "Abreviere necunoscută: «{$from}».";
          }
        }
        $hint = htmlspecialchars(Str::htmlize($hint, $sourceId));
        $s = substr_replace($s, "<abbr class=\"abbrev\" data-html=\"true\" title=\"$hint\">$from</abbr>", $position - 1, 2 + strlen($from));
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
        $matchingKey = self::bestAbbrevMatch($from, array_keys($abbrevs));
        $position = $match[1];
        if ($matchingKey) {
          $to = $abbrevs[$matchingKey]['to'];
          $s = substr_replace($s, $to, $position - 1, 2 + strlen($from));
        }
      }
    }
    return $s;
  }

}
