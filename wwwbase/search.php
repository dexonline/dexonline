<?php
require_once("../phplib/util.php");
require_once("../phplib/lexemSources.php"); 

$cuv = util_getRequestParameter('cuv');
$lexemId = util_getRequestParameter('lexemId');
$defId = util_getRequestParameter('defId');
$sourceUrlName = util_getRequestParameter('source');
$text = util_getRequestIntParameter('text');
$showParadigm = util_getRequestParameter('showParadigm');

if ($cuv) {
  $cuv = text_cleanupQuery($cuv);
}

util_redirectToFriendlyUrl($cuv, $sourceUrlName, $text, $showParadigm);

$searchType = SEARCH_INFLECTED;
$hasDiacritics = session_user_prefers('FORCE_DIACRITICS');
$exclude_unofficial = session_user_prefers('EXCLUDE_UNOFFICIAL');
$hasRegexp = FALSE;
$isAllDigits = FALSE;
$showParadigm = $showParadigm || session_user_prefers('SHOW_PARADIGM');
$paradigmLink = $_SERVER['REQUEST_URI'] . ($showParadigm ? '' : '/paradigma');
$source = $sourceUrlName ? Source::get("urlName='$sourceUrlName'") : null;
$sourceId = $source ? $source->id : null;

if ($cuv) {
  smarty_assign('cuv', $cuv);
  $arr = text_analyzeQuery($cuv);
  $hasDiacritics = session_user_prefers('FORCE_DIACRITICS') || $arr[0];
  $hasRegexp = $arr[1];
  $isAllDigits = $arr[2];
  smarty_assign('page_title', "Definitie: {$cuv}");
  smarty_assign('page_description', "Definiții, sinonime, antonime, expresii, conjugări și declinări pentru {$cuv}");
}

if ($isAllDigits) {
  $d = Definition::get("id = {$cuv}");
  if ($d) {
    util_redirect(util_getWwwRoot() . "definitie/{$d->lexicon}/{$d->id}");
  }
}

if ($text) {
  $searchType = SEARCH_FULL_TEXT;
  if (lock_exists(LOCK_FULL_TEXT_INDEX)) {
    smarty_assign('lockExists', true);
    $definitions = array();
  } else {
    $words = preg_split('/ +/', $cuv);
    list($properWords, $stopWords) = text_separateStopWords($words, $hasDiacritics);
    smarty_assign('stopWords', $stopWords);
    $defIds = Definition::searchFullText($properWords, $hasDiacritics);
    smarty_assign('numResults', count($defIds));
    // Show at most 50 definitions;
    $defIds = array_slice($defIds, 0, 500);
    // Load definitions in the given order
    $definitions = array();
    foreach ($defIds as $id) {
      $definitions[] = Definition::get("id = {$id}");
    }
    if (!count($defIds)) {
      session_setFlash('Nicio definiție nu conține toate cuvintele căutate.');
    }
  }
  $searchResults = SearchResult::mapDefinitionArray($definitions);
}

