<?php
require_once('../../phplib/Core.php');
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
   'url' => 'admin/definitionEdit',
   'count' => sprintf('%d (disponibile: %d)',
                      Variable::peek('Count.rawOcrDefinitions'),
                      OCR::countAvailable(User::getActiveId())),
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
   'url' => 'admin/viewUnassociatedLexems',
   'count' => Variable::peek('Count.unassociatedLexems'),
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
  ['text' => 'Lexeme fără accent',
   'url' => 'admin/viewLexemsWithoutAccents',
   'count' => Variable::peek('Count.lexemesWithoutAccent'),
   'privilege' => User::PRIV_EDIT
  ],
  ['text' => 'Lexeme ambigue',
   'url' => 'admin/viewAmbiguousLexems',
   'count' => Variable::peek('Count.ambiguousLexemes'),
   'privilege' => User::PRIV_EDIT
  ],
  ['text' => 'Lexeme fără paradigme',
   'url' => 'admin/viewTemporaryLexems',
   'count' => Variable::peek('Count.temporaryLexemes'),
   'privilege' => User::PRIV_EDIT
  ],
  ['text' => 'Mențiuni despre arbori nestructurați',
   'url' => 'admin/viewTreeMentions',
   'count' => Variable::peek('Count.treeMentions'),
   'privilege' => User::PRIV_EDIT
  ],
];

$minModDate = Model::factory('Variable')
            ->where_like('name', 'Count.%')
            ->min('modDate');
$timeAgo = time() - $minModDate;

SmartyWrap::assign('structurists', User::getStructurists());
SmartyWrap::assign('reports', $reports);
SmartyWrap::assign('timeAgo', $timeAgo);
SmartyWrap::addCss('admin', 'bootstrap-spinedit', 'bootstrap-datepicker');
SmartyWrap::addJs('select2Dev', 'adminIndex', 'modelDropdown', 'bootstrap-spinedit',
                  'bootstrap-datepicker');
SmartyWrap::display('admin/index.tpl');
