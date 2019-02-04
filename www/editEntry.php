<?php
require_once '../lib/Core.php';

User::mustHave(User::PRIV_EDIT | User::PRIV_STRUCT);

$id = Request::get('id');
$saveButton = Request::has('saveButton');
$mergeButton = Request::has('mergeButton');
$cloneButton = Request::has('cloneButton');
$associateButton = Request::has('associateButton');
$dissociateButton = Request::has('dissociateButton');
$createTree = Request::has('createTree');
$deleteTreeId = Request::get('deleteTreeId');
$delete = Request::has('delete');
$deleteExt = Request::has('deleteExt');

if ($id) {
  $e = Entry::get_by_id($id);
  if (!$e) {
    FlashMessage::add('Intrarea nu există.');
    Util::redirectToHome();
  }
  // Keep a copy so we can test whether certain fields have changed
  $original = Entry::get_by_id($id);
} else {
  $e = Model::factory('Entry')->create();
  $original = Model::factory('Entry')->create();
}

if ($associateButton) {
  $defIds = Request::get('associateDefinitionIds');
  $defIds = array_filter(explode(',', $defIds));
  $entryIds = Request::getArray('associateEntryIds');
  foreach ($defIds as $defId) {
    foreach ($entryIds as $entryId) {
      EntryDefinition::associate($entryId, $defId);
      Log::info("Associated entry {$entryId} with definition {$defId}");
    }
  }
  FlashMessage::add(sprintf('Am asociat %d definiții cu %d intrări.',
                            count($defIds), count($entryIds)),
                    'success');
  Util::redirect("?id={$e->id}");
}

if ($dissociateButton) {
  $defIds = Request::getArray('selectedDefIds');
  foreach ($defIds as $defId) {
    EntryDefinition::dissociate($e->id, $defId);
    Log::info("Dissociated entry {$e->id} ({$e->description}) from definition {$defId}");
  }
  Util::redirect("?id={$e->id}");
}

if ($mergeButton) {
  $mergeEntryId = Request::get('mergeEntryId');
  $other = Entry::get_by_id($mergeEntryId);

  if (!$other) {
    FlashMessage::add('Intrarea selectată nu există.');
    Util::redirect("?id={$e->id}");
  } else if (!$e->id) {
    FlashMessage::add('Nu puteți face unificarea la momentul creării.');
    Util::redirectToHome();
  } else if ($other->id == $e->id) {
    FlashMessage::add('Nu puteți unifica intrarea cu ea însăși (serios!).');
    Util::redirect("?id={$e->id}");
  }

  $e->mergeInto($other->id);
  $other->deleteTemporaryLexemes();

  FlashMessage::add('Am unificat intrările.', 'success');
  Util::redirect("?id={$other->id}");
}

if ($cloneButton) {
  $cloneDefinitions = Request::has('cloneDefinitions');
  $cloneLexemes = Request::has('cloneLexemes');
  $cloneTrees = Request::has('cloneTrees');
  $cloneStructurist = Request::has('cloneStructurist');

  $newe = $e->_clone($cloneDefinitions, $cloneLexemes, $cloneTrees, $cloneStructurist);
  Log::info("Cloned entry {$e->id} ({$e->description}), new id {$newe->id}");
  FlashMessage::add('Am clonat intrarea.', 'success');
  Util::redirect("?id={$newe->id}");
}

if ($createTree) {
  if (!$id) {
    FlashMessage::add('Nu puteți crea un arbore de sensuri înainte să salvați intrarea.');
    Util::redirectToHome();
  }
  $t = Tree::createAndSave($e->description);
  TreeEntry::associate($t->id, $e->id);
  FlashMessage::add('Am creat un arbore de sensuri.', 'success');
  Util::redirect("?id={$e->id}");
}

if ($deleteTreeId) {
  $t = Tree::get_by_id($deleteTreeId);
  if (!$t) {
    FlashMessage::add("Arborele cu ID-ul {$deleteTreeId} nu există.", 'danger');
  } else if ($t->hasMeanings()) {
    FlashMessage::add("Arborele cu ID-ul {$deleteTreeId} nu este gol.", 'danger');
  } else {
    $t->delete();
    FlashMessage::add('Am șters arborele.', 'success');
  }
  Util::redirect("?id={$e->id}");
}

if ($delete) {
  $e->delete();
  FlashMessage::add('Am șters intrarea.', 'success');
  Util::redirectToHome();
}