// LexemId search
if ($lexemId) {
  // We don't really use $cuv here
  $searchType = SEARCH_LEXEM_ID;
  smarty_assign('lexemId', $lexemId);
  if (!text_validateAlphabet($lexemId, '0123456789')) {
    $lexemId = '';
  }
  $lexem = Lexem::get("id = {$lexemId}");
  $definitions = Definition::searchLexemId($lexemId, $exclude_unofficial);
  $searchResults = SearchResult::mapDefinitionArray($definitions);
  smarty_assign('results', $searchResults);
  if ($lexem) {
    $lexems = array($lexem);
    smarty_assign('cuv', $lexem->formNoAccent);
    if ($definitions) {
      smarty_assign('page_title', "Lexem: {$lexem->formNoAccent}");
    } else {
      smarty_assign('page_title', "Lexem neoficial: {$lexem->formNoAccent}");
      smarty_assign('exclude_unofficial', $exclude_unofficial);
    }
  } else {
    $lexems = array();
    smarty_assign('page_title', "Eroare");
    session_setFlash("Nu există niciun lexem cu ID-ul căutat.");
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
  if (!$numResults) {
    session_setFlash("Niciun rezultat pentru {$cuv}.");
  }
}

// Definition.id search
if ($defId) {
  smarty_assign('defId', $defId);
  $searchType = SEARCH_DEF_ID;
  $def = Definition::get("id = '$defId' and status = 0"); 
  $definitions = array();
  if ($def) {
    $definitions[] = $def;
  } else {
    session_setFlash("Nu există nicio definiție cu ID-ul {$defId}.");
  }
  $searchResults = SearchResult::mapDefinitionArray($definitions);
  smarty_assign('results', $searchResults);
}

// Normal search
if ($searchType == SEARCH_INFLECTED) {
  $lexems = Lexem::searchInflectedForms($cuv, $hasDiacritics);
  if (count($lexems) == 0) {
    $cuv_old = text_tryOldOrthography($cuv);
    $lexems = Lexem::searchInflectedForms($cuv_old, $hasDiacritics);
  }
  if (count($lexems) == 0) {
    $searchType = SEARCH_MULTIWORD;
    $words = preg_split('/[ .-]+/', $cuv);
    if (count($words) > 1) {
      $ignoredWords = array_slice($words, 5);
      $words = array_slice($words, 0, 5);
      $definitions = Definition::searchMultipleWords($words, $hasDiacritics, $sourceId, $exclude_unofficial);
      smarty_assign('ignoredWords', $ignoredWords);
    }
  }
  if (count($lexems) == 0 && empty($definitions)) {
    $searchType = SEARCH_APPROXIMATE;
    $lexems = Lexem::searchApproximate($cuv, $hasDiacritics);
    if (count($lexems) == 1) {
      session_setFlash("Ați fost redirecționat automat la forma „{$lexems[0]->formNoAccent}”.");
    } else if (!count($lexems)) {
      session_setFlash("Niciun rezultat relevant pentru „{$cuv}”.");
    }
  }
  if (count($lexems) == 1 && $cuv != $lexems[0]->formNoAccent) {
    // Convenience redirect when there is only one correct form. We want all pages to be canonical
    $sourcePart = $source ? "-{$source->urlName}" : '';
    util_redirect(util_getWwwRoot() . "definitie{$sourcePart}/{$lexems[0]->formNoAccent}");
  }

  smarty_assign('lexems', $lexems);
  if ($searchType == SEARCH_INFLECTED) {
    // For successful searches, load the definitions and inflections
    $definitions = Definition::loadForLexems($lexems, $sourceId, $cuv, $exclude_unofficial);
  }

  if (isset($definitions)) {
    $searchResults = SearchResult::mapDefinitionArray($definitions);
  }
}

if ($searchType == SEARCH_INFLECTED || $searchType == SEARCH_LEXEM_ID || $searchType == SEARCH_FULL_TEXT || $searchType == SEARCH_MULTIWORD) {
  Definition::incrementDisplayCount($definitions);
  smarty_assign('results', $searchResults);
 
  // Maps lexems to arrays of inflected forms (some lexems may lack inflections)
  // Also compute the text of the link to the paradigm div,
  // which can be 'conjugări', 'declinări' or both
  if (!empty($lexems)) {
    $ifMaps = array();
    $conjugations = false;
    $declensions = false;
    foreach ($lexems as $l) {
	  if ($showParadigm)
        $ifMaps[] = InflectedForm::loadByLexemIdMapByInflectionId($l->id);
      if ($l->modelType == 'V' || $l->modelType == 'VT') {
        $conjugations = true;
      } else {
        $declensions = true;
      }
    }
    $declensionText = $conjugations ? ($declensions ? 'conjugări / declinări' : 'conjugări') : 'declinări';

    if ($showParadigm) {
      smarty_assign('ifMaps', $ifMaps);
    }
    smarty_assign('declensionText', $declensionText);

	$sourceNamesArr = array();
	foreach($lexems as $l) {
	  $sourceNamesArr[] = getNamesOfSources($l->source);
	}

	smarty_assign('sourceNamesArr', $sourceNamesArr);
  }
}

$adsModules = pref_getServerPreference('adsModulesH');
if ($adsModules) {
  require_once util_getRootPath() . 'phplib/ads/adsModule.php';
  foreach ($adsModules as $adsModule) {
    require_once util_getRootPath() . "phplib/ads/{$adsModule}/{$adsModule}AdsModule.php";
    $className = ucfirst($adsModule) . 'AdsModule';
    $module = new $className;
    $result = $module->run(empty($lexems) ? null : $lexems, empty($definitions) ? null : $definitions);
    if ($result) {
      smarty_assign('adsProvider', $adsModule);
      smarty_assign('adsProviderParams', $result);
      break;
    }
  }
}

smarty_assign('text', $text);
smarty_assign('searchType', $searchType);
smarty_assign('showParadigm', $showParadigm);
smarty_assign('paradigmLink', $paradigmLink);
smarty_assign('advancedSearch', $text || $sourceId);
smarty_displayCommonPageWithSkin('search.ihtml');

?>
