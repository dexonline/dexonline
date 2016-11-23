<?php

require_once('../phplib/util.php');
require_once('../phplib/ads/adsModule.php');

define('SEARCH_REGEXP', 0);
define('SEARCH_MULTIWORD', 1);
define('SEARCH_INFLECTED', 2);
define('SEARCH_APPROXIMATE', 3);
define('SEARCH_DEF_ID', 4);
define('SEARCH_ENTRY_ID', 5);
define('SEARCH_FULL_TEXT', 6);
define('SEARCH_LEXEM_ID', 7);

define('LIMIT_FULLTEXT_DISPLAY', Config::get('limits.limitFulltextSearch', 500));
define('PREVIEW_LIMIT', 20); // how many definitions to show by default

// categories: whether to show the official / specialized / unofficial category headers
// defLimit: how many definitions to display (null = not relevant)
// entryList: whether to print a list of matching entries
// paradigm: whether to display the paradigm for $entries
$SEARCH_PARAMS = [
  SEARCH_REGEXP => [
    'categories' => false,
    'defLimit' => null,
    'entryList' => false,
    'paradigm' => false,
    'trees' => false,
  ],
  SEARCH_MULTIWORD => [
    'categories' => false,
    'defLimit' => PREVIEW_LIMIT,
    'entryList' => false,
    'paradigm' => false,
    'trees' => false,
  ],
  SEARCH_INFLECTED => [
    'categories' => true,
    'defLimit' => PREVIEW_LIMIT,
    'entryList' => false,
    'paradigm' => true,
    'trees' => true,
  ],
  SEARCH_APPROXIMATE => [
    'categories' => false,
    'defLimit' => null,
    'entryList' => true,
    'paradigm' => false,
    'trees' => false,
  ],
  SEARCH_DEF_ID => [
    'categories' => true,
    'defLimit' => null,
    'entryList' => false,
    'paradigm' => false,
    'trees' => false,
  ],
  SEARCH_ENTRY_ID => [
    'categories' => true,
    'defLimit' => null,
    'entryList' => false,
    'paradigm' => true,
    'trees' => true,
  ],
  SEARCH_FULL_TEXT => [
    'categories' => false,
    'defLimit' => null, // there is a limit, but we handle it separately for memory reasons
    'entryList' => false,
    'paradigm' => false,
    'trees' => false,
  ],
];

$cuv = Request::get('cuv');
$entryId = Request::get('entryId');
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

util_redirectToFriendlyUrl($cuv, $entryId, $lexemId, $sourceUrlName, $text, $showParadigm,
                           $xml, $all);

$paradigmLink = $_SERVER['REQUEST_URI'] . ($showParadigm ? '' : '/paradigma');

$searchType = SEARCH_INFLECTED;
$hasDiacritics = session_user_prefers(Preferences::FORCE_DIACRITICS);
$oldOrthography = session_user_prefers(Preferences::OLD_ORTHOGRAPHY);
$hasRegexp = FALSE;
$isAllDigits = FALSE;
$showParadigm = $showParadigm || session_user_prefers(Preferences::SHOW_PARADIGM);
$all = $all || $showParadigm;

$source = $sourceUrlName ? Source::get_by_urlName($sourceUrlName) : null;
$sourceId = $source ? $source->id : null;

if ($cuv) {
  SmartyWrap::assign('cuv', $cuv);
  $arr = StringUtil::analyzeQuery($cuv);
  $hasDiacritics = $hasDiacritics || $arr[0];
  $hasRegexp = $arr[1];
  $isAllDigits = $arr[2];
}

$definitions = [];
$entries = [];
$lexems = [];
$extra = [];

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
}

// Lexem.id search
if ($lexemId) {
  $searchType = SEARCH_LEXEM_ID;
  $l = Lexem::get_by_id($lexemId);
  if (!$l || empty($l->getEntries())) {
    util_redirect(util_getWwwRoot());
  }
  $e = $l->getEntries()[0];
  util_redirect(sprintf('%sintrare/%s/%s', util_getWwwRoot(), $e->getShortDescription(), $e->id));
}

