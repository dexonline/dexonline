<?php

/**
 * Parse definitions from DOR, add LexemSources and mark them as structured
 * when possible.
 *
 * TODO
 * - split inflected forms containing slashes
 * - ignore the "simb." inflection (chemical symbols)
 **/

require_once __DIR__ . '/../phplib/util.php';

$DOR_SOURCE_ID = 38;
$URL = 'https://dexonline.ro/admin/definitionEdit.php?definitionId='; // for edit links
$MAX_ERRORS = 3; // stop at this many errors

// regex parts
$PART_PRON = '( \[.*(pr\.|cit\.).*\])';
$PART_HYPH = '( \(sil\. [^)]+\))';
$PART_COMMENT = '( \([^)]+\))';

$REGEX_WORD = "/^@([^@]+)@\s+/";
$REGEX_POS = "/^([-a-zăâîșț1-3.\/()+ ]+)({$PART_PRON}|{$PART_HYPH})*([,;] |$)/";
$REGEX_INFL = "/^([-a-zăâîșț1-3.() ]+) [$]([-a-zA-ZÅÁăâîșțĂÂÎȘȚáéíóúýắấäüµΩ'., \\/]+)[$]{$PART_HYPH}?{$PART_COMMENT}?([,;] |$)/";

class ParseException extends Exception {
}

class SemanticException extends Exception {
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
  'f. sg.' => ['%feminin, Nominativ-Acuzativ, singular, nearticulat'],
  'f.' => ['%feminin, Nominativ-Acuzativ, singular, nearticulat'],
  'g.-d. art.' => ['%feminin, Genitiv-Dativ, singular, articulat'],
  'g.-d. m. și f.' => ['Pronume, Genitiv-Dativ%masculin', 'Pronume, Genitiv-Dativ%feminin'],
  'g.-d. m.' => ['Pronume, Genitiv-Dativ, singular, masculin'],
  'g.-d. pl. m. și f.' => ['Pronume, Genitiv-Dativ, plural, masculin', 'Pronume, Genitiv-Dativ, plural, feminin'],
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
  'm.m.c.p. 1 sg.' => ['mai mult ca perfect% I, singular'],
  'm.m.c.p. 3 sg.' => ['mai mult ca perfect% a III-a, singular'],
  'neacc.' => ['pronume%'],
  'neg.' => ['%infinitiv prezent'], // negative imperative has the infinitive form
  'part.' => ['%participiu%'],
  'perf. s. 1 sg.' => ['%perfect simplu% I, singular'],
  'perf. s. 3 sg.' => ['%perfect simplu% a III-a, singular'],
  'pl. f.' => ['%feminin%plural%nearticulat%'],
  'pl. m. și f.' => ['%masculin%plural%nearticulat%', '%feminin%plural%nearticulat%'],
  'pl. n. și f.' => ['%feminin%plural%nearticulat%'],
  'pl.' => ['%nominativ-acuzativ, plural, nearticulat'],
  'simb.' => [], // no inflections here, but we'll create a meaning for the symbol
];

