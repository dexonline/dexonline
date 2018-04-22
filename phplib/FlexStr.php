<?php

class FlexStr {
  private static $VOWELS = "aăâäåeéiîoöuüùy";

  private static $SUFFIX_PAIRS = [
    ["'e", "e'a"],
    ["'ă", "'e"],
    ["'ă", "e'a"],
    ['a', 'e'],
    ['a', 'ă'],
    ['ă', 'a'],
    ['a', ''], // tibia
    ['ă', 'ea'],
    ['ă', 'e'],
    ['ă', 'i'], // plural
    ['â', 'i'],
    ['â', 'âi'], // mână
    ['â', 'a'], // râmâne - rămase
    ['â', 'ă'], // râmâne - rămăsei
    ['ă', ''], // popă
    ['b', 't'], // fierbe
    ['c', 's'], // duce, aduce
    ['c', 'che'], // bloca
    ['g', 'ghe'], // naviga
    ['d', 'g'], // ucide, purcede
    ['d', 'j'], // trând
    ['d', 's'], // arde, întinde
    ['d', 'z'],
    ['e', 'a'],
    ['e', 'ă'], // enumera
    ['el', 'ea'], // bălănel
    ['iel', 'ic'], // bălăiel
    ['iel', 'ia'], // bălăiel
    ['e', 'ea'],
    ['e', 'i'],
    ['e', 'uri'], // scumpete
    ['e', ''], // vale, femeie
    ['ea', 'e'],
    ['ea', 'ic'], // viorea
    ['g', 's'], // merge, mulge
    ['g', 'ps'], // frige, suge
    ['g', 't'], // frânge, sparge
    ['h', 'ș'], // leah
    ['i', 'ă'], // dormi
    ['i', 'e'],
    ['i', 'â'], // vinde
    ['i', 'o'], // veni / vino
    ['i', 'u'], // cumpl'i / cumplu
    ['ică', 'ele'], // păturică
    ['î', 'ă'], // vârî, coborî
    ['î', 'â'], // imambaialdî
    ['î', 'i'], // vârî, coborî
    ['î', 'e'], // vârî, coborî
    ['î', 'a'], // vârî, coborî
    ['k', 'c'], // tadjik / tadjici
    ['k', ''], // gobseck / gobseci
    ['l', 'i'],
    ['n', 's'], // pune, rămâne
    ['o', 'oa'],
    ['o', 'e'], // sombrero
    ['o', 'i'],
    ['o', 'uri'], // agio
    ['o', ''], // picolo
    ['oa', 'e'],
    ['oa', 'o'],
    ['oa', 'u'], // coase
    ['s', 'ș'],
    ['sc', 'șt'], // mosc
    ['sc', 'st'], // fantasc
    ['ss', 'ș'], // gauss
    ['șt', 'sc'], // naște
    ['șc', 'șt'], // gălușcă
    ['ș', 's'], // ieși
    ['t', 'ț'],
    ['t', 's'], // admite
    ['tt', 'ț'], // watt
    ['ț', 't'], // sughița
    ['u', 'i'],
    ['u', ''], // acațiu
    ['u', 'o'], // turna
    ['u', 'oa'], // turna
    ['x', 'cș'],
    ['z', 'j'],
    ['z', 'd'], // auzi

    // Explicitly listed words
    ['moale', 'moi'],
    ['oră', 'urori'], // soră, noră
    ['piele', 'piei'],
    ['caro', 'carale'],
    ['mânc', 'mănânc'],
    ['usc', 'usuc'],
    ['lua', 'ia'],
    ['sări', 'sai'], // sări
    ['sări', 'săi'], // sări
    ['pieri', 'piei'],
    ['veni', 'vi'], // veni
    ['fi', 'sunt'],
    ['fi', 'e'],
    ['fi', 'îi'],
    ['fi', 'îs'],
    ['fi', 'erai'],
    ['fi', 'erați'],
    ['fi', 'fu'],
    ['ii', 'iam'], // vâjii, scârții
    ['ii', 'iai'], // vâjii, scârții
    ['ii', 'ia'], // vâjii, scârții
    ['ii', 'iați'], // vâjii, scârții
    ['ii', 'iau'], // vâjii, scârții
    ['părea', 'pai'],
    ['avea', 'a'],
    ['vrea', 'vom'],
    ['vrea', 'veți'],
    ['vrea', 'vor'],
    ['vrea', 'vei'],
    ['vrea', 'eți'],
    ['vrea', 'voi'],
    ['vrea', 'ei'],
    ['vrea', 'va'],
    ['vrea', 'ăți'],
    ['vrea', 'oi'],
    ['vrea', 'o'],
    ['vrea', 'îi'],
    ['vrea', 'a'],
    ['vrea', 'îți'],
    ['vrea', 'ăi'],
    ['mânea', 'mas'],
    ['mânea', 'mâie'],
    ['mânea', 'măse'],
    ['ține', 'ți'],
    ['pune', 'pu'],
    ['aduce', 'adă'],
    ['rămâne', 'rămâie'],
    ['rumpe', 'rum'],
    ['fierbe', 'fierse'],
    ['suge', 'supt'],
    ['ige', 'ipt'], // frige, înfige
    ['coace', 'copt'],
    ['coace', 'copse'],
    ['coace', 'coapse'],
    ['vârî', 'vâră'],
    ['zvârli', 'zvârlu'],
    ['putea', 'poci'],
  ];

