<?php
require_once("../phplib/util.php");

util_assertModerator(PRIV_EDIT | PRIV_STRUCT);

$id = util_getRequestParameter('id');
$save = util_getRequestParameter('save') !== null;
$delete = util_getRequestParameter('delete') !== null;

if ($id) {
  $t = Tree::get_by_id($id);
  if (!$t) {
    FlashMessage::add(_('Arborele nu există.'));
    util_redirect(util_getWwwRoot());
  }
} else {
  $t = Model::factory('Tree')->create();
}

if ($delete) {
  $t->delete();
  FlashMessage::add('Am șters arborele.', 'success');
  Log::warning("Deleted meaning tree {$t->id} ({$t->description})");
  util_redirect(util_getWwwRoot());
}

if ($save) {
  $t->description = util_getRequestParameter('description');
  $entryIds = util_getRequestParameter('entryIds');

  $errors = $t->validate();
  if ($errors) {
    SmartyWrap::assign('errors', $errors);
  } else {
    $t->save();

    // dissociate the tree from the old entries
    TreeEntry::delete_all_by_treeId($t->id);

    // associate the tree with the new entries
    foreach ($entryIds as $eid) {
      TreeEntry::associate($t->id, $eid);
    }

    FlashMessage::add('Am salvat arborele.', 'success');
    util_redirect("?id={$t->id}");
  }
} else {
  $t->getMeanings(); // ensure they are loaded
  $entryIds = $t->getEntryIds();
}

SmartyWrap::assign('t', $t);
SmartyWrap::assign('entryIds', $entryIds);
SmartyWrap::assign('suggestNoBanner', true);
SmartyWrap::assign('suggestHiddenSearchForm', true);
SmartyWrap::addCss('bootstrap', 'select2', 'meaningTree');
SmartyWrap::addJs('bootstrap', 'select2', 'select2Dev', 'meaningTree');
SmartyWrap::display('editTree.tpl');

?>
