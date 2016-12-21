<?php

/**
 * Parse definitions from DOR, add LexemSources and mark them as structured
 * when possible.
 **/

require_once __DIR__ . '/../phplib/util.php';

$DOR_SOURCE_ID = 38;
$MY_USER_ID = 1;
$URL = 'https://dexonline.ro/admin/definitionEdit.php?definitionId='; // for edit links

// regex parts
$PART_HYPH = '( \(sil\. ([^)]+)\))';
$PART_PRON = '( \[(.*(pr\.|cit\.).*)\])';
$PART_COMMENT = '( \(([^)]+)\))';

$REGEX_WORD = "/^@([^@]+)@\s+/";
$REGEX_POS = "/^([-a-zăâîșț1-3.\/()+ ]+){$PART_HYPH}?{$PART_PRON}?([,;] |$)/";
$REGEX_INFL = "/^([-a-zăâîșț1-3.() ]+) [$]([-a-zA-ZÅÁăâîșțĂÂÎȘȚáéíóúýắấäüµΩ'., \\/]+)[$]{$PART_HYPH}?{$PART_COMMENT}?([,;] |$)/";

class ParseException extends Exception {
}

class SemanticException extends Exception {
}

class NewFormException extends Exception {
}

$INFL_NAME_MAP = [
  '1 pl.' => ['Verb% I,%plural'],
  '2 pl.' => ['Verb%a II-a,%plural'],
  '2 sg.' => ['Verb%a II-a,%singular'],
  '3 pl.' => ['Verb%a III-a,%plural'],
  '3 sg. și pl.' => ['Verb%a III-a,%singular%', 'Verb%a III-a,%plural'],
  '3 sg.' => ['Verb%a III-a,%singular'],
  'art.' => ['% articulat'],
  'conj. prez. 1 sg. și pl.' => ['Verb, Conjunctiv% I, singular', 'Verb, Conjunctiv% I, plural'],
  'conj. prez. 1 sg.' => ['Verb, Conjunctiv% I, singular'],
  'conj. prez. 1 și 2 sg.' => ['Verb, Conjunctiv% I, singular', 'Verb, Conjunctiv% a II-a, singular'],
  'conj. prez. 3 sg. și pl.' => ['%conjunctiv% a III-a, singular', '%conjunctiv% a III-a, plural'],
  'conj. prez. 3 sg.' => ['Verb, Conjunctiv% a III-a, singular'],
  'f. sg. și pl.' => ['%feminin, Nominativ-Acuzativ, singular, nearticulat', '%feminin, Nominativ-Acuzativ, plural, nearticulat'],
  'f. sg.' => ['%feminin, Nominativ-Acuzativ, singular%'],
  'f.' => ['%feminin, Nominativ-Acuzativ, singular, nearticulat'],
  'g.-d. art.' => ['%Genitiv-Dativ, singular, articulat'],
  'g.-d. m. și f.' => ['Pronume, masculin, Genitiv-Dativ%', 'Pronume, feminin, Genitiv-Dativ%'],
  'g.-d. m.' => ['Pronume, masculin, Genitiv-Dativ, singular'],
  'g.-d. pl. m. și f.' => ['Pronume, masculin, Genitiv-Dativ, plural', 'Pronume, feminin, Genitiv-Dativ, plural'],
  'g.-d. sg. art.' => ['%feminin, Genitiv-Dativ, singular, articulat'],
  'g.-d.' => ['%Genitiv-Dativ%'],
  'ger.' => ['%gerunziu%'],
  'imper. 2 pl.' => ['%imperativ%plural%'],
  'imper. 2 sg.' => ['%imperativ%singular%'],
  'imperf. 1 sg.' => ['%imperfect% I, singular'],
  'imperf. 3 sg.' => ['%imperfect% a III-a, singular'],
  'ind. prez. 1 sg. și 3 pl.' => ['%indicativ, prezent% I, singular', '%indicativ, prezent% a III-a, plural'],
  'ind. prez. 1 sg.' => ['%indicativ, prezent% I, singular'],
  'ind. prez. 1 și 2 sg.' => ['%indicativ, prezent% I, singular', '%indicativ, prezent% a II-a, singular'],
  'ind. prez. 3 pl.' => ['%indicativ, prezent% a III-a, plural'],
  'ind. prez. 3 sg. și pl.' => ['%indicativ, prezent% a III-a, singular', '%indicativ, prezent% a III-a, plural'],
  'ind. prez. 3 sg.' => ['%indicativ, prezent% a III-a, singular'],
  'ind. și conj. prez. 1 și 2 sg.' => ['%indicativ, prezent% I, singular', '%indicativ, prezent% a II-a, singular', '%conjunctiv% I, singular', '%conjunctiv% a II-a, singular'],
  'ind. și conj. prez. 3 sg. și pl.' => ['%indicativ, prezent% a III-a, singular', '%indicativ, prezent% a III-a, plural', '%conjunctiv% a III-a, singular', '%conjunctiv% a III-a, plural'],
  'ind. și conj. prez. 3 sg.' => ['%indicativ, prezent% a III-a, singular', '%conjunctiv% a III-a, singular'],
  'm.m.c.p. 1 sg.' => ['%mai mult ca perfect% I, singular'],
  'm.m.c.p. 3 sg.' => ['%mai mult ca perfect% a III-a, singular'],
  'neacc.' => ['pronume%'],
  'neg.' => ['%infinitiv prezent'], // negative imperative has the infinitive form
  'part.' => ['%participiu%'],
  'perf. s. 1 sg.' => ['%perfect simplu% I, singular'],
  'perf. s. 3 sg.' => ['%perfect simplu% a III-a, singular'],
  'pl. f.' => ['%feminin%plural%nearticulat%'],
  'pl. m. și f.' => ['%masculin%plural%nearticulat%', '%feminin%plural%nearticulat%'],
  'pl. n. și f.' => ['%feminin%plural%nearticulat%'],
  'pl.' => ['%nominativ-acuzativ, plural%'],
  'simb.' => [], // no inflections here, but we'll create a meaning for the symbol
];

