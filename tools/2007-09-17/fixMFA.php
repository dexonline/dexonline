<?
require_once("../../phplib/util.php");
ini_set('max_execution_time', '3600');
assert_options(ASSERT_BAIL, 1);
debug_off();

$modelMap = array(
                  1 => array(1, 1),
                  2 => array(3, 1),
                  3 => array(5, 1),
                  4 => array(6, 1),
                  5 => array(8, 1),
                  6 => array(9, 1),
                  7 => array(8, 1),
                  8 => array(10, 1),
                  9 => array(12, 1),
                  10 => array(13, 4),
                  11 => array(14, 6),
                  12 => array(16, 7),
                  13 => array(17, 8),
                  14 => array(17, 8),
                  15 => array(18, 10),
                  16 => array(19, 11),
                  17 => array(20, 12),
                  18 => array(1, 12),
                  19 => array(21, 15),
                  20 => array(22, 16),
                  21 => array(23, 17),
                  22 => array(1, 17),
                  23 => array(24, 18),
                  24 => array(25, 19),
                  25 => array(27, 21),
                  26 => array(27, 12),
                  27 => array(1, 1),
                  28 => array(1, 21),
                  29 => array(1, 0),
                  30 => array(1, 1),
                  31 => array(28, 23),
                  32 => array(29, 23),
                  33 => array(30, 24),
                  34 => array(3, 12),
                  35 => array(31, 12),
                  36 => array(0,  12),
                  37 => array(3, 16),
                  38 => array(3, 17),
                  39 => array(3, 1),
                  40 => array(34, 21),
                  41 => array(0,  21),
                  42 => array(35, 22),
                  43 => array(3, 1),
                  44 => array(5, 1),
                  45 => array(3, 1),
                  46 => array(0, 23),
                  47 => array(0, 24),
                  48 => array(6, 12),
                  51 => array(6, 1),
                  52 => array(9, 1),
                  53 => array(36, 12),
                  54 => array(12, 12),
                  55 => array(12, 1),
                  56 => array(13, 26),
                  57 => array(37, 26),
                  58 => array(14, 27),
                  59 => array(38, 27),
                  60 => array(37, 29),
                  61 => array(38, 0),
                  62 => array(39, 30),
                  63 => array(13, 4),
                  64 => array(14, 6),
                  65 => array(42, 33),
                  66 => array(1, 103),
                  67 => array(12, 151),
                  68 => array(12, 153),
                  69 => array(12, 154),
                  70 => array(12, 39),
                  71 => array(12, 156),
                  72 => array(12, 157),
                  73 => array(12, 158),
                  74 => array(12, 0),
                  75 => array(13, 46),
                  76 => array(14, 47),
                  77 => array(14, 0),
                  78 => array(17, 49),
                  79 => array(13, 76),
                  80 => array(14, 77),
                  81 => array(17, 86),
                  82 => array(17, 88),
                  83 => array(17, 89),
                  84 => array(17, 91),
                  85 => array(1,  0),
                  86 => array(46, 1),
                  87 => array(45, 1),
                  88 => array(51, 104),
                  89 => array(45, 107),
                  90 => array(51, 122),
                  91 => array(46, 109),
                  92 => array(47, 110),
                  93 => array(0, 113),
                  94 => array(0, 0),
                  95 => array(0, 0),
                  96 => array(62, 1),
                  97 => array(63, 1),
                  98 => array(62, 12),
                  99 => array(62, 1),
                  100 => array(63, 17),
                  101 => array(67, 135),
                  102 => array(69, 129),
                  103 => array(69, 142),
                  104 => array(69, 151),
                  105 => array(69, 151),
                  106 => array(0, 1),
                  108 => array(69, 134),
                  109 => array(70, 135),
                  110 => array(72, 135),
                  111 => array(69, 130),
                  112 => array(69, 0),
                  113 => array(69, 0),
                  114 => array(0, 0),
                  115 => array(73, 104),
                  116 => array(73, 104),
                  117 => array(73, 122),
                  118 => array(0, 0),
                  120 => array(78, 129),
                  121 => array(78,  129),
                  122 => array(78, 130),
                  123 => array(0, 131),
                  124 => array(0, 0),
                  125 => array(95, 0),
                  );

$query = "select * from lexems where lexem_model_type in ('A', 'MF') " .
  "order by lexem_neaccentuat";
$dbResult = mysql_query($query);

