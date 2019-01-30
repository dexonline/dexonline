<?php
require_once '../../phplib/Core.php';
User::mustHave(User::PRIV_ANY);
Util::assertNotMirror();

$recountButton = Request::has('recountButton');

if ($recountButton) {
  Util::recount();
  Util::redirect('index.php');
}

$reports = [
  ['text' => 'Definiții nemoderate',
   'url' => 'admin/viewPendingDefinitions',
   'count' => Variable::peek('Count.pendingDefinitions'),
   'privilege' => User::PRIV_EDIT
  ],
  ['text' => 'Definiții cu greșeli de tipar',
   'url' => 'admin/viewTypos',
   'count' => Variable::peek('Count.definitionsWithTypos'),
   'privilege' => User::PRIV_EDIT
  ],
  ['text' => 'Definiții cu abrevieri ambigue',
   'url' => 'admin/randomAbbrevReview',
   'count' => Variable::peek('Count.ambiguousAbbrevs'),
   'privilege' => User::PRIV_EDIT
  ],
  ['text' => 'Definiții OCR neverificate',
   'url' => 'admin/definitionEdit?isOcr=1',
   'count' => sprintf('%d (alocate dvs.: %d)',
                      Variable::peek('Count.rawOcrDefinitions'),
                      OCR::countAvailable(User::getActiveId())),
   'privilege' => User::PRIV_EDIT | User::PRIV_TRAINEE
  ],
  ['text' => 'Definiții fără eticheta [glife rare]',
   'url' => 'admin/viewMissingRareGlyphsTags',
   'count' => Variable::peek('Count.missingRareGlyphsTags'),
   'privilege' => User::PRIV_EDIT
  ],
  ['text' => 'Definiții cu eticheta inutilă [glife rare]',
   'url' => 'admin/viewUnneededRareGlyphsTags',
   'count' => Variable::peek('Count.unneededRareGlyphsTags'),
   'privilege' => User::PRIV_EDIT
  ],
  ['text' => 'Definiții neasociate cu nicio intrare',
   'url' => 'admin/viewUnassociatedDefinitions',
   'count' => Variable::peek('Count.unassociatedDefinitions'),
   'privilege' => User::PRIV_EDIT
  ],
  ['text' => 'Intrări neasociate cu definiții / lexeme',
   'url' => 'admin/viewUnassociatedEntries',
   'count' => Variable::peek('Count.unassociatedEntries'),
   'privilege' => User::PRIV_EDIT
  ],
  ['text' => 'Lexeme neasociate cu nicio intrare',
   'url' => 'admin/viewUnassociatedLexemes',
   'count' => Variable::peek('Count.unassociatedLexemes'),
   'privilege' => User::PRIV_EDIT
  ],
  ['text' => 'Arbori neasociați cu nicio intrare',
   'url' => 'admin/viewUnassociatedTrees',
   'count' => Variable::peek('Count.unassociatedTrees'),
   'privilege' => User::PRIV_EDIT
  ],
  ['text' => 'Intrări ambigue',
   'url' => 'admin/viewAmbiguousEntries',
   'count' => Variable::peek('Count.ambiguousEntries'),
   'privilege' => User::PRIV_EDIT
  ],
  ['text' => 'Intrări cu definiții de structurat',
   'url' => 'admin/viewEntriesWithDefinitionsToStructure',
   'count' => Variable::peek('Count.entriesWithDefinitionsToStructure'),
   'privilege' => User::PRIV_STRUCT
  ],
  ['text' => 'Intrări fără lexeme principale',
   'url' => 'admin/viewEntriesWithoutMainLexemes',
   'count' => Variable::peek('Count.entriesWithoutMainLexemes'),
   'privilege' => User::PRIV_STRUCT
  ],
  ['text' => 'Lexeme fără accent',
   'url' => 'admin/viewLexemesWithoutAccents',
   'count' => Variable::peek('Count.lexemesWithoutAccent'),
   'privilege' => User::PRIV_EDIT
  ],
  ['text' => 'Lexeme ambigue',
   'url' => 'admin/viewAmbiguousLexemes',
   'count' => Variable::peek('Count.ambiguousLexemes'),
   'privilege' => User::PRIV_EDIT
  ],
  ['text' => 'Lexeme fără paradigme',
   'url' => 'admin/viewTemporaryLexemes',
   'count' => Variable::peek('Count.temporaryLexemes'),
   'privilege' => User::PRIV_EDIT
  ],
  ['text' => 'Lexeme cu paradigme învechite',
   'url' => 'admin/viewStaleParadigms',
   'count' => Variable::peek('Count.staleParadigms'),
   'privilege' => User::PRIV_ADMIN
  ],
  ['text' => 'Mențiuni despre arbori nestructurați',
   'url' => 'admin/viewTreeMentions',
   'count' => Variable::peek('Count.treeMentions'),
   'privilege' => User::PRIV_EDIT
  ],
];

// OR of all the above privileges -- that's the mask to view any reports
$reportPriv = array_reduce($reports, 'orReducer', 0);

$links = [
  [
    'url' => 'moderatori',
    'text' => 'moderatori',
    'privilege' => User::PRIV_ADMIN,
  ],
  [
    'url' => 'surse',
    'text' => 'surse',
    'privilege' => User::PRIV_ADMIN,
  ],
  [
    'url' => 'etichete',
    'text' => 'etichete',
    'privilege' => User::PRIV_EDIT,
  ],
  [
    'url' => 'tipuri-modele',
    'text' => 'tipuri de model',
    'privilege' => User::PRIV_EDIT,
  ],
  [
    'url' => 'flexiuni',
    'text' => 'flexiuni',
    'privilege' => User::PRIV_ADMIN,
  ],
  [
    'url' => 'admin/ocrInput',
    'text' => 'adaugă definiții OCR',
    'privilege' => User::PRIV_ADMIN,
  ],
  [
    'url' => 'admin/contribTotals',
    'text' => 'contorizare contribuții',
    'privilege' => User::PRIV_ADMIN,
  ],
  [
    'url' => 'admin/abbrevInput',
    'text' => 'adaugă abrevieri',
    'privilege' => User::PRIV_ADMIN,
  ],
  [
    'url' => 'admin/abbrevList',
    'text' => 'abrevieri',
    'privilege' => User::PRIV_ADMIN | User::PRIV_EDIT,
  ],
  [
    'url' => 'admin/definitionEdit',
    'text' => 'adaugă o definiție',
    'privilege' => User::PRIV_EDIT | User::PRIV_TRAINEE,
  ],
  [
    'url' => 'admin/traineeDefinitions',
    'text' => 'definițiile mele',
    'privilege' => User::PRIV_TRAINEE,
  ],
  [
    'url' => 'admin/harmonize',
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

SmartyWrap::assign([
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
SmartyWrap::addCss('admin', 'bootstrap-spinedit', 'bootstrap-datepicker');
SmartyWrap::addJs('select2Dev', 'adminIndex', 'modelDropdown', 'bootstrap-spinedit',
                  'bootstrap-datepicker');
SmartyWrap::display('admin/index.tpl');

/*************************************************************************/

function orReducer($carry, $r) {
  return $carry | $r['privilege'];
}