// Part of speech to ModelType map
$POS_MAP = [
  'adj. f.' => ['A', 'MF', 'F', ],
  'adj. invar.' => ['I', ],
  'adj. m. (antepus)' => ['P', ],
  'adj. m. (postpus)' => ['P', ],
  'adj. m. și f.' => ['A', 'MF', ],
  'adj. m.' => ['A', 'M', 'MF', ],
  'adj. n.' => ['A', 'MF', 'N'],
  'adj.' => ['A', 'MF', ],
  'adv.' => ['I', ],
  'art. m.' => ['P', 'A'],
  'conjcț.' => ['I'],
  'interj.' => ['I'],
  'loc. adj.' => ['I'],
  'loc. adv.' => ['I'],
  'loc. s. f.' => ['I'],
  'num. card. m.' => ['P'],
  'num. card.' => ['P'],
  'num. m.' => ['P'],
  'num. ord.' => ['P'],
  'prep.' => ['I', ],
  'pron. dem. m.' => ['P'],
  'pron. m.' => ['P', ],
  'pron. neg.' => ['P'],
  'pron. neh. m.' => ['P'],
  'pron. neh.' => ['P'],
  'pron. pers.' => ['P'],
  's. f. art.' => ['F', ],
  's. f. invar.' => ['F', 'I'],
  's. f. pl.' => ['F'],
  's. f.' => ['A', 'F', 'MF', ],
  's. m. / s. n.' => ['M', 'N'],
  's. m. invar.' => ['M', 'I'],
  's. m. pl.' => ['M'],
  's. m. și f.' => ['MF'],
  's. m.' => ['A', 'M', 'MF', ],
  's. n. / s. m.' => ['M', 'N', ],
  's. n. art.' => ['N'],
  's. n. invar.' => ['N'],
  's. n. pl.' => ['I', ],
  's. n.' => ['A', 'MF', 'N', ],
  's. pr. f. art.' => ['SP'],
  's. pr. f. pl.' => ['SP'],
  's. pr. f.' => ['SP'],
  's. pr. m. art.' => ['SP'],
  's. pr. m.' => ['SP'],
  's. pr. n.' => ['SP'],
  'vb.' => ['V', 'VT', ],
];

