<?php
require_once("../../phplib/Core.php"); 
User::mustHave(User::PRIV_EDIT | User::PRIV_STRUCT);
Util::assertNotMirror();

// Lexeme parameters
$lexemeId = Request::get('lexemeId');
$lexemeForm = Request::get('lexemeForm');
$lexemeNumber = Request::get('lexemeNumber');
$lexemeDescription = Request::get('lexemeDescription');
$needsAccent = Request::has('needsAccent');
$stopWord = Request::has('stopWord');
$hyphenations = Request::get('hyphenations');
$pronunciations = Request::get('pronunciations');
$entryIds = Request::getArray('entryIds');
$tagIds = Request::getArray('tagIds');
$renameRelated = Request::has('renameRelated');

// Paradigm parameters
$compound = Request::has('compound');
$sourceIds = Request::getArray('sourceIds');
$notes = Request::get('notes');
$isLoc = Request::has('isLoc');

// Simple lexeme parameters
$modelType = Request::get('modelType');
$modelNumber = Request::get('modelNumber');
$restriction = Request::get('restriction');

// Compound lexeme parameters
$compoundModelType = Request::get('compoundModelType');
$compoundRestriction = Request::get('compoundRestriction');
$partIds = Request::getArray('partIds');
$declensions = Request::getArray('declensions');
$capitalized = Request::get('capitalized');

// Button parameters
$refreshButton = Request::has('refreshButton');
$saveButton = Request::has('saveButton');
$cloneButton = Request::has('cloneButton');
$deleteButton = Request::has('deleteButton');

$lexeme = Lexeme::get_by_id($lexemeId);
$original = Lexeme::get_by_id($lexemeId); // Keep a copy so we can test whether certain fields have changed

if ($cloneButton) {
  $newLexeme = $lexeme->_clone();
  Log::notice("Cloned lexeme {$lexeme->id} ({$lexeme->formNoAccent}), new id is {$newLexem->id}");
  Util::redirect("lexemEdit.php?lexemeId={$newLexem->id}");
}

if ($deleteButton) {
  $homonym = Model::factory('Lexeme')
           ->where('formNoAccent', $lexeme->formNoAccent)
           ->where_not_equal('id', $lexeme->id)
           ->find_one();
  $lexeme->delete();
  if ($homonym) {
    FlashMessage::add('Am șters lexemul și v-am redirectat la unul dintre omonime.', 'success');
    Util::redirect("?lexemeId={$homonym->id}");
  } else {
    FlashMessage::add('Am șters lexemul.', 'success');
    Util::redirect('index.php');
  }
}

if ($refreshButton || $saveButton) {
  populate($lexem, $original, $lexemeForm, $lexemeNumber, $lexemeDescription,
           $needsAccent, $stopWord, $hyphenations, $pronunciations,
           $compound, $modelType, $modelNumber, $restriction, $compoundModelType,
           $compoundRestriction, $partIds, $declensions, $capitalized, $notes, $isLoc,
           $tagIds);

  if (validate($lexem, $original)) {
    // Case 1: Validation passed
    if ($saveButton) {
      if (($original->modelType == 'VT') && ($lexeme->modelType != 'VT')) {
        $original->deleteParticiple();
      }
      if (in_array($original->modelType, ['V', 'VT']) &&
          !in_array($lexeme->modelType, ['V', 'VT'])) {
        $original->deleteLongInfinitive();
      }
      $lexeme->deepSave();
      $lexeme->regenerateDependentLexemes();
      LexemeSource::update($lexeme->id, $sourceIds);
      EntryLexeme::update($entryIds, $lexeme->id);

      if ($renameRelated) {
        // Grab all the entries
        foreach ($lexeme->getEntries() as $e) {
          if ($e->description == $original->formNoAccent) {
            FlashMessage::addTemplate('entryRenamed.tpl', [
              'entry' => $e,
              'newDescription' => $lexeme->formNoAccent,
            ], 'warning');
            $e->description = $lexeme->formNoAccent;
            $e->save();
          }
          foreach ($e->getTrees() as $t) {
            if ($t->description == $original->formNoAccent) {
              FlashMessage::addTemplate('treeRenamed.tpl', [
                't' => $t,
                'newDescription' => $lexeme->formNoAccent,
              ], 'warning');
              $t->description = $lexeme->formNoAccent;
              $t->save();
            }
          }
        }
      }

      Log::notice("Saved lexeme {$lexeme->id} ({$lexeme->formNoAccent})");
      Util::redirect("lexemEdit.php?lexemeId={$lexeme->id}");
    }
  } else {
    // Case 2: Validation failed
  }

  // Case 1-2: Page was submitted
  SmartyWrap::assign('renameRelated', $renameRelated);

} else {
  // Case 3: First time loading this page
  $lexeme->loadInflectedFormMap();
  $sourceIds = $lexeme->getSourceIds();
  $entryIds = $lexeme->getEntryIds();

  RecentLink::add("Lexem: $lexeme (ID={$lexeme->id})");
}

$definitions = Definition::loadByEntryIds($entryIds);
$searchResults = SearchResult::mapDefinitionArray($definitions);

