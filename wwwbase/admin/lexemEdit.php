<?php
require_once("../../phplib/Core.php"); 
User::mustHave(User::PRIV_EDIT | User::PRIV_STRUCT);
Util::assertNotMirror();

// We get some data as JSON because it is 2-dimensional (a list of lists)
// and PHP cannot parse the form data correctly.

// Lexem parameters
$lexemId = Request::get('lexemId');
$lexemForm = Request::get('lexemForm');
$lexemNumber = Request::get('lexemNumber');
$lexemDescription = Request::get('lexemDescription');
$lexemComment = Request::get('lexemComment');
$needsAccent = Request::has('needsAccent');
$main = Request::has('main');
$stopWord = Request::has('stopWord');
$hyphenations = Request::get('hyphenations');
$pronunciations = Request::get('pronunciations');
$entryIds = Request::get('entryIds', []);
$tagIds = Request::get('tagIds', []);
$renameRelated = Request::has('renameRelated');

// Paradigm parameters
$compound = Request::has('compound');
$sourceIds = Request::get('sourceIds', []);
$notes = Request::get('notes');
$isLoc = Request::has('isLoc');

// Simple lexeme parameters
$modelType = Request::get('modelType');
$modelNumber = Request::get('modelNumber');
$restriction = Request::get('restriction');

// Compound lexeme parameters
$compoundModelType = Request::get('compoundModelType');
$compoundRestriction = Request::get('compoundRestriction');
$partIds = Request::get('partIds', []);
$declensions = Request::get('declensions', []);
$capitalized = Request::get('capitalized');

// Button parameters
$refreshButton = Request::has('refreshButton');
$saveButton = Request::has('saveButton');
$cloneButton = Request::has('cloneButton');
$deleteButton = Request::has('deleteButton');

$lexem = Lexem::get_by_id($lexemId);
$original = Lexem::get_by_id($lexemId); // Keep a copy so we can test whether certain fields have changed

if ($cloneButton) {
  $newLexem = $lexem->_clone();
  Log::notice("Cloned lexem {$lexem->id} ({$lexem->formNoAccent}), new id is {$newLexem->id}");
  Util::redirect("lexemEdit.php?lexemId={$newLexem->id}");
}

if ($deleteButton) {
  $homonym = Model::factory('Lexem')
           ->where('formNoAccent', $lexem->formNoAccent)
           ->where_not_equal('id', $lexem->id)
           ->find_one();
  $lexem->delete();
  if ($homonym) {
    FlashMessage::add('Am șters lexemul și v-am redirectat la unul dintre omonime.', 'success');
    Util::redirect("?lexemId={$homonym->id}");
  } else {
    FlashMessage::add('Am șters lexemul.', 'success');
    Util::redirect('index.php');
  }
}

if ($refreshButton || $saveButton) {
  populate($lexem, $original, $lexemForm, $lexemNumber, $lexemDescription, $lexemComment,
           $needsAccent, $main, $stopWord, $hyphenations, $pronunciations, $entryIds,
           $compound, $modelType, $modelNumber, $restriction, $compoundModelType,
           $compoundRestriction, $partIds, $declensions, $capitalized, $notes, $isLoc,
           $sourceIds, $tagIds);

  if (validate($lexem, $original)) {
    // Case 1: Validation passed
    if ($saveButton) {
      if (($original->modelType == 'VT') && ($lexem->modelType != 'VT')) {
        $original->deleteParticiple();
      }
      if (in_array($original->modelType, ['V', 'VT']) &&
          !in_array($lexem->modelType, ['V', 'VT'])) {
        $original->deleteLongInfinitive();
      }
      $lexem->deepSave();
      $lexem->regenerateDependentLexems();

      if ($renameRelated) {
        // Grab all the entries
        foreach ($lexem->getEntries() as $e) {
          if ($e->description == $original->formNoAccent) {
            FlashMessage::add(sprintf('Am redenumit o intrare din „%s” în „%s”.',
                                      $e->description, $lexem->formNoAccent),
                              'warning');
            $e->description = $lexem->formNoAccent;
            $e->save();
          }
          foreach ($e->getTrees() as $t) {
            if ($t->description == $original->formNoAccent) {
              FlashMessage::add(sprintf('Am redenumit un arbore din „%s” în „%s”.',
                                        $t->description, $lexem->formNoAccent),
                                'warning');
              $t->description = $lexem->formNoAccent;
              $t->save();
            }
          }
        }
      }

      Log::notice("Saved lexem {$lexem->id} ({$lexem->formNoAccent})");
      Util::redirect("lexemEdit.php?lexemId={$lexem->id}");
    }
  } else {
    // Case 2: Validation failed
  }

  // Case 1-2: Page was submitted
  SmartyWrap::assign('renameRelated', $renameRelated);

} else {
  // Case 3: First time loading this page
  $lexem->loadInflectedFormMap();

  RecentLink::add("Lexem: $lexem (ID={$lexem->id})");
}