// Full-text search
if ($text) {
  $searchType = SEARCH_FULL_TEXT;

  if (Lock::exists(LOCK_FULL_TEXT_INDEX)) {
    $extra['fullTextLock'] = true;
  } else {
    $words = preg_split('/ +/', $cuv);
    list($defIds, $stopWords) = Definition::searchFullText($words, $hasDiacritics, $sourceId);

    // enforce the limit before even loading the definitions to save memory
    $extra['numDefinitions'] = count($defIds);
    $extra['stopWords'] = $stopWords;
    $defIds = array_slice($defIds, 0, LIMIT_FULLTEXT_DISPLAY);

    // load definitions in the given order
    foreach ($defIds as $id) {
      $definitions[] = Definition::get_by_id($id);
    }

    // For single-word queries, just order the definitions by lexicon.
    if (count($words) - count($extra['stopWords']) == 1) {
      usort($definitions, function($a, $b) {
        return strcoll($a->lexicon, $b->lexicon) > 0;
      });
    }

    if (!count($defIds)) {
      FlashMessage::add('Nicio definiție nu conține toate cuvintele căutate.');
    }
    Definition::highlight($words, $definitions);
  }
}

// Search by entry ID
if ($entryId) {
  // TODO obey sourceId
  $searchType = SEARCH_ENTRY_ID;
  $entry = Entry::get_by_id($entryId);
  if ($entry) {
    $entries = [$entry];
    SmartyWrap::assign('cuv', $entry->getShortDescription());
    $definitions = Definition::searchEntry($entry);
  }
}

// Regular expression search
// Count all the results, but load at most 1,000
if ($hasRegexp) {
  $searchType = SEARCH_REGEXP;
  $extra['numLexems'] = Lexem::searchRegexp($cuv, $hasDiacritics, $sourceId, true);
  $lexems = Lexem::searchRegexp($cuv, $hasDiacritics, $sourceId);
}

// If no search type requested so far, then normal search
if ($searchType == SEARCH_INFLECTED) {
  $entries = Entry::searchInflectedForms($cuv, $hasDiacritics, $oldOrthography);

  // successful search
  if (count($entries)) {
    $definitions = Definition::loadForEntries($entries, $sourceId, $cuv);
    SmartyWrap::assign('wikiArticles', WikiArticle::loadForEntries($entries));
  }

  // fallback to multiword search
  if (empty($entries) && preg_match('/[- .]/', $cuv)) {
    $searchType = SEARCH_MULTIWORD;
    $words = preg_split('/[- .]+/', $cuv);
    $extra['ignoredWords'] = array_slice($words, 5);
    $words = array_slice($words, 0, 5);
    $definitions = Definition::searchMultipleWords(
      $words, $hasDiacritics, $oldOrthography, $sourceId);
  }

  // fallback to approximate search
  if (empty($entries) && empty($definitions)) {
    $searchType = SEARCH_APPROXIMATE;
    $entries = Lexem::searchApproximate($cuv, $hasDiacritics, true);
    if (count($entries) == 1) {
      FlashMessage::add("Ați fost redirecționat automat la forma „{$entries[0]->description}”.");
    }
  }

  // Convenience redirect when there is only one correct form. We want all pages to be canonical.
  if ((count($entries) == 1) && ($cuv != $entries[0]->getShortDescription())) {
    $sourcePart = $source ? "-{$source->urlName}" : '';
    session_setVariable('redirect', true);
    session_setVariable('init_word', $cuv);
    util_redirect(util_getWwwRoot() .
                  "definitie{$sourcePart}/{$entries[0]->getShortDescription()}");
  }
}

$results = SearchResult::mapDefinitionArray($definitions);
$structuredResults = [];

// Filter out structured definitions if we are displaying trees
if ($SEARCH_PARAMS[$searchType]['trees']) {
  foreach ($results as $i => $sr) {
    if ($sr->definition->structured) {
      $structuredResults[] = $sr;
      unset($results[$i]);
    }
  }
}

// Filter out hidden definitions
list($extra['unofficialHidden'], $extra['sourcesHidden'])
  = SearchResult::filter($results);

