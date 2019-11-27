<?php

User::mustHave(User::PRIV_ANY);

$recountButton = Request::has('recountButton');

if ($recountButton) {
  Util::recount();
  Util::redirectToRoute('aggregate/dashboard');
}

// single query instead of ~20 distinct queries
$counts = Variable::loadCounts();

$reports = [
  ['text' => 'Definiții nemoderate',
   'url' => 'report/pendingDefinitions',
   'count' => $counts['pendingDefinitions'],
   'privilege' => User::PRIV_EDIT
  ],
  ['text' => 'Definiții cu greșeli de tipar',
   'url' => 'report/typos',
   'count' => $counts['definitionsWithTypos'],
   'privilege' => User::PRIV_EDIT
  ],
  ['text' => 'Definiții cu abrevieri ambigue',
   'url' => 'report/randomAbbrevReview',
   'count' => $counts['ambiguousAbbrevs'],
   'privilege' => User::PRIV_EDIT
  ],
  ['text' => 'Definiții OCR neverificate',
   'url' => 'editare-definitie?isOcr=1',
   'count' => sprintf('%d (alocate dvs.: %d)',
                      $counts['rawOcrDefinitions'],
                      OCR::countAvailable(User::getActiveId())),
   'privilege' => User::PRIV_EDIT | User::PRIV_TRAINEE
  ],
  ['text' => 'Definiții fără eticheta [glife rare]',
   'url' => 'report/missingRareGlyphsTags',
   'count' => $counts['missingRareGlyphsTags'],
   'privilege' => User::PRIV_EDIT
  ],
  ['text' => 'Definiții cu eticheta inutilă [glife rare]',
   'url' => 'report/unneededRareGlyphsTags',
   'count' => $counts['unneededRareGlyphsTags'],
   'privilege' => User::PRIV_EDIT
  ],
  ['text' => 'Definiții neasociate cu nicio intrare',
   'url' => 'report/unassociatedDefinitions',
   'count' => $counts['unassociatedDefinitions'],
   'privilege' => User::PRIV_EDIT
  ],
  ['text' => 'Intrări neasociate cu definiții / lexeme',
   'url' => 'report/unassociatedEntries',
   'count' => $counts['unassociatedEntries'],
   'privilege' => User::PRIV_EDIT
  ],
  ['text' => 'Lexeme neasociate cu nicio intrare',
   'url' => 'report/unassociatedLexemes',
   'count' => $counts['unassociatedLexemes'],
   'privilege' => User::PRIV_EDIT
  ],
  ['text' => 'Arbori neasociați cu nicio intrare',
   'url' => 'report/unassociatedTrees',
   'count' => $counts['unassociatedTrees'],
   'privilege' => User::PRIV_EDIT
  ],
  ['text' => 'Intrări ambigue',
   'url' => 'report/ambiguousEntries',
   'count' => $counts['ambiguousEntries'],
   'privilege' => User::PRIV_EDIT
  ],
  ['text' => 'Intrări cu definiții de structurat',
   'url' => 'report/entriesWithDefinitionsToStructure',
   'count' => $counts['entriesWithDefinitionsToStructure'],
   'privilege' => User::PRIV_STRUCT
  ],
  ['text' => 'Intrări fără lexeme principale',
   'url' => 'report/entriesWithoutMainLexemes',
   'count' => $counts['entriesWithoutMainLexemes'],
   'privilege' => User::PRIV_STRUCT
  ],
  ['text' => 'Intrări cu mai multe lexeme principale',
   'url' => 'report/entriesWithMultipleMainLexemes',
   'count' => $counts['entriesWithMultipleMainLexemes'],
   'privilege' => User::PRIV_STRUCT
  ],
  ['text' => 'Lexeme fără accent',
   'url' => 'report/lexemesWithoutAccent',
   'count' => $counts['lexemesWithoutAccent'],
   'privilege' => User::PRIV_EDIT
  ],
  ['text' => 'Lexeme ambigue',
   'url' => 'report/ambiguousLexemes',
   'count' => $counts['ambiguousLexemes'],
   'privilege' => User::PRIV_EDIT
  ],
  ['text' => 'Lexeme fără paradigme',
   'url' => 'report/temporaryLexemes',
   'count' => $counts['temporaryLexemes'],
   'privilege' => User::PRIV_EDIT
  ],
  ['text' => 'Lexeme cu paradigme învechite',
   'url' => 'report/staleParadigms',
   'count' => $counts['staleParadigms'],
   'privilege' => User::PRIV_ADMIN
  ],
  ['text' => 'Mențiuni despre arbori nestructurați',
   'url' => 'report/treeMentions',
   'count' => $counts['treeMentions'],
   'privilege' => User::PRIV_EDIT
  ],
];

