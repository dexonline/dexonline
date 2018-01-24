<?php

require_once('../phplib/Core.php');
require_once('../phplib/ads/adsModule.php');

define('SEARCH_REGEXP', 0);
define('SEARCH_MULTIWORD', 1);
define('SEARCH_INFLECTED', 2);
define('SEARCH_APPROXIMATE', 3);
define('SEARCH_DEF_ID', 4);
define('SEARCH_ENTRY_ID', 5);
define('SEARCH_FULL_TEXT', 6);
define('SEARCH_LEXEME_ID', 7);

define('LIMIT_FULLTEXT_DISPLAY', Config::get('limits.limitFulltextSearch', 500));
define('PREVIEW_LIMIT', 20); // how many definitions to show by default

define('SPOOF_ENABLED', Config::get('global.spoofEnabled', false));

if(SPOOF_ENABLED) {
  define('SPOOF_WORDS', Config::get('global.spoofWords', []));
  define('SPOOF_NORMALIZE', Config::get('global.spoofNormalize', true));
};

// defLimit: how many definitions to display (null = not relevant)
// paradigm: whether to display the paradigm for $entries
// trees: whether to display the entries' trees
$DEFAULT_SEARCH_PARAMS = [
  'defLimit' => null,
  'paradigm' => false,
  'trees' => false,
];
$showTrees = Config::get('search.showTrees') && !Session::userPrefers(Preferences::NO_TREES);

$SEARCH_PARAMS = [
  SEARCH_REGEXP => $DEFAULT_SEARCH_PARAMS,
  SEARCH_MULTIWORD => array_replace($DEFAULT_SEARCH_PARAMS, [
    'defLimit' => PREVIEW_LIMIT,
  ]),
  SEARCH_INFLECTED => array_replace($DEFAULT_SEARCH_PARAMS, [
    'defLimit' => PREVIEW_LIMIT,
    'paradigm' => true,
    'trees' => $showTrees,
  ]),
  SEARCH_APPROXIMATE => $DEFAULT_SEARCH_PARAMS,
  SEARCH_DEF_ID => $DEFAULT_SEARCH_PARAMS,
  SEARCH_ENTRY_ID => array_replace($DEFAULT_SEARCH_PARAMS, [
    'paradigm' => true,
    'trees' => $showTrees,
  ]),
  // there is a limit for full-text searches, but we handle it separately for memory reasons
  SEARCH_FULL_TEXT => $DEFAULT_SEARCH_PARAMS,
];

$cuv = Request::get('cuv');
$entryId = Request::get('entryId');
$lexemeId = Request::get('lexemeId');
$defId = Request::get('defId');
$sourceUrlName = Request::get('source');
$text = Request::has('text');
$showParadigm = Request::get('showParadigm');
$format = checkFormat();
$all = Request::get('all');

$redirect = Session::get('redirect');
$redirectFrom = Session::get('init_word', '');
Session::unsetVar('redirect');
Session::unsetVar('init_word');

if ($cuv && !$redirect) {
  $cuv = Str::cleanupQuery($cuv);
}

Request::redirectToFriendlyUrl($cuv, $entryId, $lexemeId, $sourceUrlName, $text, $showParadigm,
                               $format, $all);

$paradigmLink = $_SERVER['REQUEST_URI'] . ($showParadigm ? '' : '/paradigma');

$searchType = SEARCH_INFLECTED;
$hasDiacritics = Session::userPrefers(Preferences::FORCE_DIACRITICS);
$hasRegexp = FALSE;
$isAllDigits = FALSE;
$all = $all || $showParadigm;

$source = $sourceUrlName ? Source::get_by_urlName($sourceUrlName) : null;
$sourceId = $source ? $source->id : null;

if ($cuv) {
  SmartyWrap::assign('cuv', $cuv);
  $hasDiacritics |= Str::hasDiacritics($cuv);
  $hasRegexp = Str::hasRegexp($cuv);
  $isAllDigits = Str::isAllDigits($cuv);
}