$definitions = Definition::loadByEntryIds($lexem->getEntryIds());
$searchResults = SearchResult::mapDefinitionArray($definitions);

$canEdit = [
  'general' => User::can(User::PRIV_EDIT),
  'description' => User::can(User::PRIV_EDIT),
  'form' => !$lexem->isLoc || User::can(User::PRIV_LOC),
  'hyphenations' => User::can(User::PRIV_STRUCT | User::PRIV_EDIT),
  'loc' => (int)User::can(User::PRIV_LOC),
  'paradigm' => User::can(User::PRIV_EDIT),
  'pronunciations' => User::can(User::PRIV_STRUCT | User::PRIV_EDIT),
  'sources' => User::can(User::PRIV_LOC | User::PRIV_EDIT),
  'stopWord' => User::can(User::PRIV_ADMIN),
  'tags' => User::can(User::PRIV_LOC | User::PRIV_EDIT),
];

// Prepare a list of model numbers, to be used in the paradigm drop-down.
$models = FlexModel::loadByType($lexem->modelType);

$homonyms = Model::factory('Lexem')
          ->where('formNoAccent', $lexem->formNoAccent)
          ->where_not_equal('id', $lexem->id)
          ->find_many();

$frequentTags = Tag::getFrequent(ObjectTag::TYPE_LEXEM, 4);

SmartyWrap::assign('lexem', $lexem);
SmartyWrap::assign('homonyms', $homonyms);
SmartyWrap::assign('searchResults', $searchResults);
SmartyWrap::assign('modelTypes', Model::factory('ModelType')->order_by_asc('code')->find_many());
SmartyWrap::assign('models', $models);
SmartyWrap::assign('canEdit', $canEdit);
SmartyWrap::assign('frequentTags', $frequentTags);
SmartyWrap::addCss('paradigm', 'admin');
SmartyWrap::addJs('select2Dev', 'modelDropdown');
SmartyWrap::display('admin/lexemEdit.tpl');

/**************************************************************************/

