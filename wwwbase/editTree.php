<?php
require_once("../phplib/util.php");

util_assertModerator(PRIV_EDIT | PRIV_STRUCT);

$id = util_getRequestParameter('id');
$saveButton = util_getBoolean('saveButton');
$clone = util_getBoolean('clone');

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

if ($saveButton) {
  $t->description = util_getRequestParameter('description');
  $t->status = util_getRequestParameter('status');
  $entryIds = util_getRequestParameter('entryIds');
  $jsonMeanings = util_getRequestParameter('jsonMeanings');
  $meanings = json_decode($jsonMeanings);

  $errors = $t->validate();
  if ($errors) {
    FlashMessage::add('Nu pot salva arborele datorită erorilor de mai jos.');
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
  RecentLink::add("Arbore: {$t->description} (ID={$t->id})");
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

$relatedMeanings = Model::factory('Meaning')
                 ->table_alias('m')
                 ->select('m.*')
                 ->select('r.type', 'relationType')
                 ->join('Relation', ['m.id', '=', 'r.meaningId'], 'r')
                 ->where('r.treeId', $t->id)
                 ->find_many();
foreach ($relatedMeanings as $m) {
  $m->getTree(); // preload it
}


SmartyWrap::assign('t', $t);
SmartyWrap::assign('entryIds', $entryIds);
SmartyWrap::assign('modelTypes', $modelTypes);
// TODO: canEdit if STRUCT_STATUS_IN_PROGRESS) || util_isModerator(PRIV_EDIT)
SmartyWrap::assign('canEdit', true);
SmartyWrap::assign('tags', $tags);
SmartyWrap::assign('relatedMeanings', $relatedMeanings);
SmartyWrap::assign('statusNames', Tree::$STATUS_NAMES);
SmartyWrap::addCss('meaningTree', 'textComplete', 'admin');
SmartyWrap::addJs('select2Dev', 'meaningTree', 'textComplete');
SmartyWrap::display('editTree.tpl');

?>
