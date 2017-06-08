<?php
require_once("../phplib/Core.php");

User::mustHave(User::PRIV_EDIT | User::PRIV_STRUCT);

$id = Request::get('id');
$saveButton = Request::has('saveButton');
$mergeButton = Request::has('mergeButton');
$cloneButton = Request::has('cloneButton');
$dissociateButton = Request::has('dissociateButton');
$createTree = Request::has('createTree');
$delete = Request::has('delete');
$deleteExt = Request::has('deleteExt');

if ($id) {
  $e = Entry::get_by_id($id);
  if (!$e) {
    FlashMessage::add('Intrarea nu există.');
    Util::redirect(Core::getWwwRoot());
  }
  // Keep a copy so we can test whether certain fields have changed
  $original = Entry::get_by_id($id);
} else {
  $e = Model::factory('Entry')->create();
  $original = Model::factory('Entry')->create();
}

if ($dissociateButton) {
  $defIds = Request::get('dissociateDefinitionIds', []);
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
    Util::redirect(Core::getWwwRoot());
  } else if ($other->id == $e->id) {
    FlashMessage::add('Nu puteți unifica intrarea cu ea însăși (serios!).');
    Util::redirect("?id={$e->id}");
  }

  $e->mergeInto($other->id);

  FlashMessage::add('Am unificat intrările.', 'success');
  Util::redirect("?id={$other->id}");
}

if ($cloneButton) {
  $cloneDefinitions = Request::has('cloneDefinitions');
  $cloneLexems = Request::has('cloneLexems');
  $cloneTrees = Request::has('cloneTrees');

  $newe = $e->_clone($cloneDefinitions, $cloneLexems, $cloneTrees);
  Log::info("Cloned entry {$e->id} ({$e->description}), new id {$newe->id}");
  FlashMessage::add('Am clonat intrarea.', 'success');
  Util::redirect("?id={$newe->id}");
}

if ($createTree) {
  if (!$id) {
    FlashMessage::add('Nu puteți crea un arbore de sensuri înainte să salvați intrarea.');
    Util::redirect(Core::getWwwRoot());
  }
  $t = Tree::createAndSave($e->description);
  TreeEntry::associate($t->id, $e->id);
  FlashMessage::add("Am creat un arbore de sensuri pentru {$e->description}.", 'success');
  Util::redirect("editTree.php?id={$t->id}");
}

if ($delete) {
  $e->delete();
  FlashMessage::add('Am șters intrarea.', 'success');
  Util::redirect(Core::getWwwRoot());
}

// Delete the entry, its T1 lexemes and its empty trees.
if ($deleteExt) {
  foreach ($e->getLexems() as $l) {
    if ($l->modelType == 'T') {
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
  Util::redirect(Core::getWwwRoot());
}

if ($saveButton) {
  $e->description = Request::get('description');
  $e->structStatus = Request::get('structStatus');
  $e->structuristId = Request::get('structuristId');
  $lexemIds = Request::get('lexemIds');
  $treeIds = Request::get('treeIds');
  $renameTrees = Request::has('renameTrees');

  $errors = $e->validate($original);
  if ($errors) {
    SmartyWrap::assign('errors', $errors);
    SmartyWrap::assign('renameTrees', $renameTrees);
  } else {
    // Possibly overwrite the structuristId according to the structStatus change
    if (($original->structStatus == Entry::STRUCT_STATUS_NEW) &&
        ($e->structStatus == Entry::STRUCT_STATUS_IN_PROGRESS)) {
      $e->structuristId = User::getActiveId();
    }

    $e->save();

    // dissociate old lexems and trees and associate new ones
    EntryLexem::wipeAndRecreate($e->id, $lexemIds);
    TreeEntry::wipeAndRecreate($treeIds, $e->id);

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
  $lexemIds = $e->getLexemIds();
  $treeIds = $e->getTreeIds();
  $e->loadMeanings();
  RecentLink::add("Intrare: {$e->description} (ID={$e->id})");
}

// Load the distinct model types for the entry's lexems
$modelTypes = [];
foreach ($lexemIds as $lexemId) {
  $l = Lexem::get_by_id($lexemId);
  $modelTypes[] = $l->modelType;
}
$modelTypes = array_unique($modelTypes);

$definitions = Definition::loadByEntryIds([$e->id]);
foreach ($definitions as $def) {
  $def->internalRepAbbrev = AdminStringUtil::expandAbbreviations($def->internalRep, $def->sourceId);
  $def->htmlRepAbbrev = AdminStringUtil::htmlize($def->internalRepAbbrev, $def->sourceId);
}
$searchResults = SearchResult::mapDefinitionArray($definitions);

$ss = $e->structStatus;
$oss = $original->structStatus; // syntactic sugar

$canEdit = [
  'structStatus' => in_array($oss,
                             [ Entry::STRUCT_STATUS_NEW, Entry::STRUCT_STATUS_IN_PROGRESS ])
  || User::can(User::PRIV_EDIT),
  'structuristId' => User::can(User::PRIV_ADMIN),
];

$homonyms = Entry::getHomonyms([ $e ]);

SmartyWrap::assign('e', $e);
SmartyWrap::assign('searchResults', $searchResults);
SmartyWrap::assign('lexemIds', $lexemIds);
SmartyWrap::assign('treeIds', $treeIds);
SmartyWrap::assign('modelTypes', $modelTypes);
SmartyWrap::assign('canEdit', $canEdit);
SmartyWrap::assign('homonyms', $homonyms);
SmartyWrap::assign('structurists', User::getStructurists($e->structuristId));
SmartyWrap::addCss('editableMeaningTree', 'admin');
SmartyWrap::addJs('select2Dev', 'meaningTree', 'textComplete');
SmartyWrap::display('editEntry.tpl');

?>