// Populate lexem fields from request parameters.
function populate(&$lexem, &$original, $lexemForm, $lexemNumber, $lexemDescription, $lexemComment,
                  $needsAccent, $main, $stopWord, $hyphenations, $pronunciations, $entryIds,
                  $compound, $modelType, $modelNumber, $restriction, $compoundModelType,
                  $compoundRestriction, $partIds, $declensions, $capitalized, $notes, $isLoc,
                  $sourceIds, $tagIds) {
  $lexem->setForm(AdminStringUtil::formatLexem($lexemForm));
  $lexem->number = $lexemNumber;
  $lexem->description = AdminStringUtil::internalize($lexemDescription, false);
  $lexem->comment = trim(AdminStringUtil::internalize($lexemComment, false));
  // Sign appended comments
  if (StringUtil::startsWith($lexem->comment, $original->comment) &&
      $lexem->comment != $original->comment &&
      !StringUtil::endsWith($lexem->comment, ']]')) {
    $lexem->comment .= " [[" . User::getActive() . ", " . strftime("%d %b %Y %H:%M") . "]]";
  }
  $lexem->noAccent = !$needsAccent;
  $lexem->main = $main;
  $lexem->stopWord = $stopWord;
  $lexem->hyphenations = $hyphenations;
  $lexem->pronunciations = $pronunciations;

  $lexem->compound = $compound;
  $lexem->notes = $notes;
  $lexem->isLoc = $isLoc;

  if ($compound) {
    $lexem->modelType = $compoundModelType;
    $lexem->modelNumber = 0;
    $lexem->restriction = $compoundRestriction;
    // create Fragments
    $fragments = [];
    foreach ($partIds as $i => $partId) {
      $fragments[] = Fragment::create($partId, $declensions[$i], $capitalized[$i], $i);
    }
    $lexem->setFragments($fragments);
  } else {
    $lexem->modelType = $modelType;
    $lexem->modelNumber = $modelNumber;
    $lexem->restriction = $restriction;

    $autoTypes = Config::get('tags.lexemeAutoType', []);
    foreach ($autoTypes as $at) {
      list($fromModelType, $toModelType, $tagValue) = explode('|', $at);
      $tag = Tag::get_by_value($tagValue);
      if (($lexem->modelType == $fromModelType) &&
          in_array($tag->id, $tagIds)) {
        $lexem->modelType = $toModelType;
      }
    }
  }

  // create EntryLexems
  $entryLexems = [];
  foreach ($entryIds as $entryId) {
    $entryLexems[] = EntryLexem::create($entryId, null);
  }
  $lexem->setEntryLexems($entryLexems);

  // create LexemSources
  $lexemSources = [];
  foreach ($sourceIds as $sourceId) {
    $lexemSources[] = LexemSource::create(null, $sourceId);
  }
  $lexem->setLexemSources($lexemSources);

  // create ObjectTags
  $objectTags = [];
  foreach ($tagIds as $tagId) {
    $ot = Model::factory('ObjectTag')->create();
    $ot->objectType = ObjectTag::TYPE_LEXEM;
    $ot->tagId = $tagId;
    $objectTags[] = $ot;
  }
  $lexem->setObjectTags($objectTags);

  try {
    $lexem->generateInflectedFormMap();
  } catch (ParadigmException $pe) {
    FlashMessage::add($pe->getMessage());
  }
}

function validate($lexem, $original) {
  if (!$lexem->form) {
    FlashMessage::add('Forma nu poate fi vidă.');
  }

  $numAccents = mb_substr_count($lexem->form, "'");
  // Note: we allow multiple accents for lexems like hárcea-párcea
  if ($numAccents && $lexem->noAccent) {
    FlashMessage::add('Ați indicat că lexemul nu necesită accent, dar forma conține un accent.');
  } else if (!$numAccents && !$lexem->noAccent) {
    FlashMessage::add('Adăugați un accent sau debifați câmpul „Necesită accent”.');
  }

  // Gather all different restriction - model type pairs
  $pairs = Model::factory('ConstraintMap')
         ->table_alias('cm')
         ->select_expr('binary cm.code', 'restr')
         ->select('mt.code', 'modelType')
         ->distinct()
         ->join('Inflection', ['cm.inflectionId', '=', 'i.id'], 'i')
         ->join('ModelType', ['i.modelType', '=', 'mt.canonical'], 'mt')
         ->find_array();
  $restrMap = [];
  foreach ($pairs as $p) {
    $restrMap[$p['restr']][$p['modelType']] = true;
  }

  for ($i = 0; $i < mb_strlen($lexem->restriction); $i++) {
    $c = StringUtil::getCharAt($lexem->restriction, $i);
    if (!isset($restrMap[$c])) {
      FlashMessage::add("Restricția <strong>$c</strong> este nedefinită.");
    } else if (!isset($restrMap[$c][$lexem->modelType])) {
      FlashMessage::add("Restricția <strong>$c</strong> nu se aplică modelului <strong>{$lexem->modelType}.</strong>");
    }
  }
  
  try {
    $ifs = $lexem->generateInflectedForms();
  } catch (ParadigmException $pe) {
    FlashMessage::add($pe->getMessage());
  }

  return !FlashMessage::hasErrors();
}

?>