// partial, see also getTagList()
$POS_TAG_MAP = [
  'adj. f.' => ['adjectiv feminin'],
  'adj. invar.' => ['adjectiv invariabil'],
  'adj. m. (antepus)' => ['adjectiv'],
  'adj. m. (postpus)' => ['adjectiv'],
  'adj. m. și f.' => ['adjectiv'],
  'adj. m.' => ['adjectiv'],
  'adj. n.' => ['adjectiv neutru'],
  'adj.' => ['adjectiv'],
  'adv.' => ['adverb'],
  'art. m.' => ['articol; articulat'],
  'conjcț.' => ['conjuncție'],
  'interj.' => ['interjecție'],
  'loc. adj.' => ['locuțiune adjectivală'],
  'loc. adv.' => ['locuțiune adverbială'],
  'loc. s. f.' => ['locuțiune substantivală'],
  'num. card. m.' => ['numeral cardinal'],
  'num. card.' => ['numeral cardinal'],
  'num. m.' => ['numeral'],
  'num. ord.' => ['numeral ordinal'],
  'prep.' => ['prepoziție'],
  'pron. dem. m.' => ['pronume demonstrativ'],
  'pron. m.' => ['pronume'],
  'pron. neg.' => ['pronume negativ'],
  'pron. neh. m.' => ['pronume nehotărât'],
  'pron. neh.' => ['pronume nehotărât'],
  'pron. pers.' => ['pronume personal'],
  's. f. art.' => ['substantiv feminin articulat'],
  's. f. invar.' => ['substantiv feminin invariabil'],
  's. f. pl.' => ['substantiv feminin plural'],
  's. f.' => ['substantiv feminin'],
  's. m. / s. n.' => ['substantiv masculin', 'substantiv neutru'],
  's. m. invar.' => ['substantiv masculin invariabil'],
  's. m. pl.' => ['substantiv masculin plural'],
  's. m. și f.' => ['substantiv masculin', 'substantiv feminin'],
  's. m.' => ['substantiv masculin'],
  's. n. / s. m.' => ['substantiv masculin', 'substantiv neutru'],
  's. n. art.' => ['substantiv neutru articulat'],
  's. n. invar.' => ['substantiv neutru invariabil'],
  's. n. pl.' => ['substantiv neutru plural'],
  's. n.' => ['substantiv neutru'],
  's. pr. f. art.' => ['substantiv propriu feminin articulat'],
  's. pr. f. pl.' => ['substantiv propriu feminin plural'],
  's. pr. f.' => ['substantiv propriu feminin'],
  's. pr. m. art.' => ['substantiv propriu masculin articulat'],
  's. pr. m.' => ['substantiv propriu masculin'],
  's. pr. n.' => ['substantiv propriu neutru'],
  'vb.' => ['verb'],
];

// Load the inflection IDs from the regexps
$inflMap = [];
foreach ($INFL_NAME_MAP as $key => $regexList) {
  $inflMap[$key] = [];
  foreach ($regexList as $regex) {
    $inflections = Model::factory('Inflection')
      ->where_like('description', $regex)
      ->find_many();
    if (empty($inflections)) {
      throw new Exception("No inflections matching regex [{$regex}]");
    }
    $ids = [];
    foreach ($inflections as $i) {
      $ids[] = $i->id;
    }
    $inflMap[$key][] = $ids;
  }
}

// Load the tags from the regexps
$tagMap = [];
foreach ($POS_TAG_MAP as $key => $valueList) {
  $tagMap[$key] = [];

  foreach ($valueList as $value) {
    $tag = Tag::get_by_value($value);
    if (!$tag) {
      throw new Exception("No tag matching value [{$value}]");
    }
    $tagMap[$key][] = $tag;
  }
}

$defs = Model::factory('Definition')
  ->select('id')
  ->where('sourceId', $DOR_SOURCE_ID)
  ->where('structured', 0)
  ->where_in('status', [Definition::ST_ACTIVE, Definition::ST_HIDDEN])
  ->order_by_asc('lexicon')
  // ->where_gt('lexicon', 'nitrogen')
  // ->limit(1000)
  ->find_many();
$parseErrors = $semanticErrors = $newFormErrors = 0;