  // Returns an array of transforms with the accent information at the end,
  // or null on errors.
  static function extractTransforms($from, $to, $isPronoun) {
    // Vowel count after the accent
    $accentPosFrom = self::findAccentPosition($from);
    $accentPosTo = self::findAccentPosition($to);

    // String position of the accent
    $accentIndexFrom = mb_strpos($from, "'");
    $accentIndexTo = mb_strpos($to, "'");
    if ($accentIndexTo !== false) {
      $accentedVowelTo = Str::getCharAt($to, $accentIndexTo + 1);
    }

    $from = str_replace("'", '', $from);
    $to = str_replace("'", '', $to);

    $t = self::extractTransformsNoAccents($from, $to, $isPronoun);
    if ($t == null) {
      return null;
    }

    if (!count($t)) {
      $t[] = Transform::createOrLoad('', '');
    }

    if (!$accentPosFrom || !$accentPosTo) {
      $accentShift = ModelDescription::UNKNOWN_ACCENT_SHIFT;
    } else if ($accentIndexFrom == $accentIndexTo &&
               mb_substr($from, 0, $accentIndexFrom + 1) ==
               mb_substr($to, 0, $accentIndexTo + 1)) {
      // Compare the beginning of $from and $to, up to and including the
      // accented character. Note that we have already removed the accent,
      // so we only add 1 above, not 2.
      $accentShift = ModelDescription::NO_ACCENT_SHIFT;
    } else {
      $accentShift = $accentPosTo;
      $t[] = $accentedVowelTo;
    }
    $t[] = $accentShift;
    return $t;
  }

