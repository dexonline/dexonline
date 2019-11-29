<?php

User::mustHave(User::PRIV_EDIT | User::PRIV_STRUCT);

// Lexeme parameters
$lexemeId = Request::get('lexemeId');
$lexemeForm = Request::getWithApostrophes('lexemeForm');
$lexemeNumber = Request::get('lexemeNumber');
$lexemeDescription = Request::get('lexemeDescription');
$needsAccent = Request::has('needsAccent');
$stopWord = Request::has('stopWord');
$hyphenations = Request::get('hyphenations');
$pronunciations = Request::get('pronunciations');
$entryIds = Request::getArray('entryIds');  // multidimensional $array[$keyBooleanMainLexeme][$realEntryIds]
$tagIds = Request::getArray('tagIds');
$renameRelated = Request::has('renameRelated');

// Paradigm parameters
$compound = Request::has('compound');
$sourceIds = Request::getArray('sourceIds');
$notes = Request::get('notes');
$hasApheresis = Request::has('hasApheresis');
$hasApocope = Request::has('hasApocope');

// Simple lexeme parameters
$modelType = Request::get('modelType');
$modelNumber = Request::get('modelNumber');
$restriction = Request::get('restriction');

// Compound lexeme parameters
$partIds = Request::getArray('partIds');
$declensions = Request::getArray('declensions');
$capitalized = Request::get('capitalized');
$accented = Request::get('accented');

// Button parameters
$refreshButton = Request::has('refreshButton');
$saveButton = Request::has('saveButton');
$cloneButton = Request::has('cloneButton');
$deleteButton = Request::has('deleteButton');

$lexeme = Lexeme::get_by_id($lexemeId);
$original = Lexeme::get_by_id($lexemeId); // Keep a copy so we can test whether certain fields have changed

if ($cloneButton) {
  $cloneEntries = Request::getArray('cloneEntries');
  $cloneInflectedForms = Request::has('cloneInflectedForms');
  $cloneTags = Request::has('cloneTags');
  $cloneSources = Request::has('cloneSources');

  $newLexeme = $lexeme->_clone($cloneEntries, $cloneInflectedForms, $cloneTags, $cloneSources );
  Log::notice("Cloned lexeme {$lexeme->id} ({$lexeme->formNoAccent}), new id is {$newLexeme->id}");
  FlashMessage::add('Am clonat lexemul. Aceasta este pagina clonei.', 'success');
  Util::redirect(Router::link("lexeme/edit")."/{$newLexeme->id}");
}

if ($deleteButton) {
  $homonym = Model::factory('Lexeme')
           ->where('formNoAccent', $lexeme->formNoAccent)
           ->where_not_equal('id', $lexeme->id)
           ->find_one();
  $lexeme->delete();
  if ($homonym) {
    FlashMessage::add('Am șters lexemul și v-am redirecționat la unul dintre omonime.', 'success');
    Util::redirect(Router::link("lexeme/edit")."/{$homonym->id}");
  } else {
    FlashMessage::add('Am șters lexemul.', 'success');
    Util::redirectToRoute('aggregate/dashboard');
  }
}

if ($refreshButton || $saveButton) {
  populate($lexeme, $lexemeForm, $lexemeNumber, $lexemeDescription,
           $needsAccent, $stopWord, $hyphenations, $pronunciations,
           $compound, $modelType, $modelNumber, $restriction,
           $partIds, $declensions, $capitalized, $accented,
           $notes, $hasApheresis, $hasApocope, $tagIds);

  if ($lexeme->validate()) {
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
      $lexeme->harmonizeTags();
      LexemeSource::update($lexeme->id, $sourceIds);

      updateEntries($lexeme->id, $entryIds);

      if ($renameRelated) {
        // Grab all the entries
        foreach ($lexeme->getEntries() as $e) {
          if ($e->description == $original->formNoAccent) {
            $e->description = $lexeme->formNoAccent;
            $e->save();
            FlashMessage::addTemplate('entryRenamed.tpl', [
              'entry' => $e,
              'oldDescription' => $original->formNoAccent,
            ], 'warning');

          }
          foreach ($e->getTrees() as $t) {
            if ($t->description == $original->formNoAccent) {
              $t->description = $lexeme->formNoAccent;
              $t->save();
              FlashMessage::addTemplate('treeRenamed.tpl', [
                't' => $t,
                'oldDescription' => $original->formNoAccent,
              ], 'warning');

            }
          }
        }
      }

      Log::notice("Saved lexeme {$lexeme->id} ({$lexeme->formNoAccent})");
      FlashMessage::add('Am salvat lexemul.', 'success');
    }
  } else {
    // Case 2: Validation failed
  }

  // Case 1-2: Page was submitted
  Smart::assign('renameRelated', $renameRelated);

}

  // Case 3: First time loading this page
$lexeme->loadInflectedFormMap();
$sourceIds = $lexeme->getSourceIds();

// retrieving not aggregated entryIds, grouped according to lexeme associations
foreach (Lexeme::ASSOC_ENTRY as $key => $ignored) {
  $entryIds[$key] = $lexeme->getEntryIds(['main' => $key]);
}