if(SPOOF_ENABLED && $cuv) {
  $cuv_normalized = SPOOF_NORMALIZE ? mb_strtolower(Str::unicodeToLatin($cuv)) : $cuv;
  $cuv_spoofed = @SPOOF_WORDS[$cuv_normalized];
  if ($cuv_spoofed) {
    $cuv_spoofed_hasDiacritics = $hasDiacritics || Str::hasDiacritics($cuv_spoofed);
  }
}

$definitions = [];
$entries = [];
$lexemes = [];
$trees = [];
$extra = [];
$adult = false;

$showWotd = Session::isWotdMode()
  && User::can(User::PRIV_EDIT)
  && !Config::get('global.mirror');

if ($isAllDigits) {
  $d = Definition::getByIdNotHidden($cuv);
  if ($d) {
    Util::redirect(Core::getWwwRoot() . "definitie/{$d->lexicon}/{$d->id}" . $format['tpl_path']);
  }
}

// Definition.id search
if ($defId) {
  $searchType = SEARCH_DEF_ID;
  $statuses = User::can(User::PRIV_VIEW_HIDDEN)
            ? [Definition::ST_ACTIVE, Definition::ST_HIDDEN]
            : [Definition::ST_ACTIVE];
  $definitions = Model::factory('Definition')
               ->where('id', $defId)
               ->where_in('status', $statuses)
               ->find_many();
}

// Lexeme.id search
if ($lexemeId) {
  $searchType = SEARCH_LEXEME_ID;
  $l = Lexeme::get_by_id($lexemeId);
  if (!$l || empty($l->getEntries())) {
    Util::redirect(Core::getWwwRoot());
  }
  $e = $l->getEntries()[0];
  Util::redirect(sprintf('%sintrare/%s/%s', Core::getWwwRoot(), $e->getShortDescription(), $e->id));
}

// Full-text search
if ($text) {
  $searchType = SEARCH_FULL_TEXT;

  if (Lock::exists(Lock::FULL_TEXT_INDEX)) {
    $extra['fullTextLock'] = true;
  } else {
    $words = preg_split('/ +/', $cuv);
    list($defIds, $stopWords, $adult) = Definition::searchFullText($words, $hasDiacritics, $sourceId);

    // enforce the limit before even loading the definitions to save memory
    // TODO: this can lead to a bug as follows: we load 100 definitions and filter them down
    // to 90. Then we print "100 definitions (at most 90 shown)".
    $extra['numDefinitionsFullText'] = count($defIds);
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
    if (SPOOF_ENABLED && $cuv_spoofed) {
      $entries_spoofed = Entry::searchInflectedForms($cuv_spoofed, $cuv_spoofed_hasDiacritics);
      $definitions = Definition::searchEntry($entries_spoofed[0]);
    }
    else {
      $definitions = Definition::searchEntry($entry);
    }
  }
}

// Regular expression search
// Count all the results, but load at most 1,000
if ($hasRegexp) {
  $searchType = SEARCH_REGEXP;
  $extra['numLexemes'] = Lexeme::searchRegexp($cuv, $hasDiacritics, $sourceId, true);
  $lexemes = Lexeme::searchRegexp($cuv, $hasDiacritics, $sourceId);
}

