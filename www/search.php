<?php

require_once '../lib/Core.php';

const SEARCH_REGEXP = 0;
const SEARCH_MULTIWORD = 1;
const SEARCH_INFLECTED = 2;
const SEARCH_APPROXIMATE = 3;
const SEARCH_DEF_ID = 4;
const SEARCH_ENTRY_ID = 5;
const SEARCH_FULL_TEXT = 6;
const SEARCH_LEXEME_ID = 7;

// defLimit: how many definitions to display (null = not relevant)
// paradigm: whether to display the paradigm for $entries
// trees: whether to display the entries' trees
const DEFAULT_SEARCH_PARAMS = [
  'defLimit' => null,
  'paradigm' => false,
  'trees' => false,
];

$searchParams = [
  SEARCH_REGEXP => DEFAULT_SEARCH_PARAMS,
  SEARCH_MULTIWORD => array_replace(DEFAULT_SEARCH_PARAMS, [
    'defLimit' => Config::LIMIT_SEARCH_DEFINITIONS,
  ]),
  SEARCH_INFLECTED => array_replace(DEFAULT_SEARCH_PARAMS, [
    'defLimit' => Config::LIMIT_SEARCH_DEFINITIONS,
    'paradigm' => true,
    'trees' => Config::SEARCH_SHOW_TREES,
  ]),
  SEARCH_APPROXIMATE => DEFAULT_SEARCH_PARAMS,
  SEARCH_DEF_ID => DEFAULT_SEARCH_PARAMS,
  SEARCH_ENTRY_ID => array_replace(DEFAULT_SEARCH_PARAMS, [
    'paradigm' => true,
    'trees' => Config::SEARCH_SHOW_TREES,
  ]),
  // there is a limit for full-text searches, but we handle it separately for memory reasons
  SEARCH_FULL_TEXT => DEFAULT_SEARCH_PARAMS,
];

$cuv = Request::getWithApostrophes('cuv');
$entryId = Request::get('entryId');
$lexemeId = Request::get('lexemeId');
$defId = Request::get('defId');
$sourceUrlName = Request::get('source');
$text = Request::has('text');
$tab = Tab::getFromUrl();
$format = checkFormat();
$all = Request::get('all');

$redirect = Session::get('redirect');
$redirectFrom = Session::get('init_word', '');
Session::unsetVar('redirect');
Session::unsetVar('init_word');

if ($cuv && !$redirect) {
  $cuv = Str::cleanupQuery($cuv);
}

Request::redirectToFriendlyUrl(
  $cuv, $entryId, $lexemeId, $sourceUrlName, $text, $tab, $format, $all);

$searchType = SEARCH_INFLECTED;
$hasDiacritics = Session::userPrefers(Preferences::FORCE_DIACRITICS);
$hasRegexp = FALSE;
$isAllDigits = FALSE;
$all = $all || ($tab != Tab::T_RESULTS);

$source = $sourceUrlName ? Source::get_by_urlName($sourceUrlName) : null;
$sourceId = $source ? $source->id : null;

if ($cuv) {
  Smart::assign('cuv', $cuv);
  $hasDiacritics |= Str::hasDiacritics($cuv);
  $hasRegexp = Str::hasRegexp($cuv);
  $isAllDigits = Str::isAllDigits($cuv);
}

Plugin::notify('searchStart', $cuv, $hasDiacritics);

$definitions = [];
$entries = [];
$lexemes = [];
$trees = [];
$wikiArticles = [];
$extra = [];
$adult = false;

$showWotd = Session::isWotdMode() && User::can(User::PRIV_EDIT);

if ($isAllDigits) {
  $d = Definition::getByIdNotHidden($cuv);
  if ($d) {
    Util::redirect(Config::URL_PREFIX . "definitie/{$d->lexicon}/{$d->id}" . $format['tpl_path']);
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
    Util::redirectToHome();
  }
  $e = $l->getEntries()[0];
  redirectToEntry($e);
}

