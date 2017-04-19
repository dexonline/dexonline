<?php

class FlexStringUtil {
  private static $VOWELS = "aăâäåeéiîoöuüùy";

  private static $SUFFIX_PAIRS = array(array("'e", "e'a"),
                                       array("'ă", "'e"),
                                       array("'ă", "e'a"),
                                       array('a', 'e'),
                                       array('a', 'ă'),
                                       array('ă', 'a'),
                                       array('a', ''), // tibia
                                       array('ă', 'ea'),
                                       array('ă', 'e'),
                                       array('ă', 'i'), // plural
                                       array('â', 'i'),
                                       array('â', 'âi'), // mână
                                       array('â', 'a'), // râmâne - rămase
                                       array('â', 'ă'), // râmâne - rămăsei
                                       array('ă', ''), // popă
                                       array('b', 't'), // fierbe
                                       array('c', 's'), // duce, aduce
                                       array('c', 'che'), // bloca
                                       array('g', 'ghe'), // naviga
                                       array('d', 'g'), // ucide, purcede
                                       array('d', 'j'), // trând
                                       array('d', 's'), // arde, întinde
                                       array('d', 'z'),
                                       array('e', 'a'),
                                       array('e', 'ă'), // enumera
                                       array('el', 'ea'), // bălănel
                                       array('iel', 'ic'), // bălăiel
                                       array('iel', 'ia'), // bălăiel
                                       array('e', 'ea'),
                                       array('e', 'i'),
                                       array('e', 'uri'), // scumpete
                                       array('e', ''), // vale, femeie
                                       array('ea', 'e'),
                                       array('ea', 'ic'), // viorea
                                       array('g', 's'), // merge, mulge
                                       array('g', 'ps'), // frige, suge
                                       array('g', 't'), // frânge, sparge
                                       array('h', 'ș'), // leah
                                       array('i', 'ă'), // dormi
                                       array('i', 'e'),
                                       array('i', 'â'), // vinde
                                       array('i', 'o'), // veni / vino
                                       array('i', 'u'), // cumpl'i / cumplu
                                       array('ică', 'ele'), // păturică
                                       array('î', 'ă'), // vârî, coborî
                                       array('î', 'â'), // imambaialdî
                                       array('î', 'i'), // vârî, coborî
                                       array('î', 'e'), // vârî, coborî
                                       array('î', 'a'), // vârî, coborî
                                       array('k', 'c'), // tadjik / tadjici
                                       array('k', ''), // gobseck / gobseci
                                       array('l', 'i'),
                                       array('n', 's'), // pune, rămâne
                                       array('o', 'oa'),
                                       array('o', 'e'), // sombrero
                                       array('o', 'i'),
                                       array('o', 'uri'), // agio
                                       array('o', ''), // picolo
                                       array('oa', 'e'),
                                       array('oa', 'o'),
                                       array('oa', 'u'), // coase
                                       array('s', 'ș'),
                                       array('sc', 'șt'), // mosc
                                       array('sc', 'st'), // fantasc
                                       array('ss', 'ș'), // gauss
                                       array('șt', 'sc'), // naște
                                       array('șc', 'șt'), // gălușcă
                                       array('ș', 's'), // ieși
                                       array('t', 'ț'),
                                       array('t', 's'), // admite
                                       array('tt', 'ț'), // watt
                                       array('ț', 't'), // sughița
                                       array('u', 'i'),
                                       array('u', ''), // acațiu
                                       array('u', 'o'), // turna
                                       array('u', 'oa'), // turna
                                       array('x', 'cș'),
                                       array('z', 'j'),
                                       array('z', 'd'), // auzi

                                       // Explicitly listed words
                                       array('moale', 'moi'),
                                       array('oră', 'urori'), // soră, noră
                                       array('piele', 'piei'),
                                       array('caro', 'carale'),
                                       array('mânc', 'mănânc'),
                                       array('usc', 'usuc'),
                                       array('lua', 'ia'),
                                       array('sări', 'sai'), // sări
                                       array('sări', 'săi'), // sări
                                       array('pieri', 'piei'),
                                       array('veni', 'vi'), // veni
                                       array('fi', 'sunt'),
                                       array('fi', 'e'),
                                       array('fi', 'îi'),
                                       array('fi', 'îs'),
                                       array('fi', 'erai'),
                                       array('fi', 'erați'),
                                       array('fi', 'fu'),
                                       array('ii', 'iam'), // vâjii, scârții
                                       array('ii', 'iai'), // vâjii, scârții
                                       array('ii', 'ia'), // vâjii, scârții
                                       array('ii', 'iați'), // vâjii, scârții
                                       array('ii', 'iau'), // vâjii, scârții
                                       array('părea', 'pai'),
                                       array('avea', 'a'),
                                       array('vrea', 'vom'),
                                       array('vrea', 'veți'),
                                       array('vrea', 'vor'),
                                       array('vrea', 'vei'),
                                       array('vrea', 'eți'),
                                       array('vrea', 'voi'),
                                       array('vrea', 'ei'),
                                       array('vrea', 'va'),
                                       array('vrea', 'ăți'),
                                       array('vrea', 'oi'),
                                       array('vrea', 'o'),
                                       array('vrea', 'îi'),
                                       array('vrea', 'a'),
                                       array('vrea', 'îți'),
                                       array('vrea', 'ăi'),
                                       array('mânea', 'mas'),
                                       array('mânea', 'mâie'),
                                       array('mânea', 'măse'),
                                       array('ține', 'ți'),
                                       array('pune', 'pu'),
                                       array('aduce', 'adă'),
                                       array('rămâne', 'rămâie'),
                                       array('rumpe', 'rum'),
                                       array('fierbe', 'fierse'),
                                       array('suge', 'supt'),
                                       array('ige', 'ipt'), // frige, înfige
                                       array('coace', 'copt'),
                                       array('coace', 'copse'),
                                       array('coace', 'coapse'),
                                       array('vârî', 'vâră'),
                                       array('zvârli', 'zvârlu'),
                                       array('putea', 'poci'),
                                       );

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
      $accentedVowelTo = StringUtil::getCharAt($to, $accentIndexTo + 1);
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
    
