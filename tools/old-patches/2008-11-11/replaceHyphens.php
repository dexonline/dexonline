<?php

require_once '../../phplib/util.php';

define("MAX_LEN", 40); // Ignore suffixes after this length
$inflectionsToUse = array('V' => array(54, 52),
                          'VT' => array(54, 52),
                          'F' => array(11),
                          'M' => array(3),
                          'N' => array(19),
                          'A' => array(27, 33, 35),
                          'MF' => array(27, 33, 35),
                          );

$dbResult = mysql_query('select * from Definition where SourceId in (10, 12) and status = ' . ST_ACTIVE . ' order by id desc');

while ($row = mysql_fetch_assoc($dbResult)) {
  $def = Definition::createFromDbRow($row);

  // Choose a lexem to inflect. We have four cases
  // - No inflected lexems
  // - Two or more inflected lexems
  // - We don't know what inflections to use for that model type
  // - All good
  $lexems = Lexem::loadByDefinitionId($def->id);
  $lexemsWithInflections = array();
  foreach ($lexems as $l) {
    if ($l->modelType != 'T') {
      $lexemsWithInflections[] = $l;
    }
  }
  $inflections = array();
  $ambiguousLexems = false;
  $noLexems = false;
  $lexem = null;
  if (count($lexemsWithInflections) == 1) {
    $lexem = $lexemsWithInflections[0];
    if (array_key_exists($lexem->modelType, $inflectionsToUse)) {
      $inflections = $inflectionsToUse[$lexem->modelType];
    }
  } else if (count($lexemsWithInflections) > 1) {
    $ambiguousLexems = true;
  } else {
    $noLexems = true;
  }

  $rep = $def->internalRep;
  $len = mb_strlen($rep);
  $newRep = '';
  $prevC = '';
  $curInflection = 0;
  //print "Examining {$def->internalRep}\n";
  for ($i = 0; $i < $len; $i++) {
    $c = text_getCharAt($rep, $i);
    if (!text_isUnicodeLetter($prevC) && $c == '-' && $i <= MAX_LEN) {
      $j = $i + 1;
      while (text_isUnicodeLetter(text_getCharAt($rep, $j))) {
        $j++;
      }
      $chunk = mb_substr($rep, $i, $j - $i);
      if ($chunk != '-') {
        $suffix = mb_substr($chunk, 1);
        //print "{$def->id} [{$def->lexicon}] $i [$chunk]\n";
        if ($lexem) {
          $matchingForm = null;
          foreach ($inflections as $inflId) {
            $wls = WordList::loadByLexemIdInflectionId($lexem->id, $inflId);
            foreach ($wls as $wl) {
              if (matchesWithAccent($wl->form, $suffix)) {
                $matchingForm = $wl->form;
                //print "Matching [{$wl->form}] to [$chunk]\n";
              }
            }
          }
          if ($matchingForm) {
            $matchingFormImpl = str_replace($GLOBALS['text_explicitAccent'], $GLOBALS['text_accented'], $matchingForm);
            // Convert to uppercase when the suffix itself is uppercase
            if ($suffix == text_unicodeToUpper($suffix)) {
              $matchingFormImpl = text_unicodeToUpper($matchingFormImpl);
            }
            $newRep .= $matchingFormImpl;
          } else {
            $newRep .= $chunk;
            print "***** http://dexonline.ro/search.php?cuv={$lexem->unaccented} *****\n";
            print "{$rep}\n";
            print "  * Nu știu ce să fac cu [{$chunk}] la poziția {$i}, lexem {$lexem->form}, model {$lexem->modelType}{$lexem->modelNumber}{$lexem->restriction}\n";
          }
        } else {
          $newRep .= $chunk;
        }
      } else {
        $newRep .= $chunk;
      }
      $i = $j - 1;
    } else {
      $newRep .= $c;
    }
    $prevC = $c;
  }
  if ($newRep != $rep) {
    //print "Rep: {$rep}\nNew rep: {$newRep}\n";
    $def->internalRep = $newRep;
    $def->htmlRep = text_htmlize($newRep);
    $def->save();
  }
}

/********************************************************/

/**
 * 
 */
function matchesWithAccent($form, $suffix) {
  $suffix = text_unicodeToLower($suffix);
  $suffixExpl = str_replace($GLOBALS['text_accented'], $GLOBALS['text_explicitAccent'], $suffix);
  $formHasAccent = (strstr($form, "'") !== false);
  $suffixHasAccent = (strstr($suffixExpl, "'") !== false);
  if ($formHasAccent && $suffixHasAccent) {
    $formImpl = str_replace($GLOBALS['text_explicitAccent'], $GLOBALS['text_accented'], $form);
    return text_endsWith($formImpl, $suffix);
  } else if ($formHasAccent && !$suffixHasAccent) {
    $formNoAccent = str_replace("'", "", $form);
    return text_endsWith($formNoAccent, $suffix);
  } else if (!$formHasAccent && $suffixHasAccent) {
    $suffixNoAccent = str_replace("'", "", $suffixExpl);
    return text_endsWith($form, $suffixNoAccent);
  } else { // No accents
    return text_endsWith($form, $suffix);
  }
}

?>