// Full-text search
if ($text) {
  $searchType = SEARCH_FULL_TEXT;

  if (Variable::peek(Variable::LOCK_FTI)) {
    $extra['fullTextLock'] = true;
  } else {
    $words = preg_split('/ +/', $cuv);
    list($defIds, $stopWords, $adult) = Definition::searchFullText($words, $hasDiacritics, $sourceId);

    // enforce the limit before even loading the definitions to save memory
    // TODO: this can lead to a bug as follows: we load 100 definitions and filter them down
    // to 90. Then we print "100 definitions (at most 90 shown)".
    $extra['numDefinitionsFullText'] = count($defIds);
    $extra['stopWords'] = $stopWords;
    $defIds = array_slice($defIds, 0, Config::LIMIT_FULL_TEXT_RESULTS);

    // load all definitions at once, then resort them by $defIds
    $definitions = [];
    if (count($defIds)) {
      $unsorted = Model::factory('Definition')
        ->where_in('id', $defIds)
        ->find_many();
      $map = Util::mapById($unsorted);
      foreach ($defIds as $id) {
        $definitions[] = $map[$id];
      }
    }

    // For single-word queries, just order the definitions by lexicon.
    if (count($words) == 1) {
      usort($definitions, function($a, $b) {
        return strcoll($a->lexicon, $b->lexicon);
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
    Smart::assign('cuv', $entry->getShortDescription());
    $definitions = Definition::searchEntry($entry);
    Plugin::notify('searchEntryId', $definitions);
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
  // Convenience redirect when there is only one canonical form (possibly with
  // diacritics added).
  $canonicalCuv = Lexeme::canonicalize($cuv, $hasDiacritics);
  if ($canonicalCuv && ($canonicalCuv != $cuv)) {
    Session::set('redirect', true);
    Session::set('init_word', $cuv);
    redirectToDefinition($canonicalCuv, $source, $format);
  }

  $entries = Entry::searchInflectedForms($cuv, $hasDiacritics);

  // successful search
  if (count($entries)) {
    $definitions = Definition::loadForEntries($entries, $sourceId, $cuv);
    Plugin::notify('searchInflected', $definitions, $sourceId);
    $wikiArticles = WikiArticle::loadForEntries($entries);

    // Add a warning if this word is in WotD
    if ($showWotd) {
      $wasWotd = Model::factory('Definition')
               ->table_alias('d')
               ->join('WordOfTheDay', ['d.id', '=', 'w.definitionId'], 'w')
               ->where('d.lexicon', $cuv)
               ->find_one();
      if ($wasWotd) {
        FlashMessage::add('Acest cuvânt este în lista WotD.', 'warning');
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
    if (count($entries) == 1) {
      $msg = sprintf(_('We redirected you automatically from <b>%s</b> to <b>%s</b>.'),
                     $cuv, $entries[0]->description);
      FlashMessage::add($msg);
    }
  }

  if (count($entries)) {
    $baseForms = InflectedForm::isElision($cuv);
    if ($baseForms) {
      FlashMessage::addTemplate(
        'formIsElision.tpl',
        [ 'elision' => $cuv, 'baseForms' => $baseForms ],
        'info'
      );
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
        // If the main lexeme does not generate $cuv at all, let the user know
        // that they searched for a variant. However, do not throw this
        // warning for typos or for queries without diacritics.
        $generates = InflectedForm::get_by_lexemeId_formUtf8General($l->id, $cuv);
        if (!$generates && ($searchType == SEARCH_INFLECTED)) {
          FlashMessage::addTemplate(
            'searchedVariant.tpl',
            [ 'query' => $cuv, 'recommended' => $l->formNoAccent ],
            'warning');
        }

        redirectToDefinition($l->formNoAccent, $source, $format);
      } else if (!$sourceId) {
        // if the source is set, then the lesser evil is to just leave the search word unaltered
        redirectToEntry($e, $format);
      }
    }
  }
}

$results = SearchResult::mapDefinitionArray($definitions);

// Filter out hidden definitions
list($extra['nonNormativeHidden'], $extra['sourcesHidden'])
  = SearchResult::filter($results);

SearchResult::collapseIdentical($results);

$extra['numResults'] = count($results) ?: count($entries) ?: count($lexemes);

// Keep only a maximum number of definitions
$defLimit = $searchParams[$searchType]['defLimit'];
if ($defLimit) {
  $extra['numDefinitions'] = count($results);
  if (!$all) {
    $results = array_slice($results, 0, $defLimit);
  }
}

$sourceTypes = SourceType::loadForSearchResults($results);

if (empty($entries) && empty($lexemes) && empty($results)) {
  header('HTTP/1.0 404 Not Found');
}

Preload::loadEntryTags(Util::objectProperty($entries, 'id'));

// Collect meaning trees
// only display trees when no source is selected
if ($searchParams[$searchType]['trees'] && !$sourceId) {
  Preload::loadEntryTrees(Util::objectProperty($entries, 'id'));
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
    Smart::addResources('meaningTree');
    usort($trees, [new TreeComparator($cuv), 'cmp']);
  }
}

// Collect inflected forms
$conjugations = null;
$declensions = null;
if ($searchParams[$searchType]['paradigm']) {

  // Compute the text of the link to the paradigm div
  $conjugations = false;
  $declensions = false;
  Preload::loadEntryLexemes(Util::objectProperty($entries, 'id'));
  $lexemeIds = [];
  foreach ($entries as $e) {
    foreach ($e->getLexemes() as $l) {
      $isVerb = ($l->modelType == 'V') || ($l->modelType == 'VT');
      $conjugations |= $isVerb;
      $declensions |= !$isVerb;
      $lexemeIds[] = $l->id;
    }
  }
  Preload::loadLexemeInflectedForms($lexemeIds);
  Preload::loadLexemeModelTypes($lexemeIds);
  Preload::loadLexemePartsOfSpeech($lexemeIds);
  Preload::loadLexemeSources($lexemeIds);
  Preload::loadLexemeTags($lexemeIds);
  $declensionText = implode(' / ', array_filter([
    $conjugations ? _('conjugations') : '',
    $declensions ? _('declensions') : '',
  ]));
  Smart::assign('declensionText', $declensionText);

  // Check if any of the inflected forms are unrecommended
  $hasUnrecommendedForms = false;
  $hasElisionForms = false;
  foreach ($entries as $e) {
    foreach ($e->getLexemes() as $l) {
      $l->getModelType();
      $l->getSourceNames();
      $map = $l->loadInflectedFormMap();
      foreach ($map as $ifs) {
        foreach ($ifs as $if) {
          $hasUnrecommendedForms |= !$if->recommended;
          $hasElisionForms |= $if->apheresis || $if->apocope;
          $hasElisionForms |= in_array($if->inflectionId, Constant::LONG_VERB_INFLECTION_IDS);
        }
      }
    }
  }
  Smart::assign([
    'hasUnrecommendedForms' => $hasUnrecommendedForms,
    'hasElisionForms' => $hasElisionForms,
  ]);
}

// Collect source list to display in meta tags
$sourceList = [];
foreach ($results as $row) {
  $sourceList[$row->source->shortName] = true;
  foreach ($row->dependants as $dep) {
    $sourceList[$dep->source->shortName] = true;
  }
}
$sourceList = array_keys($sourceList);
Smart::assign('sourceList', $sourceList);

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

  Smart::assign('pageDescription', $pageDescription);
}

$images = Visual::loadAllForEntries($entries);
if (count($images)) {
  Smart::addResources('gallery');
}

// if the URL doesn't specify a tab, choose an active one
if ($tab === false) {
  $tab = Tab::getActive(
    $searchParams[$searchType]['paradigm'],
    count($trees),
    count($images),
    count($wikiArticles)
  );
}

foreach ($entries as $e) {
  $adult |= $e->adult;
}

Smart::assign([
  'entries' => $entries,
  'lexemes' => $lexemes,
  'results' => $results,
  'trees' => $trees,
  'images' => $images,
  'wikiArticles' => $wikiArticles,
  'extra' => $extra,
  'text' => $text,
  'searchType' => $searchType,
  'searchParams' => $searchParams[$searchType],
  'source' => $source,
  'sourceId' => $sourceId,
  'sourceTypes' => $sourceTypes,
  'tab' => $tab,
  'allDefinitions' => $all,
  'showWotd' => $showWotd,
  'pageType' => 'search',
]);
if ($text || $sourceId) {
  // must show the advanced search menu regardless of preference
  Smart::assign('advancedSearch', true);
}

switch ($format['name']) {
  case 'xml':
    header('Content-type: text/xml');
    Smart::displayWithoutSkin('xml/search.tpl');
    break;
  case 'json':
    header('Content-type: application/json');
    Smart::displayWithoutSkin('json/search.tpl');
    break;
  case 'html':
  default:
    Smart::addResources('paradigm', 'scrollTop');
    Smart::display('search.tpl');
}

// Logging
if (Config::SEARCH_LOG_ENABLED) {
  $logDefinitions = isset($definitions) ? $definitions : [];
  $log = new SearchLog($cuv, $redirectFrom, $searchType, $redirect, $logDefinitions);
  $log->logData();
}

/*************************************************************************/

function checkFormat() {
  $path = Request::get('format');

  if ($path == '/json' && Config::SEARCH_JSON_API) {
    return ['name' => 'json', 'tpl_path' => '/json'];
  } else if ($path == '/xml' && Config::SEARCH_XML_API) {
    return ['name' => 'xml', 'tpl_path' => '/xml'];
  } else {
    return ['name' => 'html', 'tpl_path' => ''];
  }
}

/**
 * If a tab is specified in the URL, returns one of Constant::TAB_*. Otherwise
 * returns false.
 */
function getTab() {
  return array_search('/' . Request::get('tab'), Constant::TAB_URL);
}

/**
 * Returns a permalink URL for this tab.
 *
 * @param int $tab One of the Constant::TAB_* values.
 */
function getTabPermalink($tab) {
  $uri = $_SERVER['REQUEST_URI'];

  // remove existing tab markers
  $regexp = sprintf('@(%s|%s|%s|%s)$@',
                    Constant::TAB_URL[Constant::TAB_PARADIGM],
                    Constant::TAB_URL[Constant::TAB_TREE],
                    Constant::TAB_URL[Constant::TAB_GALLERY],
                    Constant::TAB_URL[Constant::TAB_ARTICLES]);
  $uri = preg_replace($regexp, '', $uri);

  // add the paradigm tab marker
  $uri .= Constant::TAB_URL[$tab];
  return $uri;
}

function redirectToDefinition(string $query, ?Source $source, array $format) {
  $sourcePart = $source ? "-{$source->urlName}" : '';
  Util::redirect(sprintf('%sdefinitie%s/%s%s',
                         Config::URL_PREFIX,
                         $sourcePart,
                         $query,
                         $format['tpl_path']));
}

function redirectToEntry(Entry $e, array $format = null) {
  Util::redirect(sprintf('%sintrare/%s/%s%s',
                         Config::URL_PREFIX,
                         $e->getShortDescription(),
                         $e->id,
                         $format['tpl_path'] ?? ''));
}

class TreeComparator {
  private $query;

  function __construct($query) {
    $this->query = $query;
  }

  function cmp($a, $b) {
    // lower precedence: natural sort order
    $score = (int)(strcoll($a->description, $b->description) > 0);

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