    $transforms = array();
    $places = array();
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
          array_splice($transforms, $i + 1, 0, array(Transform::create($bitFrom, $bitFrom)));
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
    if (StringUtil::getCharAt($from, 0) == StringUtil::getCharAt($to, 0)) {
      $result = self::extractTransformsHelper(mb_substr($from, 1),
                                              mb_substr($to, 1), $transforms,
                                              $places, $commonLength + 1);
      if ($result) {
        return 1;
      }
    }
  
    // Try one of the predefined combinations
    foreach (self::$SUFFIX_PAIRS as $pair) {
      if (StringUtil::startsWith($from, $pair[0]) && StringUtil::startsWith($to, $pair[1])) {
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

    if (StringUtil::startsWith($to, $from)) {
      $t = Transform::create('', mb_substr($to, mb_strlen($from)));
      $transforms[] = $t;
      $places[] = mb_strlen($from);
      return 1;
    }

    while (mb_strlen($from) > 1 && $to &&
           StringUtil::getLastChar($from) == StringUtil::getLastChar($to)) {
      $from = StringUtil::dropLastChar($from);
      $to = StringUtil::dropLastChar($to);
    }

    $place = 0;
    while (mb_strlen($from) > 1 && $to &&
           StringUtil::getCharAt($from, 0) == StringUtil::getCharAt($to, 0)) {
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
      $c = StringUtil::getCharAt($s, $i);
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
      $c = StringUtil::getCharAt($s, $i);
      if (self::isVowel($c)) {
        $pos--;
      }
    }

    if (!$pos) {
      // Sometimes we have to move the accent forward or backward to account
      // for diphthongs
      if ($vowel && StringUtil::getCharAt($s, $i) != $vowel) {
        if ($i > 0 && StringUtil::getCharAt($s, $i - 1) == $vowel) {
          $i--;
        } else if ($i < mb_strlen($s) - 1 &&
                   StringUtil::getCharAt($s, $i + 1) == $vowel) {
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
    // Remove the accent, but store its position
    $accentIndex = mb_strpos($s, "'");
    $s = str_replace("'", '', $s);

    // Go backwards through the transforms list and figure out where each
    // of them will take place
    $pos = mb_strlen($s);
    $places = array();
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
    if ($accentShift == ModelDescription::NO_ACCENT_SHIFT) {
      // Place the accent exactly where it is in the lexem form, if there is
      // one.
      if ($accentIndex !== false) {
        $result = mb_substr($result, 0, $accentIndex) . "'" .
          mb_substr($result, $accentIndex);
      }
    } else if ($accentShift != ModelDescription::UNKNOWN_ACCENT_SHIFT) {
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

?>