// Don't save extra information for structured definitions.
// If there are none left after filtering, then we'll hide the tab altogether.
SearchResult::filter($structuredResults);

// Keep only a maximum number of definitions
$defLimit = $SEARCH_PARAMS[$searchType]['defLimit'];
if ($defLimit) {
  $extra['numDefinitions'] = count($results);
  if (!$all) {
    $results = array_slice($results, 0, $defLimit);
  }
}
  
if (empty($entries) && empty($lexems) && empty($results)) {
  header('HTTP/1.0 404 Not Found');
}

// Collect meaning trees
if ($SEARCH_PARAMS[$searchType]['trees']) {
  $hasTrees = false;
  foreach ($entries as $e) {
    if (in_array($e->structStatus, [Entry::STRUCT_STATUS_DONE, Entry::STRUCT_STATUS_UNDER_REVIEW])) {
      foreach ($e->getTrees() as $t) {
        if (($t->status == Tree::ST_VISIBLE) &&
            count($t->getMeanings())) {
          $hasTrees = true;
        }
      }
    }
  }

  $extra['hasTrees'] = $hasTrees;
  if ($hasTrees) {
    SmartyWrap::addCss('meaningTree');
  }
}

// Collect inflected forms
$conjugations = null;
$declensions = null;
if ($SEARCH_PARAMS[$searchType]['paradigm']) {

  // Compute the text of the link to the paradigm div
  $conjugations = false;
  $declensions = false;
  foreach ($entries as $e) {
    foreach ($e->getLexems() as $l) {
      $isVerb = ($l->modelType == 'V') || ($l->modelType == 'VT');
      $conjugations |= $isVerb;
      $declensions |= !$isVerb;
    }
  }
  $declensionText = $conjugations
                  ? ($declensions ? 'conjugări / declinări' : 'conjugări')
                  : 'declinări';
  SmartyWrap::assign('declensionText', $declensionText);

  // Check if any of the inflected forms are unrecommended
  $hasUnrecommendedForms = false;
  foreach ($entries as $e) {
    foreach ($e->getLexems() as $l) {
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
  }
  SmartyWrap::assign('hasUnrecommendedForms', $hasUnrecommendedForms);
}

// Collect source list to display in meta tags
$sourceList = [];
foreach ($results as $row) {
  $sourceList[$row->source->shortName] = true;
}
foreach ($structuredResults as $row) {
  $sourceList[$row->source->shortName] = true;
}
$sourceList = array_keys($sourceList);
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

// Gallery images
$images = empty($entries) ? [] : Visual::loadAllForEntries($entries);
SmartyWrap::assign('images', $images);
if (count($images)) {
  SmartyWrap::addCss('gallery');
  SmartyWrap::addJs('gallery', 'jcanvas');
}

SmartyWrap::assign('entries', $entries);
SmartyWrap::assign('lexems', $lexems);
SmartyWrap::assign('results', $results);
SmartyWrap::assign('structuredResults', $structuredResults);
SmartyWrap::assign('extra', $extra);
SmartyWrap::assign('text', $text);
SmartyWrap::assign('searchType', $searchType);
SmartyWrap::assign('searchParams', $SEARCH_PARAMS[$searchType]);
SmartyWrap::assign('sourceId', $sourceId);
SmartyWrap::assign('showParadigm', $showParadigm);
SmartyWrap::assign('locParadigm', session_user_prefers(Preferences::LOC_PARADIGM));
SmartyWrap::assign('paradigmLink', $paradigmLink);
SmartyWrap::assign('advancedSearch', $text || $sourceId);
SmartyWrap::assign('allDefinitions', $all);

if (!$xml) {
  SmartyWrap::addCss('paradigm');
  SmartyWrap::display('search.tpl');

} else {
  header('Content-type: text/xml');
  SmartyWrap::displayWithoutSkin('xml/search.tpl');
}

// Logging
if (Config::get('search-log.enabled')) {
  $logDefinitions = isset($definitions) ? $definitions : [];
  $log = new SearchLog($cuv, $redirectFrom, $searchType, $redirect, $logDefinitions);
  $log->logData();
}

?>