$fixed = 0;
$prevUnaccented = '';
while (($dbRow = mysql_fetch_assoc($dbResult)) != null) {
  $l = Lexem::createFromDbRow($dbRow);
  if ($prevUnaccented != $l->unaccented) {
    $defs = loadDefinitions($l->id);
    if (count($defs)) {
      $isPart = isParticiple($l);
      $isAdj = isAdjective($defs) || $isPart;
      $isMf = isMfNoun($defs);
      $isN = isNNoun($defs);
      $isM = isMNoun($defs) && !$isMf;
      $femForm = loadFeminineForm($l->id);
      $isF = countDefinitionsByLexicon($femForm);
      $homonyms = Lexem::loadByUnaccented($l->unaccented);

      // Very often, the participle also acts os homonym. This is normal.
      // Report other cases of N because we don't have mapping tables for N.
      if (count($homonyms) == 1 && $isN && $isPart) {
        $isN = false;
      }

      // Report cases which (1) contain a N lexem outside of the above case, OR
      // (2) Do not seem to generate all the M and F forms.
      if ((!$isAdj && !$isMf && !($isM && $isF)) || $isN) {
        //printLexem($homonyms, $isAdj, $isMf, $isM, $isN, $isF);
      } else {
        $fixed += replaceLexems($homonyms, $isAdj, $isMf, $isM, $isN, $isF,
                                $femForm);
      }
    }
  }
  $prevUnaccented = $l->unaccented;
}

print "Fixed $fixed lexems.\n";

/****************************************************************************/

function isAdjective($defs) {
  foreach ($defs as $d) {
    if (preg_match("/adj\\./i", $d->internalRep)) {
      return true;
    }
  }
  return false;
}

function isMfNoun($defs) {
  foreach ($defs as $d) {
    if (preg_match("/s[ .]+m[ .]+şi f\\./i", $d->internalRep) ||
        preg_match("/s[ .]+m[ .]+f\\./i", $d->internalRep) ||
        preg_match("/^@[^@]+@ +s. +[^mnf]/i", $d->internalRep)) {
      return true;
    }
  }
  return false;
}

function isNNoun($defs) {
  foreach ($defs as $d) {
    if (preg_match("/s[ .]+n\\./i", $d->internalRep)) {
      return true;
    }
  }
  return false;
}

function isMNoun($defs) {
  foreach ($defs as $d) {
    if (preg_match("/s[ .]+m\\./i", $d->internalRep)) {
      return true;
    }
  }
  return false;
}

function isFNoun($defs) {
  foreach ($defs as $d) {
    if (preg_match("/s[ .]+f\\./i", $d->internalRep)) {
      return true;
    }
  }
  return false;
}

function isParticiple($lexem) {
  $query = "select count(*) from wordlist " .
    "where wl_neaccentuat = '{$lexem->unaccented}' " .
    "and wl_analyse = 52";
  return db_fetchInteger(mysql_query($query));
}

function loadDefinitions($lexemId) {
  $query = "select Definition.* from Definition, LexemDefinitionMap " .
    "where Definition.Id = LexemDefinitionMap.DefinitionId " .
    "and LexemDefinitionMap.LexemId = $lexemId " .
    "and Status = 0 " .
    "and SourceId in (1,2,3,4,5,17,21)";
    //    "and SourceId not in (7, 12)";
  $dbResult = mysql_query($query);
  return Definition::populateFromDbResult($dbResult);
}

function loadFeminineForm($lexemId) {
  $query = "select * from wordlist where wl_lexem = $lexemId " .
    "and wl_analyse = 33 and wl_variant = 0";
  $dbRow = db_fetchSingleRow(mysql_query($query));
  $wl = WordList::createFromDbRow($dbRow);
  if (!$wl) {
    return '';
  } else {
    return $wl->unaccented;
  }
}

function countDefinitionsByLexicon($lexicon) {
  $query = "select count(*) from Definition where Status = 0 " .
    "and Lexicon = '$lexicon' " .
    "and SourceId in (1,2,3,4,5,17,21)";
  //    "and SourceId not in (8)";
  return db_fetchInteger(mysql_query($query));  
}

function replaceLexems($homonyms, $isAdj, $isMf, $isM, $isN, $isF, $femForm) {
  // Create lists of model numbers for A/MF/M/N/F
  $aList = array();
  $mfList = array();
  $mList = array();
  $nList = array();
  $fList = array();

  foreach ($homonyms as $h) {
    if ($h->modelType == 'A') {
      $aList[] = $h;
    } else if ($h->modelType == 'MF') {
      $mfList[] = $h;
    } else if ($h->modelType == 'M') {
      $mList[] = $h;
    } else if ($h->modelType == 'N') {
      $nList[] = $h;
    } else if ($h->modelType == 'F') {
      $fList[] = $h;
    }
  }

  // Count A and MF as the same thing. Do not change A into MF or viceversa.
  if (((count($aList) + count($mfList)) xor ($isAdj || $isMf)) or
      (count($mList) xor $isM) or
      (count($nList) xor $isN) or
      (count($fList) xor $isF)) {
    printLexem($homonyms, $isAdj, $isMf, $isM, $isN, $isF);
    return 1;
  } else {
    return 0;
  }
}

function printLexem($homonyms, $isAdj, $isMf, $isM, $isN, $isF) {
  print "{$homonyms[0]->unaccented}";
  foreach ($homonyms as $h) {
    print " {$h->modelType}{$h->modelNumber}";
  }
  print ":";
  if ($isAdj) { print " A"; }
  if ($isMf) { print " MF"; }
  if ($isM) { print " M"; }
  if ($isF) { print " F"; }
  if ($isN) { print " N"; }
  print "\n";
}

?>