// Delete the entry, its T1 lexemes and its empty trees.
if ($deleteExt) {
  foreach ($e->getLexemes() as $l) {
    if (($l->modelType == 'T') &&
        ($l->canDelete() == Lexeme::CAN_DELETE_OK)) {
      $l->delete();
    }
  }
  foreach ($e->getTrees() as $t) {
    if (!$t->hasMeanings()) {
      $t->delete();
    }
  }

  $e->delete();
  FlashMessage::add('Am șters intrarea extinsă.', 'success');
  Util::redirectToHome();
}

if ($saveButton) {
  $e->description = Request::getWithApostrophes('description');
  $e->structStatus = Request::get('structStatus');
  $e->structuristId = Request::get('structuristId');
  $e->adult = Request::has('adult');
  $mainLexemeIds = Request::getArray('mainLexemeIds');
  $variantLexemeIds = Request::getArray('variantLexemeIds');
  $treeIds = Request::getArray('treeIds');
  $renameTrees = Request::has('renameTrees');
  $tagIds = Request::getArray('tagIds');

  $errors = $e->validate($original);
  if ($errors) {
    Smart::assign('errors', $errors);
    Smart::assign('renameTrees', $renameTrees);
  } else {
    // Possibly overwrite the structuristId according to the structStatus change
    if (($original->structStatus == Entry::STRUCT_STATUS_NEW) &&
        ($e->structStatus == Entry::STRUCT_STATUS_IN_PROGRESS)) {
      $e->structuristId = User::getActiveId();
    }
    // Make ourselves the structurist if we are in structurist mode.
    // However, allow us to remove ourselves from the entry if we wish.
    if (!$e->structuristId && !$original->structuristId && Session::isStructureMode()) {
      $e->structuristId = User::getActiveId();
    }

    $e->save();

    // dissociate old lexemes, trees and tags and associate new ones
    EntryLexeme::update($e->id, $mainLexemeIds, ['main' => true]);
    EntryLexeme::update($e->id, $variantLexemeIds, ['main' => false]);
    TreeEntry::update($treeIds, $e->id);
    ObjectTag::wipeAndRecreate($e->id, ObjectTag::TYPE_ENTRY, $tagIds);

    $e->deleteTemporaryLexemes();

    if ($renameTrees) {
      foreach ($e->getTrees() as $t) {
        $t->description = $e->description;
        $t->save();
      }
    }

    FlashMessage::add('Am salvat intrarea.', 'success');
    Util::redirect("?id={$e->id}");
  }
} else {
  // Viewing the page, not saving
  $mainLexemeIds = $e->getMainLexemeIds();
  $variantLexemeIds = $e->getVariantLexemeIds();
  $treeIds = $e->getTreeIds();
  $ots = ObjectTag::getEntryTags($e->id);
  $tagIds = Util::objectProperty($ots, 'tagId');
  $e->loadMeanings();
  RecentLink::add("Intrare: {$e->description} (ID={$e->id})");
}

// Load the distinct model types for the entry's lexemes
$modelTypes = [];
foreach (array_merge($mainLexemeIds, $variantLexemeIds) as $lexemeId) {
  $l = Lexeme::get_by_id($lexemeId);
  $modelTypes[] = $l->modelType;
}
$modelTypes = array_unique($modelTypes);

$definitions = Definition::loadByEntryIds([$e->id]);
foreach ($definitions as $def) {
  $def->internalRepAbbrev = Abbrev::expandAbbreviations($def->internalRep, $def->sourceId);
  list($def->htmlAbbrev, $ignored)
    = Str::htmlize($def->internalRepAbbrev, $def->sourceId);
}
$searchResults = SearchResult::mapDefinitionArray($definitions);

$ss = $e->structStatus;
$oss = $original->structStatus; // syntactic sugar

$canEdit =
  in_array($oss, [ Entry::STRUCT_STATUS_NEW, Entry::STRUCT_STATUS_IN_PROGRESS ]) ||
  User::can(User::PRIV_STRUCT);

$canDelete = ($e->structuristId == User::getActiveId()) || !$e->structuristId;

$homonyms = Entry::getHomonyms([ $e ]);

Smart::assign([
  'e' => $e,
  'searchResults' => $searchResults,
  'mainLexemeIds' => $mainLexemeIds,
  'variantLexemeIds' => $variantLexemeIds,
  'treeIds' => $treeIds,
  'tagIds' => $tagIds,
  'modelTypes' => $modelTypes,
  'canEdit' => $canEdit,
  'canDelete' => $canDelete,
  'homonyms' => $homonyms,
  'structurists' => User::getStructurists($e->structuristId),
]);
Smart::addCss('editableMeaningTree', 'admin');
Smart::addJs('select2Dev', 'meaningTree', 'textComplete');
Smart::display('editEntry.tpl');
