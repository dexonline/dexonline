<?php

function text_init() {
  $GLOBALS['text_shorthand'] = array("'^a", "'^A", "^'a", "^'A",
				       "'~a", "'~A", "~'a", "~'A", "'ă", "'Ă",
				       '~a', '~A', '^a', '^A', "'a", "'A",
               '`a', '`A', ':a', ':A', '°a', '°A',
				       ',c', ',C', "'c", "'C", '~c', '~C',
				       "'e", "'E", '`e', '`E', '^e', '^E',
				       ':e', ':E', '~e', '~E', '~g', '~G',
				       "'^i", "'^I", "^'i", "^'I",
				       "'i", "'I", '`i', '`I', '^i', '^I',
				       ':i', ':I', '~i', '~I', '~n', '~N',
				       "'o", "'O", '`o', '`O', '^o', '^O',
				       ':o', ':O', '~o', '~O', '~r', '~R',
				       '~s', '~S', ',s', ',S', ',t', ',T',
				       'ş', 'Ş', 'ţ', 'Ţ',
				       "'u", "'U", '`u', '`U', '^u', '^U',
				       ':u', ':U', '~u', '~U',
				       "'y", "'Y", ':y', ':Y', '~z', '~Z');
  $GLOBALS['text_unicode'] = array('ấ', 'Ấ', 'ấ', 'Ấ',
				    'ắ', 'Ắ', 'ắ', 'Ắ', 'ắ', 'Ắ',
				    'ă', 'Ă', 'â', 'Â', 'á', 'Á',
            'à', 'À', 'ä', 'Ä', 'å', 'Å',
				    'ç', 'Ç', 'ć', 'Ć', 'č', 'Č',
				    'é', 'É', 'è', 'È', 'ê', 'Ê',
				    'ë', 'Ë', 'ĕ', 'Ĕ', 'ğ', 'Ğ',
				    'î́', 'Î́', 'î́', 'Î́',
				    'í', 'Í', 'ì', 'Ì', 'î', 'Î',
				    'ï', 'Ï', 'ĩ', 'Ĩ', 'ñ', 'Ñ',
				    'ó', 'Ó', 'ò', 'Ò', 'ô', 'Ô',
				    'ö', 'Ö', 'õ', 'Õ', 'ř', 'Ř',
				    'š', 'Š', 'ș', 'Ș', 'ț', 'Ț',
				    'ș', 'Ș', 'ț', 'Ț',
				    'ú', 'Ú', 'ù', 'Ù', 'û', 'Û',
				    'ü', 'Ü', 'ŭ', 'Ŭ',
				    'ý', 'Ý', 'ÿ', 'Ÿ', 'ž', 'Ž');
  $GLOBALS['text_latin'] = array('a', 'A', 'a', 'A',
         'a', 'A', 'a', 'A', 'a', 'a',
				 'a', 'A', 'a', 'A', 'a', 'A',
         'a', 'A', 'a', 'A', 'a', 'A',
				 'c', 'C', 'c', 'C', 'c', 'C',
				 'e', 'E', 'e', 'E', 'e', 'E',
				 'e', 'E', 'e', 'E', 'g', 'G',
				 'i', 'I', 'i', 'I',
				 'i', 'I', 'i', 'I', 'i', 'I',
				 'i', 'I', 'i', 'I', 'n', 'N',
				 'o', 'O', 'o', 'O', 'o', 'O',
				 'o', 'O', 'o', 'O', 'r', 'R',
				 's', 'S', 's', 'S', 't', 'T',
				 's', 'S', 't', 'T',
				 'u', 'U', 'u', 'U', 'u', 'U',
				 'u', 'U', 'u', 'U',
				 'y', 'Y', 'y', 'Y', 'z', 'Z');

  $GLOBALS['text_internal'] = array(' - ', ' ** ', ' * ');
  $GLOBALS['text_html'] = array(' &#x2013; ', ' &#x2666; ', ' &#x2662; ');

  $GLOBALS['text_accented'] = array('á', 'Á', 'ắ', 'Ắ', 'ấ', 'Ấ',
                                    'é', 'É', 'í', 'Í', 'î́', 'Î́',
                                    'ó', 'Ó', 'ú', 'Ú', 'ý', 'Ý');

  $GLOBALS['text_explicitAccent'] = array("'a", "'A", "'ă", "'Ă", "'â", "'Â",
                                    "'e", "'E", "'i", "'I", "'î", "'Î",
                                    "'o", "'O", "'u", "'U", "'y", "'Y");

  $GLOBALS['text_unacccented'] = array('a', 'A', 'ă', 'Ă', 'â', 'Â',
                                       'e', 'E', 'i', 'I', 'î', 'Î',
                                       'o', 'O', 'u', 'U', 'y', 'Y');

  $GLOBALS['text_illegalNameChars'] =
    '!@#$%^&*()-_+=\\|[]{},.<>/?;:\'"`~0123456789';

  $GLOBALS['vowels'] = "aăâäåeéiîoöuüùy";

  $GLOBALS['text_stopWords'] = array(
                                "adj",
                                "al",
                                "ale",
                                "art",
                                "ca",
                                "care",
                                "ce",
                                "cu",
                                "de",
                                "despre",
                                "din",
                                "dinspre",
                                "după",
                                "este",
                                "etc",
                                "expr",
                                "face",
                                "fi",
                                "fig",
                                "fr",
                                "în",
                                "îi",
                                "îți",
                                "lat",
                                "la",
                                "mai",
                                "nu",
                                "pe",
                                "pentru",
                                "pl",
                                "pop",
                                "pr",
                                "prez",
                                "prin",
                                "refl",
                                "reg",
                                "sau",
                                "să",
                                "se",
                                "sil",
                                "sg",
                                "suf",
                                "și",
                                "te",
                                "tine",
                                "tranz",
                                "tu",
                                "ți",
                                "ție",
                                "un",
                                "unor",
                                "unui",
                                "var",
                                "vb",
                                );

  $GLOBALS['text_suffixPairs'] = array(
               array("'e", "e'a"),
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

  mb_internal_encoding("UTF-8");
  $GLOBALS['text_stopWordsLatin'] =
    text_unicodeToLatin($GLOBALS['text_stopWords']);
}

/**** Conversions from whatever the user typed in to our internal format ****/

function text_process($s, $ops) {
  foreach ($ops as $op) {
    $s = call_user_func($op, $s);
  }
  return $s;
}

function text_internalizeWordName($name) {
  return text_process($name, array('text_shorthandToUnicode', 'text_removeAccents', 'strip_tags', 'text_unicodeToLower',
                                   'text_stripHtmlEscapeCodes', 'text_stripWhiteSpace', 'text_stripIllegalCharacters'));
}

function text_formatLexem($s) {
  return text_process($s, array('text_shorthandToUnicode', 'text_explicitAccents', 'trim', 'strip_tags', 'text_stripHtmlEscapeCodes'));
}

// If preserveAccent is true, then c'as~a is converted to c'asă, but not
// to cásă.
function text_internalize($text, $preserveAccent) {
  if ($preserveAccent) {
    $text = str_replace("'", "*****", $text);
  }
  $text = text_process($text, array('text_shorthandToUnicode', 'trim', 'strip_tags'));
  if ($preserveAccent) {
    $text = str_replace("*****", "'", $text);
  }
  return $text;
}

function text_internalizeDefinition($def, $sourceId, &$ambiguousMatches = null) {
  $def = trim($def);
  $def = text_shorthandToUnicode($def);
  $def = _text_migrateFormatChars($def);
  // Do not strip tags here. strip_tags will strip them even if they're not
  // closed, so things like "< fr." will get stripped.
  $def = _text_markAbbreviations($def, $sourceId, $ambiguousMatches);
  return _text_internalizeAllReferences($def);
}

function _text_migrateFormatChars($s) {
  // First, check that all format chars come in pairs
  $len = strlen($s);
  $i = 0;
  $state = array('$' => false, '@' => false, '%' => false, '#' => false);
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
  $matches = array();
  preg_match_all('/[ .,;:@$%#]+/', $s, $matches, PREG_OFFSET_CAPTURE);
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
function _text_internalizeAllReferences($s) {
  $result = '';
  $text = '';
  $ref = '';
  $mode = 0; // 0 = not between bars; 1 = text; 2 = reference
  for ($i = 0; $i < strlen($s); $i++) {
    $char = $s[$i];
    if ($char == '|') {
      if ($mode == 2) {
	$newRef = _text_internalizeReference($text, $ref);
	$result .= "|$text|$newRef|";
	$text = '';
	$ref = '';
      }
      $mode = ($mode + 1) % 3;
    } else {
      switch($mode) {
      case 0: $result .= $char; break;
      case 1: $text .= $char; break;
      case 2: $ref .= $char;
      }
    }
  }
  return $result;
}

function _text_internalizeReference($text, $ref) {
  if ($ref == '-') {
    return _text_removeKnownSuffixes($text);
  } else if ($ref == '') {
    return _text_getLastWord($text);
  } else {
    return $ref;
  }
}

/**
 * Strips out the suffix from a word, if possible. If no suffix matches,
 * returns the original word.
 */
function _text_removeKnownSuffixes($word) {
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
    if (text_endsWith($word, $suffix)) {
      return substr($word, 0, strlen($word) - strlen($suffix)) . $replacement;
    }
  }
  return $word;
}

/**
 * Returns the last word in a string (i.e., the last sequence of ASCII and/or
 * unicode letters. If the string contains no letters, returns the empty
 * string.
 */
function _text_getLastWord($text) {
  $len = mb_strlen($text);

  $end = $len - 1;
  while ($end >= 0 && !text_isUnicodeLetter(text_getCharAt($text, $end))) {
    $end--;
  }

  if ($end == -1) {
    return '';
  }

  $start = $end - 1;
  while ($start >= 0 && text_isUnicodeLetter(text_getCharAt($text, $start))) {
    $start--;
  }

  return mb_substr($text, $start + 1, $end - $start);
}

/**
 * Replace shorthand notations like ~a with Unicode symbols like ă.
 * These are convenience symbols the user might type in, but we don't
 * want to store them as such in the database.
 */
function text_shorthandToUnicode($s) {
  // Replace \abcd with the Unicode character 0xABCD
  $s = preg_replace('/\\\\([\dabcdefABCDEF]{4})/e',
                    "text_chr(hexdec('$1'))",
                    $s);
  
  // A bit of a hack: We should not replace \~e with \ĕ, therefore we isolate
  // the \~ compound first and restore it at the end.
  $s = preg_replace('/\\\\(.)/', '[[[$1]]]', $s);
  $s = str_replace($GLOBALS['text_shorthand'], $GLOBALS['text_unicode'], $s);
  $s = preg_replace('/\\[\\[\\[(.)\\]\\]\\]/', '\\\\$1', $s);
  return $s;
}

function text_removeAccents($s) {
  return str_replace($GLOBALS['text_accented'], $GLOBALS['text_unacccented'], $s);
}

// Note: This does not handle the mixed case of old orthgraphy and no diacriticals (e.g. inminind instead of înmânând).
// That case is inherently ambiguous. For example, if the query is 'gindind', the correct substitution is 'gândind',
// where the second 'i' is left unchanged.
function text_tryOldOrthography($cuv) {
  if (preg_match('/^sînt(em|eți)?$/', $cuv)) {
    return str_replace('î', 'u', $cuv);
  }

  if (mb_strlen($cuv) > 2) {
    $interior = mb_substr($cuv, 1, mb_strlen($cuv) - 2);
    if (mb_stripos($interior, 'î') !== FALSE) {
      return text_getCharAt($cuv, 0) . str_replace('î', 'â', $interior) . text_getLastChar($cuv);
    }
  }

  return NULL;
}

function _text_extractLexiconHelper($def) {
  $internalRep = $def->internalRep;
  if ($def->sourceId == 7 || $def->sourceId == 9) {
    // Some sources write @A se iubi@ instead of just @iubi@.
    if (text_startsWith($internalRep, '@A se ')) {
      $internalRep = '@' . substr($internalRep, 6);
    } else if (text_startsWith($internalRep, '@A (se) ')) {
      $internalRep = '@' . substr($internalRep, 8);
    } else if (text_startsWith($internalRep, '@A SE ')) {
      $internalRep = '@' . substr($internalRep, 6);
    } else if (text_startsWith($internalRep, '@A ')) {
      $internalRep = '@' . substr($internalRep, 3);
    }
  }
  if ($def->sourceId == 9) {
    // Sources separate the root with //, like @ZOOTEHNI//C ~ca (~ci, ~ce)
    $internalRep = str_replace('//', '', $internalRep);
  }
  $portion = '';

  $len = mb_strlen($internalRep);
  $begun = false;
  $inBold = false;
  $prevChar = '';
  for ($i = 0; $i < $len; $i++) {
    $c = text_getCharAt($internalRep, $i);

    if ($c == '@') {
      if ($inBold) {
        break;
      }
      $inBold = true;
    } else if (text_isUnicodeLetter($c)) {
      $begun = true;
      $portion .= $c;
    } else if ($inBold && $c == ' ') {
      // Ok in some sources, bad in others
      if ($def->sourceId == 9) {
        break;
      }
    } else if ($c == '(' || $c == ')') {
      // Continue in situations like @ABER(O)-@ (we should return ABERO),
      // break otherwise.
      if ($prevChar == ' ') {
        break;
      }
    } else if ($c == '-') {
      if($prevChar == ' ' || $prevChar == '(') {
        // Done -- it's a situation like @abstractiv -ă@ or @beldar (-re)@
        break;
      }
    } else if ($c == "'" or $c == "́") {
      // Ok -- probably used as an accent where we don't have a Unicode
      // character to represent it, such as @Î'NCOT, @ $încote$...
    } else if ($c == '.') {
      // Ok -- for example, @S.O.S@
    } else if ($begun) {
      break;
    }
    $prevChar = $c;
  }
  return $portion;
}

/**
 * Extracts the term that the definition is *probably* defining. That is,
 * more or less, the first word in the definition, but we have lots of special
 * cases to deal with the formatting.
 */
function text_extractLexicon($def) {
  $portion = _text_extractLexiconHelper($def);
  $portion = text_internalizeWordName($portion);
  return $portion;
}


/****** Conversions from our internal format to HTML (for search.php) ********/

// Converts the text to html. If $obeyNewlines is TRUE, replaces \n with
// <br/>\n; otherwise leaves \n as \n. Collects unrecoverable errors in $errors.
function text_htmlize($s, $sourceId, &$errors = null, $obeyNewlines = false) {
  $s = htmlspecialchars($s, ENT_NOQUOTES);
  $s = _text_convertReferencesToHtml($s);
  $s = _text_insertSuperscripts($s);
  $s = _text_internalToHtml($s, $obeyNewlines);
  $s = _text_htmlizeAbbreviations($s, $sourceId, $errors);
  $s = _text_minimalInternalToHtml($s);
  return $s;
}

/**
 * Runs a simple set of substitutions from the internal notations to HTML.
 * For example, replaces ** with &diams;. Does not look at bold/italic/spaced
 * characters.
 */
function _text_minimalInternalToHtml($s) {
  return str_replace($GLOBALS['text_internal'], $GLOBALS['text_html'], $s);
}

function _text_convertReferencesToHtml($s) {
  return preg_replace('/\|([^|]*)\|([^|]*)\|/',
		      '<a class="ref" href="/definitie/$2">$1</a>',
		      $s);
}

/**
 * Replaces \^[-+]?[0-9]+ with <sup>...</sup>.
 * Replaces \_[-+]?[0-9]+ with <sub>...</sub>.
 */
function _text_insertSuperscripts($text) {
  $patterns = array("/\^(\d)/", "/_(\d)/",
                    "/\^\{([^}]*)\}/", "/_\{([^}]*)\}/");
  $replace = array("<sup>$1</sup>", "<sub>$1</sub>",
                   "<sup>$1</sup>", "<sub>$1</sub>");
  return preg_replace($patterns, $replace, $text);
}

/**
 * The bulk of the HTML conversion. A few things happen in other places, such
 * as _text_insertSuperscripts(). This must be called before
 * _text_insertSuperscripts, or it may replace x^123 with x ^ 1 2 3.
 */
function _text_internalToHtml($s, $obeyNewlines) {
  // We can't have user-entered tags since we have called htmlspecialchars
  // already. However, we can have tags like <sup> and <a>.
  $inTag = FALSE;
  $inBold = FALSE;
  $inItalic = FALSE;
  $inQuotes = FALSE;
  $inSpaced = FALSE;

  $result = '';
  $len = mb_strlen($s);
  for ($i = 0; $i < $len; $i++) {
    $c = text_getCharAt($s, $i);
    if ($c == '<') {
      $inTag = TRUE;
    } else if ($c == '>') {
      $inTag = FALSE;
    }

    if ($inTag) {
      // Don't touch ANYTHING between < and >
      $result .= $c;
    } else  if ($c == '\\') {
      // Next character is escaped
      $i++;
      if ($i < $len) {
        $result .= text_getCharAt($s, $i);
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
      $result .= $obeyNewlines ? "<br/>\n" : "\n";
    } else if ($c == '%') {
      $inSpaced = !$inSpaced;
      $result .= $inSpaced ? '<span class="spaced">' : '</span>';
    } else {
      $result .= $c;
    }
  }
  return $result;
}

/******* Conversions from our internal format to XML (for update.php) ********/

function text_xmlizeOptional($s) {
  return _text_minimalInternalToHtml($s);
}

function text_xmlizeRequired($s) {
  // Escape <, > and &
  $s = htmlspecialchars($s, ENT_NOQUOTES);
  // Replace backslashed characters with their XML escape code
  $s = preg_replace('/\\\\(.)/e',
                    "'&#x' . dechex(text_ord('$1')) . ';'",
                    $s);
  return $s;
}

function text_unicodeToLatin($s) {
  return str_replace($GLOBALS['text_unicode'], $GLOBALS['text_latin'], $s);
}


/***************************** Other functions **************************/

function text_endsWith($string, $substring) {
  $lenString = strlen($string);
  $lenSubstring = strlen($substring);
  $endString = substr($string, $lenString - $lenSubstring, $lenSubstring);
  return $endString == $substring;
}

function text_startsWith($string, $substring) {
  $startString = substr($string, 0, strlen($substring));
  return $startString == $substring;
}

function text_isUnicodeLetter($char) {
  return ctype_alpha($char) || in_array($char, $GLOBALS['text_unicode']);
}

/**
 * True if it contains any Unicode (but non-Latin) letters.
 */
function text_hasDiacritics($s) {
  $len = mb_strlen($s);
  for ($i = 0; $i < $len; $i++) {
    $char = text_getCharAt($s, $i);
    if (in_array($char, $GLOBALS['text_unicode'])) {
      return true;
    }
  }
  return false;
}

function text_isAllDigits($s) {
  $len = mb_strlen($s);
  for ($i = 0; $i < $len; $i++) {
    $char = text_getCharAt($s, $i);
    if (!ctype_digit($char)) {
      return false;
    }
  }
  return true;
}

function text_isLowercase($s) {
  return $s != mb_strtoupper($s);
}

function text_isUppercase($s) {
  return $s != mb_strtolower($s);
}

function text_getCharAt($s, $index) {
  return mb_substr($s, $index, 1);
}

function text_getLastChar($s) {
  return text_getCharAt($s, mb_strlen($s) - 1);
}

function text_dropLastChar($s) {
  return mb_substr($s, 0, mb_strlen($s) - 1);
}

function text_unicodeToLower($s) {
  return mb_strtolower($s);
}

function text_unicodeToUpper($s) {
  return mb_strtoupper($s);
}

// Assumes that the string is trimmed
function text_capitalize($s) {
  if (!$s) {
    return $s;
  }
  return text_unicodeToUpper(text_getCharAt($s, 0)) . mb_substr($s, 1);
}

function text_cleanupQuery($query) {
  $query = str_replace(array('"', "'"), array("", ""), $query);
  if (text_startsWith($query, 'a ')) {
    $query = substr($query, 2);
  }
  $query = trim($query);
  $query = strip_tags($query);
  if (!text_hasRegexp($query)) {
    $query = text_shorthandToUnicode($query);
  }
  $query = text_stripHtmlEscapeCodes($query);
  // Delete all kinds of illegal symbols, but use them as word delimiters. Allow dots, dashes and spaces
  $query = preg_replace("/[!@#$%&()_+=\\\\{}'\":;<>,\/]/", " ", $query);
  $query = preg_replace("/\s+/", " ", $query);
  $query = mb_substr($query, 0, 50);
  return $query;
}

function text_hasRegexp($query) {
  return preg_match("/[*?|\[\]]/", $query);
}

function text_dexRegexpToMysqlRegexp($s) {
  if (preg_match("/[|\[\]]/", $s)) {
    return "rlike '^(" . str_replace(array("*", "?"), array(".*", "."), $s) .
      ")$'";
  } else {
    return "like '" . str_replace(array("*", "?"), array("%", "_"), $s) . "'";
  }
}

/** Generates a set of clauses usable for counting or fetching results */
function text_analyzeQuery($query) {
  $hasDiacritics = text_hasDiacritics($query);
  $hasRegexp = text_hasRegexp($query);
  $isAllDigits = text_isAllDigits($query);

  return array($hasDiacritics, $hasRegexp, $isAllDigits);
}

function text_scrambleEmail($email) {
  return str_replace(array("@", "."), array("AT", "DOT"), $email);
}

function text_chr($u) {
  return mb_convert_encoding(pack('N', $u), 'UTF-8', 'UCS-4BE');
}

function text_ord($s) {
  $arr = unpack('N', mb_convert_encoding($s, 'UCS-4BE', 'UTF-8'));
  return $arr[1];
}

function text_reverse($s) {
  $result = '';
  $len = mb_strlen($s);
  for ($i = 0; $i < $len; $i++) {
    $char = text_getCharAt($s, $i);
    $result = $char . $result;
  }
  return $result;
}

function text_contains($str, $substr) {
  return strpos($str, $substr) !== FALSE;
}

// Returns an array of transforms with the accent information at the end,
// or null on errors.
function text_extractTransforms($from, $to, $isPronoun) {
  // Vowel count after the accent
  $accentPosFrom = text_findAccentPosition($from);
  $accentPosTo = text_findAccentPosition($to);

  // String position of the accent
  $accentIndexFrom = mb_strpos($from, "'");
  $accentIndexTo = mb_strpos($to, "'");
  if ($accentIndexTo !== false) {
    $accentedVowelTo = text_getCharAt($to, $accentIndexTo + 1);
  }

  $from = str_replace("'", '', $from);
  $to = str_replace("'", '', $to);

  $t = text_extractTransformsNoAccents($from, $to, $isPronoun);
  if ($t == null) {
    return null;
  }

  if (!count($t)) {
    $t[] = Transform::createOrLoad('', '');
  }

  if (!$accentPosFrom || !$accentPosTo) {
    $accentShift = UNKNOWN_ACCENT_SHIFT;
  } else if ($accentIndexFrom == $accentIndexTo &&
             mb_substr($from, 0, $accentIndexFrom + 1) ==
             mb_substr($to, 0, $accentIndexTo + 1)) {
    // Compare the beginning of $from and $to, up to and including the
    // accented character. Note that we have already removed the accent,
    // so we only add 1 above, not 2.
    $accentShift = NO_ACCENT_SHIFT;
  } else {
    $accentShift = $accentPosTo;
    $t[] = $accentedVowelTo;
  }
  $t[] = $accentShift;
  return $t;
}

// Returns an array of transforms, or null on errors
function text_extractTransformsNoAccents($from, $to, $isPronoun) {
  //print "Extracting [$from] [$to]\n";

  $transforms = array();
  $places = array();
  $result = $isPronoun
    ? _text_extractPronounTransforms($from, $to, $transforms, $places)
    : _text_extractTransformsHelper($from, $to, $transforms, $places, 0);
  if (!$result) {
    return null;
  }
  
  if (count($transforms) == 0) {
    $transforms[] = new Transform('', '');
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
        array_splice($transforms, $i + 1, 0, array(new Transform($bitFrom, $bitFrom)));
        array_splice($places, $i + 1, 0, $posFound);
        // print "Adding $bitFrom -> $bitFrom to [$from][$to]\n";
      }
    }
  }

  return $transforms;
}

