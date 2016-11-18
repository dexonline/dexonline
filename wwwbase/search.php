<?php

require_once('../phplib/util.php');
require_once('../phplib/ads/adsModule.php');

define('SEARCH_REGEXP', 0);
define('SEARCH_MULTIWORD', 1);
define('SEARCH_INFLECTED', 2);
define('SEARCH_APPROXIMATE', 3);
define('SEARCH_DEF_ID', 4);
define('SEARCH_LEXEM_ID', 5);
define('SEARCH_FULL_TEXT', 6);

define('LIMIT_FULLTEXT_DISPLAY', Config::get('limits.limitFulltextSearch', 500));

define('HEADER_404', 'HTTP/1.0 404 Not Found');

$cuv = Request::get('cuv');
$lexemId = Request::get('lexemId');
$defId = Request::get('defId');
$sourceUrlName = Request::get('source');
$text = Request::has('text');
$showParadigm = Request::get('showParadigm');
$xml = Request::get('xml');
$all = Request::get('all');

$redirect = session_get('redirect');
$redirectFrom = session_getWithDefault('init_word', '');
session_unsetVariable('redirect');
session_unsetVariable('init_word');

if ($cuv && !$redirect) {
  $cuv = StringUtil::cleanupQuery($cuv);
}

util_redirectToFriendlyUrl($cuv, $lexemId, $sourceUrlName, $text, $showParadigm, $xml, $all);

$paradigmLink = $_SERVER['REQUEST_URI'] . ($showParadigm ? '' : '/paradigma');

$searchType = SEARCH_INFLECTED;
$hasDiacritics = session_user_prefers(Preferences::FORCE_DIACRITICS);
$oldOrthography = session_user_prefers(Preferences::OLD_ORTHOGRAPHY);
$exclude_unofficial = session_user_prefers(Preferences::EXCLUDE_UNOFFICIAL);
$hasRegexp = FALSE;
$isAllDigits = FALSE;
$showParadigm = $showParadigm || session_user_prefers(Preferences::SHOW_PARADIGM);
$all = $all || $showParadigm;
SmartyWrap::assign('allDefinitions', $all);

$source = $sourceUrlName ? Source::get_by_urlName($sourceUrlName) : null;
$sourceId = $source ? $source->id : null;

if ($cuv) {
  SmartyWrap::assign('cuv', $cuv);
  $arr = StringUtil::analyzeQuery($cuv);
  $hasDiacritics = $hasDiacritics || $arr[0];
  $hasRegexp = $arr[1];
  $isAllDigits = $arr[2];
}

if ($isAllDigits) {
  $d = Definition::getByIdNotHidden($cuv);
  if ($d) {
    util_redirect(util_getWwwRoot() . "definitie/{$d->lexicon}/{$d->id}" . ($xml ? '/xml' : ''));
  }
}

// Definition.id search
if ($defId) {
  $searchType = SEARCH_DEF_ID;
  $statuses = util_isModerator(PRIV_VIEW_HIDDEN)
            ? [Definition::ST_ACTIVE, Definition::ST_HIDDEN]
            : [Definition::ST_ACTIVE];
  $definitions = Model::factory('Definition')
               ->where('id', $defId)
               ->where_in('status', $statuses)
               ->find_many();
  if (empty($definitions)) {
    header(HEADER_404);
  }
  $searchResults = SearchResult::mapDefinitionArray($definitions);
  SmartyWrap::assign('results', $searchResults);
}

if ($text) {
  $searchType = SEARCH_FULL_TEXT;
  $definitions = [];

  if (Lock::exists(LOCK_FULL_TEXT_INDEX)) {
    SmartyWrap::assign('fullTextLock', true);
  } else {
    $words = preg_split('/ +/', $cuv);
    list($defIds, $stopWords) = Definition::searchFullText($words, $hasDiacritics, $sourceId);
    SmartyWrap::assign('numResults', count($defIds));
    SmartyWrap::assign('stopWords', $stopWords);
    // Show at most 500 definitions;
    $defIds = array_slice($defIds, 0, LIMIT_FULLTEXT_DISPLAY);
    // Load definitions in the given order
    foreach ($defIds as $id) {
      $definitions[] = Definition::get_by_id($id);
    }
    if (empty($definitions)) {
      header(HEADER_404);
    }
    Definition::highlight($words, $definitions);
  }
  $searchResults = SearchResult::mapDefinitionArray($definitions);
}