// Inflection map definitions
$POS_MAP = [
  'adj. f.' => [],
  'adj. invar.' => [],
  'adj. m. (antepus)' => [],
  'adj. m. (postpus)' => [],
  'adj. m. și f.' => [],
  'adj. m.' => [],
  'adj. n.' => [],
  'adj.' => [],
  'adv.' => [],
  'art. m.' => [],
  'conjcț.' => [],
  'interj.' => [],
  'loc. adj.' => [],
  'loc. adv.' => [],
  'loc. s. f.' => [],
  'num. card. m.' => [],
  'num. card.' => [],
  'num. m.' => [],
  'num. ord.' => [],
  'prep. + adv.' => [],
  'prep. + art./num.' => [],
  'prep. + pron.' => [],
  'prep.' => ['I'],
  'pron. dem. m.' => [],
  'pron. m.' => [],
  'pron. neg.' => [],
  'pron. neh. m.' => [],
  'pron. neh.' => [],
  'pron. pers.' => [],
  's. f' => [],
  's. f. art. + pron.' => [],
  's. f. art.' => [],
  's. f. invar.' => [],
  's. f. pl.' => [],
  's. f.' => [],
  's. m. / s. n.' => [],
  's. m. art.' => [],
  's. m. invar.' => [],
  's. m. pl.' => [],
  's. m. și f.' => [],
  's. m.' => [],
  's. n. / s. m.' => [],
  's. n. art.' => [],
  's. n. invar.' => [],
  's. n. pl.' => [],
  's. n.' => [],
  's. pr. f. art.' => [],
  's. pr. f. pl.' => [],
  's. pr. f.' => [],
  's. pr. m. art.' => [],
  's. pr. m.' => [],
  's. pr. n.' => [],
  's.' => [],
  'vb.' => ['V', 'VT'],
];

$defs = Model::factory('Definition')
  ->select('id')
  ->where('sourceId', $DOR_SOURCE_ID)
  ->where('structured', 0)
  ->where_in('status', [Definition::ST_ACTIVE, Definition::ST_HIDDEN])
  ->order_by_asc('lexicon')
//  ->where_gt('lexicon', 'a')
//  ->offset(20000)
//  ->limit(10)
  ->find_many();
$parseErrors = $semanticErrors = $otherErrors = 0;
$imap = [];

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
        'pronunciation' => $m[2],
        'hyphenation' => $m[4],
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
        'extra' => $m[3],
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

    $possibleLexems = Model::factory('Lexem')
      ->select('l.*')
      ->distinct()
      ->table_alias('l')
      ->join('EntryDefinition', ['l.entryId', '=', 'ed.entryId'], 'ed')
      ->where('ed.definitionId', $d->id)
      ->find_many();

    // Filter out the lexems not matching any acceptable parts of speech
    $lexems = [];
    foreach ($possibleLexems as $l) {
      $found = false;
      foreach ($posList as $pos) {
        if (in_array($l->modelType, $POS_MAP[$pos['pos']])) {
          $found = true;
        }
      }
      if ($found) {
        $lexems[] = $l;
      }
    }

    if (empty($lexems)) {
      $posText = [];
      foreach ($posList as $pos) {
        $posText[] = $pos['pos'];
      }
      throw new Exception(sprintf(
        'No acceptable lexems for [%s] (%s), PoS list %s',
        $baseForm, $l->modelType, implode(', ', $posText)));
    }
    /* foreach ($inflList as $infl) { */
    /*   $if = $infl['inflection']; */
    /*   if (isset($imap[$if])) { */
    /*     $imap[$if]++; */
    /*   } else { */
    /*     $imap[$if] = 1; */
    /*   } */
    /* } */

  } catch (ParseException $e) {
    Log::warning('Parse Exception %s: %s [%s%d]',
                 $e->getMessage(), $d->internalRep, $URL, $d->id);
    $parseErrors++;
  } catch (SemanticException $e) {
    Log::warning('Semantic Exception %s: %s [%s%d]',
                 $e->getMessage(), $d->internalRep, $URL, $d->id);
    $semanticErrors++;
  } catch (Exception $e) {
    Log::error('Exception %s: %s [%s%d]',
               $e->getMessage(), $d->internalRep, $URL, $d->id);
    if (++$otherErrors == $MAX_ERRORS) {
      exit;
    }
  }
  
  if ($i % 1000 == 0) {
    Log::info('Processed %d / %d definitions.', $i, count($defs));
  }
}

/* arsort($imap); */
/* foreach ($imap as $inflection => $c) { */
/*   print("'$inflection' => $c,\n"); */
/* } */

Log::warning('Processed %d definitions, of which %d parsing / %d semantic errors.',
             count($defs), $parseErrors, $semanticErrors);