function _text_extractTransformsHelper($from, $to, &$transforms, &$places,
                                       $commonLength) {
  if (!$from && !$to) {
    return 1;
  } else if (!$from) {
    $transforms[] = new Transform('', $to);
    $places[] = $commonLength;
    return 1;
  } else if (!$to) {
    $transforms[] = new Transform($from, '');
    $places[] = $commonLength;
    return 1;
  }

  // Skip common first letter
  if (text_getCharAt($from, 0) == text_getCharAt($to, 0)) {
    $result = _text_extractTransformsHelper(mb_substr($from, 1),
                                            mb_substr($to, 1), $transforms,
                                            $places, $commonLength + 1);
    if ($result) {
      return 1;
    }
  }
  
  // Try one of the predefined combinations
  foreach ($GLOBALS['text_suffixPairs'] as $pair) {
    if (text_startsWith($from, $pair[0]) && text_startsWith($to, $pair[1])) {
      $transforms[] = new Transform($pair[0], $pair[1]);
      $places[] = $commonLength;
      $newFrom = mb_substr($from, mb_strlen($pair[0]));
      $newTo = mb_substr($to, mb_strlen($pair[1]));
      $result = _text_extractTransformsHelper($newFrom, $newTo, $transforms,
                                              $places, $commonLength +
                                              mb_strlen($pair[0]));
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
function _text_extractPronounTransforms($from, $to, &$transforms, &$places) {
  $origFrom = $from;

  // We have one special case
  if ($from == 'doisprezecelea' && $to == 'douăsprezecea') {
    $transforms[] = new Transform('i', 'uă');
    $transforms[] = new Transform('lea', 'a');
    $places[] = 2;
    $places[] = 11;
    return 1;
  }

  if (text_startsWith($to, $from)) {
    $t = new Transform('', mb_substr($to, mb_strlen($from)));
    $transforms[] = $t;
    $places[] = mb_strlen($from);
    return 1;
  }

  while (mb_strlen($from) > 1 && $to &&
         text_getLastChar($from) == text_getLastChar($to)) {
    $from = text_dropLastChar($from);
    $to = text_dropLastChar($to);
  }

  $place = 0;
  while (mb_strlen($from) > 1 && $to &&
	 text_getCharAt($from, 0) == text_getCharAt($to, 0)) {
    $from = mb_substr($from, 1);
    $to = mb_substr($to, 1);
    $place++;
  }

  $transforms[] = new Transform($from, $to);
  $places[] = $place;
  return 1;
}

function _text_getSuffixPairIndex($from, $to) {
  $len = count($GLOBALS['text_suffixPairs']);
  for ($i = 0; $i < $len; $i++) {
    $pair = $GLOBALS['text_suffixPairs'][$i];
    if ($pair[0] == $from && $pair[1] == $to) {
      return $i;
    }
  }
  return INFINITY;
}

function text_countVowels($s) {
  $count = 0;
  $len = mb_strlen($s);
  for ($i = 0; $i < $len; $i++) {
    $c = text_getCharAt($s, $i);
    if (text_isVowel($c)) {
      $count++;
    }
  }
  return $count;
}

function text_validateAlphabet($s, $alphabet) {
  $len = mb_strlen($s);
  for ($i = 0; $i < $len; $i++) {
    $c = text_getCharAt($s, $i);
    $found = mb_strpos($alphabet, $c);
    if ($found === false) {
      return false;
    }
  }
  return true;
}

// Returns the number of vowels after the accent (') in $s.
function text_findAccentPosition($s) {
  $parts = preg_split("/\'/", $s);
  assert(count($parts) <= 2);
  if (count($parts) == 1) {
    return 0; // No accent at all
  }
  return text_countVowels($parts[1]);
}

// Place the accent $pos vowels from the right
function text_placeAccent($s, $pos, $vowel) {
  $i = mb_strlen($s);

  while ($i && $pos) {
    $i--;
    $c = text_getCharAt($s, $i);
    if (text_isVowel($c)) {
      $pos--;
    }
  }

  if (!$pos) {
    // Sometimes we have to move the accent forward or backward to account
    // for diphthongs
    if ($vowel && text_getCharAt($s, $i) != $vowel) {
      if ($i > 0 && text_getCharAt($s, $i - 1) == $vowel) {
        $i--;
      } else if ($i < mb_strlen($s) - 1 &&
                 text_getCharAt($s, $i + 1) == $vowel) {
        $i++;
      } else {
        //print "Nu pot găsi vocala $vowel la poziția $pos în șirul $s\n";
      }
    }
    $s = text_insert($s, "'", $i);
  }

  return $s;
}

function text_insert($str, $substr, $pos) {
  return mb_substr($str, 0, $pos) . $substr . mb_substr($str, $pos);
}

function text_padRight($str, $length) {
  $len = strlen($str);
  $mbLen = mb_strlen($str);
  return str_pad($str, $length + ($len - $mbLen));
}

function text_isVowel($c) {
  return mb_strpos($GLOBALS['vowels'], $c) !== false;
}

function text_applyTransforms($s, $transforms, $accentShift, $accentedVowel) {
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
  if ($accentShift == NO_ACCENT_SHIFT) {
    // Place the accent exactly where it is in the lexem form, if there is
    // one.
    if ($accentIndex !== false) {
      $result = mb_substr($result, 0, $accentIndex) . "'" .
        mb_substr($result, $accentIndex);
    }
  } else if ($accentShift != UNKNOWN_ACCENT_SHIFT) {
    $result = text_placeAccent($result, $accentShift, $accentedVowel);
  }
  return $result;
}

// If you just extract each character with mb_substr, the complexity is O(N^2).
function text_unicodeExplode($s) {
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

function text_isStopWord($word, $hasDiacritics) {
  if (mb_strlen($word) == 1) {
    return true;
  }
  return $hasDiacritics
    ? in_array($word, $GLOBALS['text_stopWords'])
    : in_array($word, $GLOBALS['text_stopWordsLatin']);
}

function text_separateStopWords($words, $hasDiacritics) {
  $properWords = array();
  $stopWords = array();

  foreach ($words as $word) {
    if (text_isStopWord($word, $hasDiacritics)) {
      $stopWords[] = $word;
    } else {
      $properWords[] = $word;
    }
  }

  return array($properWords, $stopWords);
}

function text_explicitAccents($s) {
  return str_replace($GLOBALS['text_accented'], $GLOBALS['text_explicitAccent'], $s);
}

function text_stripWhiteSpace($s) {
  return str_replace(' ', '', $s);
}

function text_stripHtmlEscapeCodes($s) {
  return preg_replace("/&[^;]+;/", "", $s);
}

function text_stripIllegalCharacters($s) {
  $result = '';
  $len = mb_strlen($s);
  for ($i = 0; $i < $len; $i++) {
    $c = text_getCharAt($s, $i);
    if (strstr($GLOBALS['text_illegalNameChars'], $c) === FALSE) {
      $result .= $c;
    }
  }
  return $result;
}

function text_replace_st($tpl_output) {
  $tpl_output = str_replace('ș', 'ş', $tpl_output);
  $tpl_output = str_replace('ț', 'ţ', $tpl_output);
  $tpl_output = str_replace('Ș', 'Ş', $tpl_output);
  $tpl_output = str_replace('Ț', 'Ţ', $tpl_output);
  return $tpl_output;
}

function text_replace_ai($tpl_output) {
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

/**
 * Creates a map($sourceId => map($from, pair($to, $ambiguous))).
 * That is, for each sourceId and abbreviated text, we store the expanded text and whether the abbreviation is ambiguous.
 * An ambigious abbreviation such as "top" or "gen" also has a meaning as an inflected form.
 * Ambiguous abbreviations should be expanded carefully, or with human approval.
 */
function text_loadAbbreviations() {
  if (empty($GLOBALS['abbrev'])) {
    $raw = parse_ini_file(util_getRootPath() . "docs/abbrev.conf", true);
    $result = array();
    foreach ($raw['sources'] as $sourceId => $sectionList) {
      $sections = preg_split('/, */', $sectionList);
      $list = array();
      foreach ($sections as $section) {
        // If an abbreviation is defined in several sections, use the one that's defined later
        $list = array_merge($list, $raw[$section]);
      }
      $result[$sourceId] = array();
      foreach ($list as $from => $to) {
        $ambiguous = ($from[0] == '*');
        if ($ambiguous) {
          $from = substr($from, 1);
        }
        $numWords = 1 + substr_count($from, ' ');
        $regexp = str_replace(array('.', ' '), array("\\.", ' *'), $from);
        $pattern = "[^a-zăâîșțáéíóú]({$regexp})([^a-zăâîșțáéíóú]|$)";
        $result[$sourceId][$from] = array('to' => $to, 'ambiguous' => $ambiguous, 'regexp' => $pattern, 'numWords' => $numWords);
      }
      // Sort the list by number of words, then by ambiguous
      uasort($result[$sourceId], '_text_abbrevCmp');
    }
    $GLOBALS['abbrev'] = $result;
  }
  return $GLOBALS['abbrev'];
}

function _text_abbrevCmp($a, $b) {
  if ($a['numWords'] < $b['numWords']) {
    return 1;
  } else if ($a['numWords'] > $b['numWords']) {
    return -1;
  } else {
    return $a['ambiguous'] - $b['ambiguous'];
  }
}

function _text_markAbbreviations($s, $sourceId, &$ambiguousMatches = null) {
  $abbrevs = text_loadAbbreviations();
  $hashMap = _text_constructHashMap($s);
  if (!array_key_exists($sourceId, $abbrevs)) {
    return $s;
  }
  foreach ($abbrevs[$sourceId] as $from => $tuple) {
    $matches = array();
    preg_match_all("/{$tuple['regexp']}/i", $s, $matches, PREG_OFFSET_CAPTURE);
    if (count($matches[1])) {
      foreach (array_reverse($matches[1]) as $match) {
        $orig = $match[0];
        $position = $match[1];

        if (!$hashMap[$position]) { // Don't replace anything if we are already between hash signs
          if ($tuple['ambiguous']) {
            if ($ambiguousMatches !== null) {
              $ambiguousMatches[] = array('abbrev' => $from, 'position' => $position, 'length' => strlen($orig));
            }
          } else {
            $replacement = text_isUppercase(text_getCharAt($orig, 0)) ? text_capitalize($from) : $from;
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
function _text_constructHashMap($s) {
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

function _text_htmlizeAbbreviations($s, $sourceId, &$errors = null) {
  $abbrevs = text_loadAbbreviations();
  if (!array_key_exists($sourceId, $abbrevs)) {
    return $s;
  }

  $matches = array();
  preg_match_all("/#([^#]*)#/", $s, $matches, PREG_OFFSET_CAPTURE);
  if (count($matches[1])) {
    foreach (array_reverse($matches[1]) as $match) {
      $from = $match[0];
      $lower = text_unicodeToLower($from);
      $position = $match[1];
      if (array_key_exists($lower, $abbrevs[$sourceId])) {
        $hint =  $abbrevs[$sourceId][$lower]['to'];
      } else {
        $hint =  'abreviere necunoscută';
        if ($errors !== null) {
          $errors[] = "Abreviere necunoscută: «{$from}». Verificați că după fiecare punct există un spațiu.";
        }
      }
      $s = substr_replace($s, "<span class=\"abbrev\" title=\"$hint\">$from</span>", $position - 1, 2 + strlen($from));
    }
  }
  return $s;
}

?>
