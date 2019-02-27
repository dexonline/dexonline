<?php
require_once '../../lib/Core.php';
User::mustHave(User::PRIV_ANY);

$recountButton = Request::has('recountButton');

if ($recountButton) {
  Util::recount();
  Util::redirect('index.php');
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
   'url' => 'admin/definitionEdit?isOcr=1',
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
    'url' => '../moderatori',
    'text' => 'moderatori',
    'privilege' => User::PRIV_ADMIN,
  ],
  [
    'url' => Router::link('source/list'),
    'text' => 'surse',
    'privilege' => User::PRIV_ADMIN,
  ],
  [
    'url' => '../etichete',
    'text' => 'etichete',
    'privilege' => User::PRIV_EDIT,
  ],
  [
    'url' => '../tipuri-modele',
    'text' => 'tipuri de model',
    'privilege' => User::PRIV_EDIT,
  ],
  [
    'url' => '../flexiuni',
    'text' => 'flexiuni',
    'privilege' => User::PRIV_ADMIN,
  ],
  [
    'url' => 'ocrInput',
    'text' => 'adaugă definiții OCR',
    'privilege' => User::PRIV_ADMIN,
  ],
  [
    'url' => 'contribTotals',
    'text' => 'contorizare contribuții',
    'privilege' => User::PRIV_ADMIN,
  ],
  [
    'url' => 'abbrevInput',
    'text' => 'adaugă abrevieri',
    'privilege' => User::PRIV_ADMIN,
  ],
  [
    'url' => 'abbrevList',
    'text' => 'abrevieri',
    'privilege' => User::PRIV_ADMIN | User::PRIV_EDIT,
  ],
  [
    'url' => 'definitionEdit',
    'text' => 'adaugă o definiție',
    'privilege' => User::PRIV_EDIT | User::PRIV_TRAINEE,
  ],
  [
    'url' => 'traineeDefinitions',
    'text' => 'definițiile mele',
    'privilege' => User::PRIV_TRAINEE,
  ],
  [
    'url' => 'harmonize',
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
  'admin', 'adminIndex', 'bootstrap-datepicker', 'modelDropdown', 'select2Dev'
);
Smart::display('admin/index.tpl');

/*************************************************************************/

function orReducer($carry, $r) {
  return $carry | $r['privilege'];
}
