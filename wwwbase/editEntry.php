<?php
require_once("../phplib/util.php");

util_assertModerator(PRIV_EDIT | PRIV_STRUCT);

$id = util_getRequestParameter('id');
$saveButton = util_getBoolean('saveButton');
$createTree = util_getBoolean('createTree');
$delete = util_getBoolean('delete');
$dissociateDefinitionId = util_getRequestParameter('dissociateDefinitionId');

if ($id) {
  $e = Entry::get_by_id($id);
  if (!$e) {
    FlashMessage::add('Intrarea nu există.');
    util_redirect(util_getWwwRoot());
  }
  // Keep a copy so we can test whether certain fields have changed
  $original = Entry::get_by_id($id);
} else {
  $e = Model::factory('Entry')->create();
  $original = Model::factory('Entry')->create();
}

if ($dissociateDefinitionId) {
  EntryDefinition::dissociate($e->id, $dissociateDefinitionId);
  Log::info("Dissociated lexem {$e->id} ({$e->description}) from definition {$dissociateDefinitionId}");
  util_redirect("?id={$e->id}");
}

if ($createTree) {
  if (!$id) {
    FlashMessage::add('Nu puteți crea un arbore de sensuri înainte să salvați intrarea.');
    util_redirect(util_getWwwRoot());
  }
  $t = Tree::createAndSave($e->description . " (NOU)");
  TreeEntry::associate($t->id, $e->id);
  FlashMessage::add("Am creat un arbore de sensuri pentru {$e->description}.", 'success');
  util_redirect("editTree.php?id={$t->id}");
}

if ($delete) {
  $e->delete();
  FlashMessage::add('Am șters intrarea.', 'success');
  util_redirect(util_getWwwRoot());
}

if ($saveButton) {
  $e->description = util_getRequestParameter('description');
  $e->structStatus = util_getRequestIntParameter('structStatus');
  $e->structuristId = util_getRequestIntParameter('structuristId');
  $lexemIds = util_getRequestParameter('lexemIds');
  $treeIds = util_getRequestParameter('treeIds');

  $errors = $e->validate($original);
  if ($errors) {
    SmartyWrap::assign('errors', $errors);
  } else {
    // Possibly overwrite the structuristId according to the structStatus change
    if (($original->structStatus == Entry::STRUCT_STATUS_NEW) &&
        ($e->structStatus == Entry::STRUCT_STATUS_IN_PROGRESS)) {
      $e->structuristId = session_getUserId();
    }

    $e->save();

    // dissociate the entry from the old lexems
    foreach ($e->getLexems() as $l) {
      $l->entryId = null;
      $l->save();
    }

    // associate the entry with the new lexems
    foreach ($lexemIds as $id) {
      $l = Lexem::get_by_id($id);
      $l->entryId = $e->id;
      $l->save();
    }

    // dissociate the entry from the old trees
    TreeEntry::delete_all_by_entryId($e->id);

    // associate the entry with the new trees
    foreach ($treeIds as $tid) {
      TreeEntry::associate($tid, $e->id);
    }

    FlashMessage::add('Am salvat intrarea.', 'success');
    util_redirect("?id={$e->id}");
  }
} else {
  // Viewing the page, not saving
  $lexemIds = $e->getLexemIds();
  $treeIds = $e->getTreeIds();
  $e->loadMeanings();
}

// Load the distinct model types for the entry's lexems
$modelTypes = [];
foreach ($lexemIds as $lexemId) {
  $l = Lexem::get_by_id($lexemId);
  $modelTypes[] = $l->modelType;
}
$modelTypes = array_unique($modelTypes);

$definitions = Definition::loadByEntryId($e->id);
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
  || util_isModerator(PRIV_EDIT),
  'structuristId' => util_isModerator(PRIV_ADMIN),
];

SmartyWrap::assign('e', $e);
SmartyWrap::assign('searchResults', $searchResults);
SmartyWrap::assign('lexemIds', $lexemIds);
SmartyWrap::assign('treeIds', $treeIds);
SmartyWrap::assign('modelTypes', $modelTypes);
SmartyWrap::assign('canEdit', $canEdit);
SmartyWrap::assign('structStatusNames', Entry::$STRUCT_STATUS_NAMES);
SmartyWrap::addCss('select2', 'meaningTree', 'admin');
SmartyWrap::addJs('select2', 'select2Dev', 'meaningTree', 'textComplete');
SmartyWrap::display('editEntry.tpl');

?>