// LexemId search
if ($lexemId) {
  // We don't really use $cuv here
  $searchType = SEARCH_LEXEM_ID;
  SmartyWrap::assign('lexemId', $lexemId);
  if (!StringUtil::validateAlphabet($lexemId, '0123456789')) {
    $lexemId = '';
  }
  $lexem = Lexem::get_by_id($lexemId);
  $definitions = Definition::searchLexem($lexem, $exclude_unofficial);
  $searchResults = SearchResult::mapDefinitionArray($definitions);
  SmartyWrap::assign('results', $searchResults);
  if ($lexem) {
    $lexems = array($lexem);
    SmartyWrap::assign('cuv', $lexem->formNoAccent);
    if (empty($definitions)) {
      SmartyWrap::assign('exclude_unofficial', $exclude_unofficial);
    }
  } else {
    $lexems = array();
    FlashMessage::add("Nu există niciun lexem cu ID-ul căutat.");
    header(HEADER_404);
  }
  SmartyWrap::assign('lexems', $lexems);
}

SmartyWrap::assign('sourceId', $sourceId);

// Regular expressions
if ($hasRegexp) {
  $searchType = SEARCH_REGEXP;
  $lexems = Lexem::searchRegexp($cuv, $hasDiacritics, $sourceId, true);
  $numResults = count($lexems);
  SmartyWrap::assign('numResults', $numResults);
  SmartyWrap::assign('lexems', $lexems);
  if (!$numResults) {
    FlashMessage::add("Niciun rezultat pentru {$cuv}.");
    header(HEADER_404);
  }
}

// If no search type requested so far, then normal search
if ($searchType == SEARCH_INFLECTED) {
  $lexems = Lexem::searchInflectedForms($cuv, $hasDiacritics, $oldOrthography);

  if (count($lexems) == 0) {
    $searchType = SEARCH_MULTIWORD;
    $words = preg_split('/[ .-]+/', $cuv);
    if (count($words) > 1) {
      $ignoredWords = array_slice($words, 5);
      $words = array_slice($words, 0, 5);
      $definitions = Definition::searchMultipleWords($words, $hasDiacritics, $oldOrthography,
                                                     $sourceId, $exclude_unofficial);
      SmartyWrap::assign('ignoredWords', $ignoredWords);
    }
  }

  if (count($lexems) == 0 && empty($definitions)) {
    $searchType = SEARCH_APPROXIMATE;
    $lexems = Lexem::searchApproximate($cuv, $hasDiacritics, true);
    if (count($lexems) == 1) {
      FlashMessage::add("Ați fost redirecționat automat la forma „{$lexems[0]->formNoAccent}”.");
    } else {
      if (!count($lexems)) {
        FlashMessage::add("Niciun rezultat relevant pentru „{$cuv}”.");
      }
      header(HEADER_404);
    }
  }

  if (count($lexems) == 1 && $cuv != $lexems[0]->formNoAccent) {
    // Convenience redirect when there is only one correct form. We want all pages to be canonical
    $sourcePart = $source ? "-{$source->urlName}" : '';
    session_setVariable('redirect', true);
    session_setVariable('init_word', $cuv);
    util_redirect(util_getWwwRoot() . "definitie{$sourcePart}/{$lexems[0]->formNoAccent}" . ($xml ? '/xml' : ''));
  }

  SmartyWrap::assign('lexems', $lexems);
  if ($searchType == SEARCH_INFLECTED) {
    // For successful searches, load the definitions, inflections and linguistic articles
    $definitions = Definition::loadForLexems($lexems, $sourceId, $cuv, $exclude_unofficial);
    SmartyWrap::assign('wikiArticles', WikiArticle::loadForLexems($lexems));
  }

  if (empty($definitions)) {
    $definitions = [];
  }

  $searchResults = SearchResult::mapDefinitionArray($definitions);
}