// If no search type requested so far, then normal search
if ($searchType == SEARCH_INFLECTED) {
  $entries = Entry::searchInflectedForms($cuv, $hasDiacritics);

  // successful search
  if (count($entries)) {
    if(SPOOF_ENABLED && $cuv_spoofed) {
      $entries_spoofed = Entry::searchInflectedForms($cuv_spoofed, $cuv_spoofed_hasDiacritics);
      $definitions = Definition::loadForEntries($entries_spoofed, $sourceId, $cuv);
    }
    else {
      $definitions = Definition::loadForEntries($entries, $sourceId, $cuv);
    }
    SmartyWrap::assign('wikiArticles', WikiArticle::loadForEntries($entries));

    // Add a warning if this word is in WotD
    if ($showWotd) {
      $wasWotd = Model::factory('Definition')
               ->table_alias('d')
               ->join('WordOfTheDayRel', ['d.id', '=', 'r.refId'], 'r')
               ->join('WordOfTheDay', ['w.id', '=', 'r.wotdId'], 'w')
               ->where('d.lexicon', $cuv)
               ->where('r.refType', 'Definition')
               ->find_one();
      if ($wasWotd) {
        FlashMessage::add('Acest cuvânt este în lista WotD', 'warning');
      }
    }
  }

  // fallback to multiword search
  if (empty($entries) && preg_match('/[- .]/', $cuv)) {
    $searchType = SEARCH_MULTIWORD;
    $words = preg_split('/[- .]+/', $cuv);
    $extra['ignoredWords'] = array_slice($words, 5);
    $words = array_slice($words, 0, 5);
    $definitions = Definition::searchMultipleWords(
      $words, $hasDiacritics, $sourceId);
  }

  // fallback to approximate search
  if (empty($entries) && empty($definitions)) {
    $searchType = SEARCH_APPROXIMATE;
    $entries = Lexeme::searchApproximate($cuv);
    SmartyWrap::assign('suggestNoBanner', true);
    if (count($entries) == 1) {
      FlashMessage::add("Ați fost redirecționat automat la forma „{$entries[0]->description}”.");
    }
  }

  if (count($entries) == 1) {
    // Convenience redirect when there is only one correct form. We want all pages to be canonical.
    $e = $entries[0];
    $l = $e->getMainLexeme();
    if ($cuv != $l->formNoAccent) {
      Session::set('redirect', true);
      Session::set('init_word', $cuv);

      // Try to redirect to the canonical /definitie page. However, if that result would return
      // multiple entries, then redirect to the specific entry.
      $candidates = Entry::searchInflectedForms($l->formNoAccent, true, false);
      if (count($candidates) == 1) {
        $sourcePart = $source ? "-{$source->urlName}" : '';		
        Util::redirect(sprintf('%sdefinitie%s/%s%s',
                               Core::getWwwRoot(),
                               $sourcePart,
                               $l->formNoAccent,
                               $format['tpl_path']));
      } else if (!$sourceId) {
        // if the source is set, then the lesser evil is to just leave the search word unaltered
        Util::redirect(sprintf('%sintrare/%s/%s%s',
                               Core::getWwwRoot(),
                               $e->getShortDescription(),
                               $e->id,
                               $format['tpl_path']));
      }
    }
  }
}

$results = SearchResult::mapDefinitionArray($definitions);

if (SPOOF_ENABLED && $cuv_spoofed) {

  function replaceSpoofedWord($definition, $cuv) {
    $pattern = '/<b>(.*)<\/b>(.*?)/U';
    $replacement = sprintf('<b>%s</b>${2}', mb_strtoupper($cuv));
    $definition->htmlRep = preg_replace($pattern, $replacement, $definition->htmlRep, 1);
    return $definition;
  }

  foreach ($results as $result) {
    $result->definition = replaceSpoofedWord($result->definition, $cuv);
  }
}

// Filter out hidden definitions
list($extra['unofficialHidden'], $extra['sourcesHidden'])
  = SearchResult::filter($results);

// Keep only a maximum number of definitions
$defLimit = $SEARCH_PARAMS[$searchType]['defLimit'];
if ($defLimit) {
  $extra['numDefinitions'] = count($results);
  if (!$all) {
    $results = array_slice($results, 0, $defLimit);
  }
}

if (empty($entries) && empty($lexemes) && empty($results)) {
  header('HTTP/1.0 404 Not Found');
}

