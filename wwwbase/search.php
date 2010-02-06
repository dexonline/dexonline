<?php
require_once("../phplib/util.php");

util_hideEmptyRequestParameters();
$cuv = util_getRequestParameter('cuv');
$lexemId = util_getRequestParameter('lexemId');
$sourceId = util_getRequestIntParameter('source');
$text = util_getRequestIntParameter('text');

$searchType = SEARCH_WORDLIST;
$hasDiacritics = session_user_prefers('FORCE_DIACRITICS');
$exclude_unofficial = session_user_prefers('EXCLUDE_UNOFFICIAL');
$hasRegexp = FALSE;
$isAllDigits = FALSE;
$showParadigm = util_getRequestParameter('showParadigm') || session_user_prefers('SHOW_PARADIGM');
$paradigmLink = $_SERVER['REQUEST_URI'] . (util_getRequestParameter('showParadigm') ? '' : '&showParadigm=1');

if ($cuv) {
  // Keep spaces for full-text searches.
  $cuv = text_cleanupQueryKeepSpaces($cuv, $text);
  smarty_assign('cuv', $cuv);
  $arr = text_analyzeQuery($cuv);
  $hasDiacritics = session_user_prefers('FORCE_DIACRITICS') || $arr[0];
  $hasRegexp = $arr[1];
  $isAllDigits = $arr[2];
  if (!$hasRegexp) {
    $cuv = text_cleanupNonRegexpQuery($cuv);
  }
  smarty_assign('page_title', 'DEX online - Căutare: ' . $cuv);
}

if ($text) {
  $searchType = SEARCH_FULL_TEXT;
  if (lock_exists(LOCK_FULL_TEXT_INDEX)) {
    smarty_assign('lockExists', true);
    $definitions = array();
  } else {
    $words = split(' +', $cuv);
    list($properWords, $stopWords) = text_separateStopWords($words, $hasDiacritics);
    smarty_assign('stopWords', $stopWords);
    $defIds = Definition::searchFullText($properWords, $hasDiacritics);
    smarty_assign('numResults', count($defIds));
    // Show at most 50 definitions;
    $defIds = array_slice($defIds, 0, 500);
    // Load definitions in the given order
    $definitions = array();
    foreach ($defIds as $defId) {
      $definitions[] = Definition::load($defId);
    }
  }
  $searchResults = SearchResult::mapDefinitionArray($definitions);
}

// LexemId search
if ($lexemId) {
  $searchType = SEARCH_LEXEM_ID;
  if (!text_validateAlphabet($lexemId, '0123456789')) {
    $lexemId = '';
  }
  $lexem = Lexem::load($lexemId);
  $definitions = Definition::searchLexemId($lexemId, $exclude_unofficial);
  $searchResults = SearchResult::mapDefinitionArray($definitions);
  smarty_assign('results', $searchResults);
  if ($lexem) {
    $lexems = array($lexem);
    smarty_assign('cuv', $lexem->unaccented);
	if ($definitions) {
		smarty_assign('page_title', 'DEX online - Lexem: ' . $lexem->unaccented);
	}
	else {
		smarty_assign('page_title', 'DEX online - Lexem neoficial: ' . $lexem->unaccented);
		smarty_assign('exclude_unofficial', $exclude_unofficial);
	}
  }
  else {
    $lexems = array();
    smarty_assign('page_title', 'DEX online - Eroare');
  }
  smarty_assign('lexems', $lexems);
}

smarty_assign('src_selected', $sourceId);

// Regular expressions
if ($hasRegexp) {
  $searchType = SEARCH_REGEXP;
  $numResults = Lexem::countRegexpMatches($cuv, $hasDiacritics, $sourceId);
  $lexems = Lexem::searchRegexp($cuv, $hasDiacritics, $sourceId);
  smarty_assign('numResults', $numResults);
  smarty_assign('lexems', $lexems);
}

// Definition.Id search
if ($isAllDigits) {
  $searchType = SEARCH_DEF_ID;
  $def = Definition::searchDefId($cuv);
  $definitions = array();
  if ($def) {
    $definitions[] = $def;
  }
  $searchResults = SearchResult::mapDefinitionArray($definitions);
  smarty_assign('results', $searchResults);
}

// Normal search
if ($searchType == SEARCH_WORDLIST) {
  $lexems = Lexem::searchWordlists($cuv, $hasDiacritics);
  if (count($lexems) == 0) {
    $cuv_old = text_tryOldOrthography($cuv);
    $lexems = Lexem::searchWordlists($cuv_old, $hasDiacritics);
  }
  if (count($lexems) == 0) {
    $searchType = SEARCH_APPROXIMATE;
    $lexems = Lexem::searchApproximate($cuv, $hasDiacritics);
  }

  smarty_assign('lexems', $lexems);
  if ($searchType == SEARCH_LEXEM || $searchType == SEARCH_WORDLIST) {
    // For successful searches, load the definitions and inflections
    $definitions = Definition::loadForLexems($lexems, $sourceId, $cuv, $exclude_unofficial);
    $searchResults = SearchResult::mapDefinitionArray($definitions);
  }
}

if ($searchType == SEARCH_LEXEM || $searchType == SEARCH_WORDLIST ||
    $searchType == SEARCH_LEXEM_ID || $searchType == SEARCH_FULL_TEXT) {
  foreach ($definitions as $def) {
    $def->displayed = $def->displayed + 1;
    $def->saveDisplayedValue();
  }
  
  smarty_assign('results', $searchResults);
  
  // Maps lexems to arrays of wordlists (some lexems may lack inflections)
  // Also compute the text of the link to the paradigm div,
  // which can be 'conjugări', 'declinări' or both
  if (isset($lexems)) {
    $wordListMaps = array();
    $conjugations = false;
    $declensions = false;
    foreach ($lexems as $l) {
      $wordListMaps[] = WordList::loadByLexemIdMapByInflectionId($l->id);
      if ($l->modelType == 'V' || $l->modelType == 'VT') {
        $conjugations = true;
      } else {
        $declensions = true;
      }
    }
    $declensionText = $conjugations ? ($declensions ? 'conjugări / declinări' : 'conjugări') : 'declinări';
    smarty_assign('wordListMaps', $wordListMaps);
    smarty_assign('declensionText', $declensionText);
  }
}

smarty_assign('text', $text);
smarty_assign('searchType', $searchType);
smarty_assign('showParadigm', $showParadigm);
smarty_assign('paradigmLink', $paradigmLink);
smarty_assign('adsense', pref_getAdsense());
smarty_displayCommonPageWithSkin('search.ihtml');

?>