  // Returns an array of transforms, or null on errors
  private static function extractTransformsNoAccents($from, $to, $isPronoun) {
    //print "Extracting [$from] [$to]\n";

    $transforms = [];
    $places = [];
    $result = $isPronoun
      ? self::extractPronounTransforms($from, $to, $transforms, $places)
      : self::extractTransformsHelper($from, $to, $transforms, $places, 0);
    if (!$result) {
      return null;
    }

    if (count($transforms) == 0) {
      $transforms[] = Transform::create('', '');
      return $transforms;
    }

    // In some cases, the returned set of transforms would apply to the wrong
    // letters. E.g. for ('căpăta'->'capăt'), the transforms are 'ă'->'a' and
    // 'a'->'', which would generate the form 'căpat'. The correct set of
    // transforms here is 'ă'->'a', 'ă'->'ă' and 'a'->''.
    for ($i = 0; $i < count($transforms); $i++) {
      $bitFrom = $transforms[$i]->transfFrom;
      if ($bitFrom != '') {
        $place1 = $places[$i] + mb_strlen($bitFrom);
        $place2 = ($i == count($transforms) - 1)
          ? mb_strlen($from)
          : $places[$i + 1];
        $posFound = mb_strpos($from, $bitFrom, $place1);
        if ($posFound !== false && $posFound < $place2) {
          // Add another transform $bitFrom->$bitFrom, so that this newly found
          // occurrence does not "steal" the transform.
          array_splice($transforms, $i + 1, 0, [Transform::create($bitFrom, $bitFrom)]);
          array_splice($places, $i + 1, 0, $posFound);
          // print "Adding $bitFrom -> $bitFrom to [$from][$to]\n";
        }
      }
    }

    return $transforms;
  }

  private static function extractTransformsHelper($from, $to, &$transforms, &$places, $commonLength) {
    if (!$from && !$to) {
      return 1;
    } else if (!$from) {
      $transforms[] = Transform::create('', $to);
      $places[] = $commonLength;
      return 1;
    } else if (!$to) {
      $transforms[] = Transform::create($from, '');
      $places[] = $commonLength;
      return 1;
    }

    // Skip common first letter
    if (Str::getCharAt($from, 0) == Str::getCharAt($to, 0)) {
      $result = self::extractTransformsHelper(mb_substr($from, 1),
                                              mb_substr($to, 1), $transforms,
                                              $places, $commonLength + 1);
      if ($result) {
        return 1;
      }
    }

    // Try one of the predefined combinations
    foreach (self::$SUFFIX_PAIRS as $pair) {
      if (Str::startsWith($from, $pair[0]) && Str::startsWith($to, $pair[1])) {
        $transforms[] = Transform::create($pair[0], $pair[1]);
        $places[] = $commonLength;
        $newFrom = mb_substr($from, mb_strlen($pair[0]));
        $newTo = mb_substr($to, mb_strlen($pair[1]));
        $result = self::extractTransformsHelper($newFrom, $newTo, $transforms, $places, $commonLength + mb_strlen($pair[0]));
        if ($result) {
          return 1;
        }
        array_pop($transforms);
        array_pop($places);
      }
    }

    return 0;
  }

  // Pronouns are trickier and have many special cases. Therefore, we
  // just trim the common beginning and ending parts and make a
  // transform of whatever's left.
  private static function extractPronounTransforms($from, $to, &$transforms, &$places) {
    $origFrom = $from;

    // We have one special case
    if ($from == 'doisprezecelea' && $to == 'douăsprezecea') {
      $transforms[] = Transform::create('i', 'uă');
      $transforms[] = Transform::create('lea', 'a');
      $places[] = 2;
      $places[] = 11;
      return 1;
    }

    if (Str::startsWith($to, $from)) {
      $t = Transform::create('', mb_substr($to, mb_strlen($from)));
      $transforms[] = $t;
      $places[] = mb_strlen($from);
      return 1;
    }

    while (mb_strlen($from) > 1 && $to &&
           Str::getLastChar($from) == Str::getLastChar($to)) {
      $from = Str::dropLastChar($from);
      $to = Str::dropLastChar($to);
    }

    $place = 0;
    while (mb_strlen($from) > 1 && $to &&
           Str::getCharAt($from, 0) == Str::getCharAt($to, 0)) {
      $from = mb_substr($from, 1);
      $to = mb_substr($to, 1);
      $place++;
    }

    $transforms[] = Transform::create($from, $to);
    $places[] = $place;
    return 1;
  }

  static function countVowels($s) {
    $count = 0;
    $len = mb_strlen($s);
    for ($i = 0; $i < $len; $i++) {
      $c = Str::getCharAt($s, $i);
      if (self::isVowel($c)) {
        $count++;
      }
    }
    return $count;
  }