// Collect meaning trees
// only display trees when no source is selected
if ($SEARCH_PARAMS[$searchType]['trees'] && !$sourceId) {
  $statuses = [Entry::STRUCT_STATUS_DONE, Entry::STRUCT_STATUS_UNDER_REVIEW];
  foreach ($entries as $e) {
    if (in_array($e->structStatus, $statuses)) {
      foreach ($e->getTrees() as $t) {
        if (($t->status == Tree::ST_VISIBLE) &&
            count($t->getMeanings()) &&
            !isset($trees[$t->id])) {
          $t->extractExamples();
          $t->extractEtymologies();
          $trees[$t->id] = $t;
        }
      }
    }
  }

  if (count($trees)) {
    SmartyWrap::addCss('meaningTree');
    usort($trees, [new TreeComparator($cuv), 'cmp']);
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
    foreach ($e->getLexemes() as $l) {
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
    foreach ($e->getLexemes() as $l) {
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

// We cannot show the paradigm tab by default if there isn't one to show.
$showParadigm = ($showParadigm || Session::userPrefers(Preferences::SHOW_PARADIGM))
  && $SEARCH_PARAMS[$searchType]['paradigm'];

foreach ($entries as $e) {
  $adult |= $e->adult;
}

SmartyWrap::assign('entries', $entries);
SmartyWrap::assign('lexemes', $lexemes);
SmartyWrap::assign('results', $results);
SmartyWrap::assign('trees', $trees);
SmartyWrap::assign('extra', $extra);
SmartyWrap::assign('text', $text);
SmartyWrap::assign('searchType', $searchType);
SmartyWrap::assign('searchParams', $SEARCH_PARAMS[$searchType]);
SmartyWrap::assign('sourceId', $sourceId);
SmartyWrap::assign('showParadigm', $showParadigm);
SmartyWrap::assign('locParadigm', Session::userPrefers(Preferences::LOC_PARADIGM));
SmartyWrap::assign('paradigmLink', $paradigmLink);
SmartyWrap::assign('allDefinitions', $all);
SmartyWrap::assign('showWotd', $showWotd);
SmartyWrap::assign('adult', $adult);
SmartyWrap::assign('pageType', 'search');
if ($text || $sourceId) {
  // must show the advanced search menu regardless of preference
  SmartyWrap::assign('advancedSearch', true);
}

switch ($format['name']) {
  case 'xml':
    header('Content-type: text/xml');
    SmartyWrap::displayWithoutSkin('xml/search.tpl');
    break;
  case 'json':
    header('Content-type: application/json');
    SmartyWrap::displayWithoutSkin('json/search.tpl');
    break;
  case 'html':
  default:
    SmartyWrap::addCss('paradigm');
    SmartyWrap::display('search.tpl');
}

// Logging
if (Config::get('search-log.enabled')) {
  $logDefinitions = isset($definitions) ? $definitions : [];
  $log = new SearchLog($cuv, $redirectFrom, $searchType, $redirect, $logDefinitions);
  $log->logData();
}

/*************************************************************************/

function checkFormat() {
  $f = Request::get('format');
  if (!$f) {
    $f = 'html';
  }

  $path = '';
  if (($f == 'xml') && Config::get('global.xmlApi')) {
    $path = '/xml';
  }
  if (($f == 'json') && Config::get('global.jsonApi')) {
    $path = '/json';
  }

  return ['name' => $f, 'tpl_path' => $path];
}

class TreeComparator {
  private $query;

  function __construct($query) {
    $this->query = $query;
  }

  function cmp($a, $b) {
    // lower precedence: natural sort order
    $score = (strcoll($a->description, $b->description) > 0);

    // higher precedence: prefer trees that exactly match the query
    if ($a->getShortDescription() != $this->query) {
      $score += 2;
    }
    if ($b->getShortDescription() != $this->query) {
      $score -= 2;
    }
    return $score;
  }
}