$conjugations = NULL;
$declensions = NULL;
if ($searchType == SEARCH_INFLECTED || $searchType == SEARCH_LEXEM_ID || $searchType == SEARCH_FULL_TEXT || $searchType == SEARCH_MULTIWORD) {
  // Filter out hidden definitions
  $hiddenSources = [];
  SearchResult::filterHidden($searchResults, $hiddenSources);

  // Only once we've filtered them can we count the total and take a slice
  if ($searchType == SEARCH_INFLECTED || $searchType == SEARCH_MULTIWORD) {
    $totalDefinitionsCount = count($searchResults);
    if (!$all && ($totalDefinitionsCount > PREVIEW_LIMIT)) {
      $searchResults = array_slice($searchResults, 0, PREVIEW_LIMIT);
      SmartyWrap::assign('totalDefinitionsCount', $totalDefinitionsCount);
    }
  }
  
  if (Config::get('global.aprilFoolsDay')) {
    foreach ($searchResults as $sr) {
      $sr->definition->htmlRep = StringUtil::iNoGrammer($sr->definition->htmlRep);
    }
  }

  SmartyWrap::assign('results', $searchResults);
  SmartyWrap::assign('hiddenSources', $hiddenSources);

  // Maps lexems to arrays of inflected forms (some lexems may lack inflections)
  // Also compute the text of the link to the paradigm div,
  // which can be 'conjugări', 'declinări' or both
  if (!empty($lexems)) {
    $conjugations = false;
    $declensions = false;
    foreach ($lexems as $l) {
      $isVerb = ($l->modelType == 'V') || ($l->modelType == 'VT');
      $conjugations |= $isVerb;
      $declensions |= !$isVerb;
    }
    $declensionText = $conjugations ? ($declensions ? 'conjugări / declinări' : 'conjugări') : 'declinări';

    $hasUnrecommendedForms = false;
    foreach ($lexems as $l) {
      $l->getModelType();
      $l->getSourceNames();
      $map = $l->loadInflectedFormMap();
      $l->addLocInfo();
      foreach ($map as $ifs) {
        foreach ($ifs as $if) {
          $hasUnrecommendedForms |= !$if->recommended;
        }
      }
    }
    SmartyWrap::assign('hasUnrecommendedForms', $hasUnrecommendedForms);

    SmartyWrap::assign('declensionText', $declensionText);
  }
}

// Collect source list to display in meta tags
$sourceList = array();
if (isset($searchResults)) {
  foreach ($searchResults as $row) {
      if (!in_array($row->source->shortName, $sourceList)) {
        $sourceList[] = $row->source->shortName;
      }
  }
}
SmartyWrap::assign('sourceList', $sourceList);

// META tags - TODO move in a dedicated file
if ($cuv) {
  $pageDescription = "Dicționar dexonline. Definiții";
  if (in_array('Sinonime', $sourceList)) {
    $pageDescription .= ', sinonime';
  }
  if (in_array('Antonime', $sourceList)) {
    $pageDescription .= ', antonime';
  }
  if(!is_null($conjugations)) {
    $pageDescription .= ', conjugări';
  }
  if (!is_null($declensions)) {
    $pageDescription .= ', declinări';
  }
  if (!is_null($conjugations) || !is_null($declensions)) {
    $pageDescription .= ', paradigme';
  }
  $pageDescription .= " pentru {$cuv}";

  if (count($sourceList)) {
    $pageDescription .= " din dicționarele: " . implode(", ", $sourceList);
  }

  SmartyWrap::assign('pageDescription', $pageDescription);
}

// Ads
AdsModule::runAllModules(empty($lexems) ? null : $lexems, empty($definitions) ? null : $definitions);

SmartyWrap::assign('text', $text);
SmartyWrap::assign('searchType', $searchType);
SmartyWrap::assign('showParadigm', $showParadigm);
SmartyWrap::assign('locParadigm', session_user_prefers(Preferences::LOC_PARADIGM));
SmartyWrap::assign('paradigmLink', $paradigmLink);
SmartyWrap::assign('advancedSearch', $text || $sourceId);

/* Gallery */
$images = empty($lexems) ? [] : Visual::loadAllForLexems($lexems);
SmartyWrap::assign('images', $images);
if (count($images)) {
  SmartyWrap::addCss('gallery');
  SmartyWrap::addJs('gallery', 'jcanvas');
}

if (!$xml) {
  SmartyWrap::addCss('paradigm');
  SmartyWrap::display('search.tpl');

} else {
  header('Content-type: text/xml');
  SmartyWrap::displayWithoutSkin('xml/search.tpl');
}

if (Config::get('search-log.enabled')) {
  $logDefinitions = isset($definitions) ? $definitions : array();
  $log = new SearchLog($cuv, $redirectFrom, $searchType, $redirect, $logDefinitions);
  $log->logData();
}

?>
