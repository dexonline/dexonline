<?php
require_once("../phplib/util.php");

util_assertModerator(PRIV_EDIT | PRIV_STRUCT);

$id = util_getRequestParameter('id');
$save = util_getRequestParameter('save') !== null;
$clone = util_getRequestParameter('clone') !== null;

if ($id) {
  $t = Tree::get_by_id($id);
  if (!$t) {
    FlashMessage::add('Arborele nu există.');
    util_redirect(util_getWwwRoot());
  }
} else {
  $t = Model::factory('Tree')->create();
}

if ($clone) {
  $newt = $t->_clone();
  Log::info("Cloned tree {$t->id} ({$t->description}), new id {$newt->id}");
  FlashMessage::add('Am clonat arborele.', 'success');
  util_redirect("?id={$newt->id}");
}

if ($save) {
  $t->description = util_getRequestParameter('description');
  $t->status = util_getRequestParameter('status');
  $entryIds = util_getRequestParameter('entryIds');
  $jsonMeanings = util_getRequestParameter('jsonMeanings');
  $meanings = json_decode($jsonMeanings);

  $errors = $t->validate();
  if ($errors) {
    SmartyWrap::assign('errors', $errors);
    $t->setMeanings(Meaning::convertTree($meanings));
  } else {
    $t->save();

    // dissociate the tree from the old entries
    TreeEntry::delete_all_by_treeId($t->id);

    // associate the tree with the new entries
    foreach ($entryIds as $eid) {
      TreeEntry::associate($t->id, $eid);
    }

    Meaning::saveTree($meanings, $t);

    FlashMessage::add('Am salvat arborele.', 'success');
    util_redirect("?id={$t->id}");
  }
} else {
  $t->getMeanings(); // ensure they are loaded
  $entryIds = $t->getEntryIds();
}

// Load the distinct model types for the entries' lexems
$modelTypes = Model::factory('Lexem')
  ->table_alias('l')
  ->select('l.modelType')
  ->distinct()
  ->where_in('entryId', $entryIds)
  ->order_by_asc('modelType')
  ->find_many();

$tags = Model::factory('Tag')->order_by_asc('value')->find_many();

SmartyWrap::assign('t', $t);
SmartyWrap::assign('entryIds', $entryIds);
SmartyWrap::assign('modelTypes', $modelTypes);
// TODO: canEdit if STRUCT_STATUS_IN_PROGRESS) || util_isModerator(PRIV_EDIT)
SmartyWrap::assign('canEdit', true);
SmartyWrap::assign('tags', $tags);
SmartyWrap::assign('statusNames', Tree::$STATUS_NAMES);
SmartyWrap::assign('suggestNoBanner', true);
SmartyWrap::assign('suggestHiddenSearchForm', true);
SmartyWrap::addCss('select2', 'meaningTree', 'textComplete');
SmartyWrap::addJs('select2', 'select2Dev', 'meaningTree', 'textComplete');
SmartyWrap::display('editTree.tpl');

?>