// OR of all the above privileges -- that's the mask to view any reports
$reportPriv = array_reduce($reports, 'orReducer', 0);

$links = [
  [
    'url' => Router::link('user/list'),
    'text' => 'moderatori',
    'privilege' => User::PRIV_ADMIN,
  ],
  [
    'url' => Router::link('source/list'),
    'text' => 'surse',
    'privilege' => User::PRIV_ADMIN,
  ],
  [
    'url' => Router::link('source-role/edit'),
    'text' => 'roluri ale autorilor',
    'privilege' => User::PRIV_ADMIN,
  ],
  [
    'url' => Router::link('tag/list'),
    'text' => 'etichete',
    'privilege' => User::PRIV_EDIT,
  ],
  [
    'url' => Router::link('model/listTypes'),
    'text' => 'tipuri de model',
    'privilege' => User::PRIV_EDIT,
  ],
  [
    'url' => Router::link('inflection/list'),
    'text' => 'flexiuni',
    'privilege' => User::PRIV_ADMIN,
  ],
  [
    'url' => Router::link('definition/ocrUpload'),
    'text' => 'adaugă definiții OCR',
    'privilege' => User::PRIV_ADMIN,
  ],
  [
    'url' => Router::link('definition/contribTotals'),
    'text' => 'contorizare contribuții',
    'privilege' => User::PRIV_ADMIN,
  ],
  [
    'url' => Router::link('abbreviation/upload'),
    'text' => 'adaugă abrevieri',
    'privilege' => User::PRIV_ADMIN,
  ],
  [
    'url' => Router::link('abbreviation/list'),
    'text' => 'abrevieri',
    'privilege' => User::PRIV_ADMIN | User::PRIV_EDIT,
  ],
  [
    'url' => Router::link('definition/edit'),
    'text' => 'adaugă o definiție',
    'privilege' => User::PRIV_EDIT | User::PRIV_TRAINEE,
  ],
  [
    'url' => Router::link('definition/trainee'),
    'text' => 'definițiile mele',
    'privilege' => User::PRIV_TRAINEE,
  ],
  [
    'url' => Router::link('aggregate/harmonize'),
    'text' => 'armonizare lexem-etichetă',
    'privilege' => User::PRIV_ADMIN,
  ],
];

// OR of all the above privileges -- that's the mask to view any links
$linkPriv = array_reduce($links, 'orReducer', 0);

$minModDate = Model::factory('Variable')
            ->where_like('name', 'Count.%')
            ->min('modDate');
$timeAgo = time() - $minModDate;

$wotdAssistantDates = [
  strtotime("+1 month"),
  strtotime("+2 month"),
  strtotime("+3 month"),
];

Smart::assign([
  'structurists' => User::getStructurists(),
  'reports' => $reports,
  'reportPriv' => $reportPriv,
  'modelTypes' => ModelType::getAll(),
  'canonicalModelTypes' => ModelType::loadCanonical(),
  'links' => $links,
  'linkPriv' => $linkPriv,
  'timeAgo' => $timeAgo,
  'wotdAssistantDates' => $wotdAssistantDates,
]);
Smart::addResources(
  'admin', 'bootstrap-datepicker', 'modelDropdown', 'select2Dev'
);
Smart::display('aggregate/dashboard.tpl');

/*************************************************************************/

function orReducer($carry, $r) {
  return $carry | $r['privilege'];
}
