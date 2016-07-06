<?php
require_once("../phplib/util.php");

util_assertModerator(PRIV_EDIT | PRIV_STRUCT);

$id = util_getRequestParameter('id');
$save = util_getRequestParameter('save') !== null;
$delete = util_getRequestParameter('delete') !== null;
$dissociateDefinitionId = util_getRequestParameter('dissociateDefinitionId');

if ($id) {
  $e = Entry::get_by_id($id);
  if (!$e) {
    FlashMessage::add(_('Intrarea nu există.'));
    util_redirect(util_getWwwRoot());
  }
} else {
  $e = Model::factory('Entry')->create();
}

if ($dissociateDefinitionId) {
  EntryDefinition::dissociate($e->id, $dissociateDefinitionId);
  Log::info("Dissociated lexem {$e->id} ({$e->description}) from definition {$dissociateDefinitionId}");
  util_redirect("?id={$e->id}");
}

if ($delete) {
  $e->delete();
  FlashMessage::add('Am șters intrarea.', 'success');
  util_redirect(util_getWwwRoot());
}

if ($save) {
  $e->description = util_getRequestParameter('description');
  $lexemIds = util_getRequestParameter('lexemIds');
  $treeIds = util_getRequestParameter('treeIds');

  $errors = $e->validate();
  if ($errors) {
    SmartyWrap::assign('errors', $errors);
  } else {
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

$definitions = Definition::loadByEntryId($e->id);
foreach ($definitions as $def) {
  $def->internalRepAbbrev = AdminStringUtil::expandAbbreviations($def->internalRep, $def->sourceId);
  $def->htmlRepAbbrev = AdminStringUtil::htmlize($def->internalRepAbbrev, $def->sourceId);
}
$searchResults = SearchResult::mapDefinitionArray($definitions);

SmartyWrap::assign('e', $e);
SmartyWrap::assign('searchResults', $searchResults);
SmartyWrap::assign('lexemIds', $lexemIds);
SmartyWrap::assign('treeIds', $treeIds);
SmartyWrap::assign('suggestNoBanner', true);
SmartyWrap::assign('suggestHiddenSearchForm', true);
SmartyWrap::addCss('bootstrap', 'select2', 'meaningTree');
SmartyWrap::addJs('bootstrap', 'select2', 'select2Dev', 'meaningTree', 'textComplete');
SmartyWrap::display('editEntry.tpl');

?>