foreach ($defs as $i => $defId) {
  try {
    $d = Definition::get_by_id($defId->id);

    // cleanup
    $s = $d->internalRep;
    $s = str_replace(');$', '$);', $s);
    $s = str_replace(',$ ', '$, ', $s);
    $s = str_replace(';$ ', '$; ', $s);
    $s = str_replace(')$ ', '$) ', $s);
    $s = preg_replace('/\)\$$/', '$)', $s);

    // match the word being defined
    if (!preg_match($REGEX_WORD, $s, $m)) {
      throw new ParseException('Cannot parse word');
    }
    $s = substr($s, strlen($m[0]));

    $baseForm = $m[1];

    // match the part(s) of speech
    $posList = [];
    while (preg_match($REGEX_POS, $s, $m)) {
      $s = substr($s, strlen($m[0]));
      $posList[] = [
        'pos' => $m[1],
        'hyphenation' => $m[3],
        'pronunciation' => $m[5],
      ];
    }

    if (empty($posList)) {
      throw new ParseException('Cannot parse part of speech');
    }

    // match the inflections and inflected forms
    $inflList = [];
    while (preg_match($REGEX_INFL, $s, $m)) {
      $s = substr($s, strlen($m[0]));
      $inflList[]= [
        'inflection' => $m[1],
        'form' => $m[2],
        'hyphenation' => $m[4],
	'comment' => $m[6],
      ];
    }

    if ($s) {
      throw new ParseException('Cannot parse inflection list');
    }

    foreach ($inflList as $rec) {
      if ((strpos($rec['form'], '.') !== false) &&
          ($rec['inflection'] != 'abr.')) {
        throw new ParseException('Inflected form contains a dot');
      }
    }

    // end of parsing, beginning of semantic analysis

    foreach ($posList as $pos) {
      $p = $pos['pos'];
      if (!isset($POS_MAP[$p])) {
        throw new SemanticException("Unknown part of speech [{$p}]");
      }
    }

    $lexems = Model::factory('Lexem')
      ->select('l.*')
      ->distinct()
      ->table_alias('l')
      ->join('EntryDefinition', ['l.entryId', '=', 'ed.entryId'], 'ed')
      ->where('ed.definitionId', $d->id)
      ->find_many();
    $lexemIds = getField($lexems, 'id');

    if (empty($lexems)) {
      throw new SemanticException('No associated lexemes');
    }

    $homonyms = loadHomonyms($lexems, $lexemIds);
    $homonymIds = getField($homonyms, 'id');

    $lexemIdsToSkip = [];

    foreach ($inflList as $infl) {
      $inflectionLists = $inflMap[$infl['inflection']];

      $forms = explode('/', $infl['form']);
      foreach ($forms as $form) {
	$form = trim($form);
	$form = str_replace(['á', 'é', 'í', 'ó', 'ú', 'ắ', 'ấ'],
			    ["'a", "'e", "'i", "'o", "'u", "'ă", "'â"],
			    $form);
	$noAccent = strpos($form, "'") === false;
	$fieldToMatch = $noAccent ? 'formNoAccent' : 'form';

	// for each list in $inflection lists, $infl['form'] must match one of the IDs
	foreach ($inflectionLists as $inflectionIds) {
	  $inflectedForms = Model::factory('InflectedForm')
	    ->where_in('lexemId', $lexemIds)
	    ->where_in('inflectionId', $inflectionIds)
	    ->where($fieldToMatch, $form)
	    ->find_many();
	  if (empty($inflectedForms)) {
	    // look for a homonym lexeme that does generate this form
	    if (!associateHomonyms($d, $fieldToMatch, $form, $lexems, $lexemIds,
				   $homonyms, $homonymIds, $inflectionIds)) {
	      throw new NewFormException(sprintf('[%s] -> [%s]',
						 $baseForm, $infl['form']));
	    }
	  } else {
	    // will not tag lexemes that do NOT generate this form, so mark them
	    foreach ($lexems as $l) {
	      $exists = false;
	      foreach ($inflectedForms as $if) {
		$exists |= ($if->lexemId == $l->id);
	      }
	      if (!$exists) {
		$lexemIdsToSkip[$l->id] = true;
	      }
	    }
	  }
	}
      }
    }

    // save the various bits of information
    foreach ($lexems as $l) {
      if (isset($lexemIdsToSkip[$l->id])) {
	Log::notice("skipping lexem {$l->id} {$l} for {$d->internalRep}");
      } else {

	$tagList = getTagList($posList); 
	foreach ($tagList as $tag) {
	  LexemTag::associate($l->id, $tag->id);
	  Log::info("Tag {$tag->value} on lexem {$l} for definition {$d->internalRep}");
	}

	foreach ($posList as $p) {
	  $pron = trimPronunciation($p['pronunciation']);
	  if ($pron && !$l->pronunciation) {
	    $l->pronunciations = $pron;
	    Log::info("Pronunciation [{$pron}] on lexem {$l} for definition {$d->internalRep}");
	  }

	  $hyph = trimHyphenation($p['hyphenation']);
	  if ($hyph && !$l->hyphenations) {
	    $l->hyphenations = $hyph;
	    Log::info("Hyphenation [{$hyph}] on lexem {$l} for definition {$d->internalRep}");
	  }
	}

	foreach ($inflList as $infl) {
	  // add lexem comments from definition comments
	  $comment = trimComment($infl['comment']);
	  if ($comment) {
	    $l->comment .= "{$comment} [[structurare automată DOR]]\n";
	    Log::info("Comment [{$comment}] on lexem {$l} for definition {$d->internalRep}");
	  }

	  // add a meaning for symbols, only if the lexeme has no meanings yet
	  if ($infl['inflection'] == 'simb.') {
	    $symbol = trimSymbol($infl['form']);

	    $te = TreeEntry::get_by_entryId($l->entryId);
	    if ($te) {
	      $existingMeaning = Meaning::get_by_treeId($te->treeId);
	      if (!$existingMeaning) {
		$m = Model::factory('Meaning')->create();
		$m->parentId = 0;
		$m->displayOrder = 1;
		$m->breadcrumb = '1';
		$m->userId = $MY_USER_ID;
		$m->treeId = $te->treeId;
		$m->internalRep = "simbol: {$symbol}";
		$m->htmlRep = AdminStringUtil::htmlize($m->internalRep, 0);
		$m->save();

    MeaningSource::associate($m->id, $DOR_SOURCE_ID);

		Log::info("Symbol meaning [{$symbol}] on lexem {$l} for definition " .
			  "{$d->internalRep}");
	      }
	    }
	  }
	}

	LexemSource::associate($l->id, $DOR_SOURCE_ID);
	Log::info('Source DOR on lexem %s', $l);

	$l->save();
      }
    }

    $d->structured = 1;
    $d->save();

  } catch (ParseException $e) {
    Log::warning('Parse Exception %s: %s [%s%d]',
                 $e->getMessage(), $d->internalRep, $URL, $d->id);
    $parseErrors++;
  } catch (SemanticException $e) {
    Log::warning('Semantic Exception %s: %s [%s%d]',
                 $e->getMessage(), $d->internalRep, $URL, $d->id);
    $semanticErrors++;
  } catch (NewFormException $e) {
    Log::warning('%s [https://dexonline.ro/definitie-dor/%s/paradigma] %s',
		 $e->getMessage(), $d->lexicon, $d->internalRep);
    $newFormErrors++;
  } catch (Exception $e) {
    Log::warning('Exception: %s: %s [%s%d]',
                 $e->getMessage(), $d->internalRep, $URL, $d->id);
    exit;
  }
  
  if ($i % 100 == 0) {
    Log::info('Processed %d / %d definitions.', $i, count($defs));
  }
}