if (!$refreshButton) {
  RecentLink::add("Lexem: $lexeme (ID={$lexeme->id})");
}

$compoundIds = $lexeme->getCompoundsFromPart();

// retrieving not aggregated definitions, grouped according to entry association
foreach (Lexeme::ASSOC_ENTRY as $key => $ignored) {
  $searchResults[$key] = SearchResult::mapDefinitionArray(Definition::loadByEntryIds($entryIds[$key]));
}

$canEdit = [
  'general' => User::can(User::PRIV_EDIT),
  'description' => User::can(User::PRIV_EDIT),
  'form' => User::can(User::PRIV_EDIT),
  'hyphenations' => User::can(User::PRIV_STRUCT | User::PRIV_EDIT),
  'paradigm' => User::can(User::PRIV_EDIT),
  'pronunciations' => User::can(User::PRIV_STRUCT | User::PRIV_EDIT),
  'sources' => User::can(User::PRIV_EDIT),
  'stopWord' => User::can(User::PRIV_ADMIN),
  'tags' => User::can(User::PRIV_EDIT),
];

// Prepare a list of model numbers, to be used in the paradigm drop-down.
// TODO - seems to be unused
$models = FlexModel::loadByType($lexeme->modelType);

$homonyms = Model::factory('Lexeme')
          ->where('formNoAccent', $lexeme->formNoAccent)
          ->where_not_equal('id', $lexeme->id)
          ->find_many();

$modelTypes = new ModelTypeDropdown('getAll', [ 'selectedValue' => $lexeme->modelType ]);
$modelNumbers = new ModelNumberDropdown('loadByType', $lexeme->modelType, [ 'selectedValue' => $lexeme->modelNumber, 'compoundLexeme' => $lexeme->compound ]);

Smart::assign([
  'lexeme' => $lexeme,
  'entryIds' => $entryIds,
  'sourceIds' => $sourceIds,
  'compoundIds' => $compoundIds,
  'homonyms' => $homonyms,
  'searchResults' => $searchResults,
  'modelTypes' => (array)$modelTypes,
  'modelNumbers' => (array)$modelNumbers,
  'models' => $models, // TODO - seems to be unused
  'canEdit' => $canEdit,
  'assocEntry' => Lexeme::ASSOC_ENTRY,
]);
Smart::addResources('paradigm', 'admin', 'frequentObjects',
                    'select2Dev', 'modelDropdown', 'scrollTop', 'ldring', 'bulkCheckbox');
Smart::display('lexeme/edit.tpl');

/**************************************************************************/

// Populate lexeme fields from request parameters.
function populate(&$lexeme, $lexemeForm, $lexemeNumber, $lexemeDescription,
                  $needsAccent, $stopWord, $hyphenations, $pronunciations,
                  $compound, $modelType, $modelNumber, $restriction,
                  $partIds, $declensions, $capitalized, $accented,
                  $notes, $hasApheresis, $hasApocope, $tagIds) {
  $lexeme->setForm($lexemeForm);
  $lexeme->number = $lexemeNumber;
  $lexeme->description = $lexemeDescription;
  $lexeme->noAccent = !$needsAccent;
  $lexeme->stopWord = $stopWord;
  $lexeme->hyphenations = $hyphenations;
  $lexeme->pronunciations = $pronunciations;

  $lexeme->compound = $compound;
  $lexeme->notes = $notes;
  $lexeme->hasApheresis = $hasApheresis;
  $lexeme->hasApocope = $hasApocope;
  $lexeme->modelType = $modelType;

  if ($compound) {
    $lexeme->modelNumber = 0;
    // create Fragments
    $fragments = [];
    foreach ($partIds as $i => $partId) {
      $fragments[] = Fragment::create(
        $partId, $declensions[$i], $capitalized[$i], $accented[$i], $i);
    }
    $lexeme->setFragments($fragments);
  } else {
    $lexeme->modelNumber = $modelNumber;
    $lexeme->restriction = $restriction;
    $lexeme->harmonizeModel($tagIds); // TODO  - not sure if this is the right way
  }

  // create ObjectTags
  $objectTags = [];
  foreach ($tagIds as $tagId) {
    $ot = Model::factory('ObjectTag')->create();
    $ot->objectType = ObjectTag::TYPE_LEXEME;
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

// create new entries for $entryIds starting with '@', then update the associations
function updateEntries($lexemeId, $entryIds) {
  // In case of deletions $entryIds may have empty arrays submitted.
  // Set them, according to Lexeme::ASSOC_ENTRY, for EntryLexeme::update below
  // iterating through multidimensional $entryIds[$keyBooleanMainLexeme][$realEntryIds]
  foreach (Lexeme::ASSOC_ENTRY as $main => $ignored) {
    $entries = [];
    if (isset($entryIds[$main])) {
      foreach ($entryIds[$main] as $key => $entryId) {
        if (Str::startsWith($entryId, '@')) {
          // create a new entry
          $form = substr($entryId, 1);
          $e = Entry::createAndSave($form, true);
        }
        else {
          $e = Entry::get_by_id($entryId);
        }
        $entries[] = $e;
      }
    }
    EntryLexeme::update(Util::objectProperty($entries, 'id'), $lexemeId, ['main' => $main]);
  }

}