  // Returns the number of vowels after the accent (') in $s.
  private static function findAccentPosition($s) {
    $parts = preg_split("/\'/", $s);
    assert(count($parts) <= 2);
    if (count($parts) == 1) {
      return 0; // No accent at all
    }
    return self::countVowels($parts[1]);
  }

  // Place the accent $pos vowels from the right
  static function placeAccent($s, $pos, $vowel) {
    $i = mb_strlen($s);

    while ($i && $pos) {
      $i--;
      $c = Str::getCharAt($s, $i);
      if (self::isVowel($c)) {
        $pos--;
      }
    }

    if (!$pos) {
      // Sometimes we have to move the accent forward or backward to account
      // for diphthongs
      if ($vowel && Str::getCharAt($s, $i) != $vowel) {
        if ($i > 0 && Str::getCharAt($s, $i - 1) == $vowel) {
          $i--;
        } else if ($i < mb_strlen($s) - 1 &&
                   Str::getCharAt($s, $i + 1) == $vowel) {
          $i++;
        } else {
          //print "Nu pot găsi vocala $vowel la poziția $pos în șirul $s\n";
        }
      }
      $s = self::insert($s, "'", $i);
    }

    return $s;
  }

  private static function isVowel($c) {
    return mb_strpos(self::$VOWELS, $c) !== false;
  }

  static function applyTransforms($s, $transforms, $accentShift, $accentedVowel) {
    // Remove the accent, but store its position.
    // Disregard double (secondary) accents and escaped accents.
    preg_match("/(?<!\\\\|\\')'(?!\\')/", $s, $matches, PREG_OFFSET_CAPTURE);
    if (count($matches)) {
      $accentIndex = $matches[0][1];
      $s = substr_replace($s, '', $accentIndex, 1);
    } else {
      $accentIndex = false;
    }

    // Go backwards through the transforms list and figure out where each
    // of them will take place
    $pos = mb_strlen($s);
    $places = [];
    for ($i = count($transforms) - 1; $i >= 0; $i--) {
      assert($transforms[$i]);
      $tfrom = $transforms[$i]->transfFrom;
      $tlen = mb_strlen($tfrom);
      if ($tfrom == '') {
        assert($i == count($transforms) - 1);
        assert($pos == mb_strlen($s));
      } else {
        while ($pos >= 0 && mb_substr($s, $pos, $tlen) != $tfrom) {
          $pos--;
        }

        // We should perhaps throw a nicer error, but for now just return
        // null;
        if ($pos < 0) {
          return null;
        }
      }
      $places[$i] = $pos;
      $pos--;
    }

    // Now go forward through the transforms and apply them
    $result = '';
    $previous = 0;
    for ($i = 0; $i < count($transforms); $i++) {
      $result .= mb_substr($s, $previous, $places[$i] - $previous);
      $result .= $transforms[$i]->transfTo;
      $previous = $places[$i] + mb_strlen($transforms[$i]->transfFrom);
    }
    if ($previous < mb_strlen($s)) {
      $result .= mb_substr($s, $previous);
    }

    // Try to place the accent
    if (($accentShift == ModelDescription::NO_ACCENT_SHIFT) ||
        ($accentShift == ModelDescription::UNKNOWN_ACCENT_SHIFT)) {
      // Place the accent exactly where it is in the lexeme form, if there is
      // one.
      if ($accentIndex !== false) {
        $result = substr_replace($result, "'", $accentIndex, 0);
      }
    } else {
      $result = self::placeAccent($result, $accentShift, $accentedVowel);
    }
    return $result;
  }

  static function contains($str, $substr) {
    return strpos($str, $substr) !== FALSE;
  }

  static function insert($str, $substr, $pos) {
    return mb_substr($str, 0, $pos) . $substr . mb_substr($str, $pos);
  }
}