$canEdit = [
  'general' => User::can(User::PRIV_EDIT),
  'description' => User::can(User::PRIV_EDIT),
  'form' => !$lexeme->isLoc || User::can(User::PRIV_LOC),
  'hyphenations' => User::can(User::PRIV_STRUCT | User::PRIV_EDIT),
  'loc' => (int)User::can(User::PRIV_LOC),
  'paradigm' => User::can(User::PRIV_EDIT),
  'pronunciations' => User::can(User::PRIV_STRUCT | User::PRIV_EDIT),
  'sources' => User::can(User::PRIV_LOC | User::PRIV_EDIT),
  'stopWord' => User::can(User::PRIV_ADMIN),
  'tags' => User::can(User::PRIV_LOC | User::PRIV_EDIT),
];

// Prepare a list of model numbers, to be used in the paradigm drop-down.
$models = FlexModel::loadByType($lexeme->modelType);

$homonyms = Model::factory('Lexeme')
          ->where('formNoAccent', $lexeme->formNoAccent)
          ->where_not_equal('id', $lexeme->id)
          ->find_many();

SmartyWrap::assign('lexeme', $lexeme);
SmartyWrap::assign('entryIds', $entryIds);
SmartyWrap::assign('sourceIds', $sourceIds);
SmartyWrap::assign('homonyms', $homonyms);
SmartyWrap::assign('searchResults', $searchResults);
SmartyWrap::assign('modelTypes', Model::factory('ModelType')->order_by_asc('code')->find_many());
SmartyWrap::assign('models', $models);
SmartyWrap::assign('canEdit', $canEdit);
SmartyWrap::addCss('paradigm', 'admin');
SmartyWrap::addJs('select2Dev', 'modelDropdown', 'cookie', 'frequentObjects');
SmartyWrap::display('admin/lexemEdit.tpl');

/**************************************************************************/

// Populate lexeme fields from request parameters.
function populate(&$lexem, &$original, $lexemeForm, $lexemeNumber, $lexemeDescription,
                  $needsAccent, $stopWord, $hyphenations, $pronunciations,
                  $compound, $modelType, $modelNumber, $restriction, $compoundModelType,
                  $compoundRestriction, $partIds, $declensions, $capitalized, $notes, $isLoc,
                  $tagIds) {
  $lexeme->setForm($lexemeForm);
  $lexeme->number = $lexemeNumber;
  $lexeme->description = $lexemeDescription;
  $lexeme->noAccent = !$needsAccent;
  $lexeme->stopWord = $stopWord;
  $lexeme->hyphenations = $hyphenations;
  $lexeme->pronunciations = $pronunciations;

  $lexeme->compound = $compound;
  $lexeme->notes = $notes;
  $lexeme->isLoc = $isLoc;

  if ($compound) {
    $lexeme->modelType = $compoundModelType;
    $lexeme->modelNumber = 0;
    $lexeme->restriction = $compoundRestriction;
    // create Fragments
    $fragments = [];
    foreach ($partIds as $i => $partId) {
      $fragments[] = Fragment::create($partId, $declensions[$i], $capitalized[$i], $i);
    }
    $lexeme->setFragments($fragments);
  } else {
    $lexeme->modelType = $modelType;
    $lexeme->modelNumber = $modelNumber;
    $lexeme->restriction = $restriction;

    // set / clear the model type when the right tag is present / absent
    $autoTypes = Config::get('tags.lexemeAutoType', []);
    foreach ($autoTypes as $at) {
      list($fromModelType, $toModelType, $tagValue) = explode('|', $at);
      $tag = Tag::get_by_value($tagValue);
      if (($lexeme->modelType == $fromModelType) &&
          in_array($tag->id, $tagIds)) {
        $lexeme->modelType = $toModelType;
      } else if (($lexeme->modelType == $toModelType) &&
          !in_array($tag->id, $tagIds)) {
        $lexeme->modelType = $fromModelType;
      }
    }
  }

  // create ObjectTags
  $objectTags = [];
  foreach ($tagIds as $tagId) {
    $ot = Model::factory('ObjectTag')->create();
    $ot->objectType = ObjectTag::TYPE_LEXEM;
    $ot->tagId = $tagId;
    $objectTags[] = $ot;
  }
  $lexeme->setObjectTags($objectTags);

  try {
    $lexeme->generateInflectedFormMap();
  } catch (ParadigmException $pe) {
    FlashMessage::add($pe->getMessage());
  }
}

function validate($lexem, $original) {
  if (!$lexeme->form) {
    FlashMessage::add('Forma nu poate fi vidă.');
  }

  $numAccents = mb_substr_count($lexeme->form, "'");
  // Note: we allow multiple accents for lexemes like hárcea-párcea
  if ($numAccents && $lexeme->noAccent) {
    FlashMessage::add('Ați indicat că lexemul nu necesită accent, dar forma conține un accent.');
  } else if (!$numAccents && !$lexeme->noAccent) {
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

  for ($i = 0; $i < mb_strlen($lexeme->restriction); $i++) {
    $c = Str::getCharAt($lexeme->restriction, $i);
    if (!isset($restrMap[$c])) {
      FlashMessage::add("Restricția <strong>$c</strong> este nedefinită.");
    } else if (!isset($restrMap[$c][$lexeme->modelType])) {
      FlashMessage::add("Restricția <strong>$c</strong> nu se aplică modelului <strong>{$lexeme->modelType}.</strong>");
    }
  }
  
  try {
    $ifs = $lexeme->generateInflectedForms();
  } catch (ParadigmException $pe) {
    FlashMessage::add($pe->getMessage());
  }

  return !FlashMessage::hasErrors();
}