Log::warning('Processed %d definitions, of which %d parsing / %d semantic / %d new form errors.',
             count($defs), $parseErrors, $semanticErrors, $newFormErrors);

/*************************************************************************/

function getField(&$arr, $field) {
  $result = [];
  foreach ($arr as $obj) {
    $result[] = $obj->$field;
  }
  return $result;
}

function loadHomonyms(&$lexems, &$lexemIds) {
  $forms = getField($lexems, 'formNoAccent');

  return Model::factory('Lexem')
    ->where_in('formNoAccent', $forms)
    ->where_not_in('id', $lexemIds)
    ->find_many();
}

// returns true on success, false on failure
function associateHomonyms($d, $fieldToMatch, $form, &$lexems, &$lexemIds,
			   &$homonyms, &$homonymIds, &$inflectionIds) {
  if (empty($homonyms)) {
    return false;
  }

  $inflectedForms = Model::factory('inflectedForm')
    ->where_in('lexemId', $homonymIds)
    ->where_in('inflectionId', $inflectionIds)
    ->where($fieldToMatch, $form)
    ->find_many();

  if (count($inflectedForms)) {
    foreach ($inflectedForms as $if) {
      $currentSet = [];
      foreach ($lexems as $l) {
        $currentSet[] = (string)$l;
      }
      $currentSet = implode(', ', $currentSet);

      $h = Lexem::get_by_id($if->lexemId);
      EntryDefinition::associate($h->entryId, $d->id);

      $lexems[] = $h;
      $lexemIds[] = $h->id;

      Log::info('Associated homonym %s to [%s], current lexeme set %s',
		$h, $d->internalRep, $currentSet);
    }
    return true;
  } else {
    return false;
  }
}

// returns all tags applicable to all parts of speech, removing duplicates
function getTagList($posList) {
  global $tagMap;

  $result = [];
  $tagIds = [];

  foreach ($posList as $p) {
    $pos = $p['pos'];
    foreach ($tagMap[$pos] as $tag) {
      if (!isset($tagIds[$tag->id])) {
	$result[] = $tag;
	$tagIds[$tag->id] = true;
      }
    }
  }

  return $result;
}

function trimPronunciation($s) {
  $s = str_replace('$', '', $s);
  if (StringUtil::startsWith($s, 'pr. ')) {
    $s = substr($s, 4);
  }
  return $s;
}

function trimHyphenation($s) {
  $s = str_replace('$', '', $s);
  if (StringUtil::startsWith($s, 'mf. ')) {
    $s = substr($s, 4);
  }
  return $s;
}

function trimComment($s) {
  $s = trim($s);
  return $s;
}

function trimSymbol($s) {
  $s = trim($s);
  return $s;
}
