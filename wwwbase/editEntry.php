<?php
require_once("../phplib/util.php");

util_assertModerator(PRIV_EDIT | PRIV_STRUCT);

$id = util_getRequestParameter('id');
$save = util_getRequestParameter('save') !== null;
$delete = util_getRequestParameter('delete') !== null;

if ($id) {
  $e = Entry::get_by_id($id);
  if (!$e) {
    FlashMessage::add(_('Intrarea nu există.'));
    util_redirect(util_getWwwRoot());
  }
} else {
  $e = Model::factory('Entry')->create();
}

if ($delete) {
  $e->delete();
  FlashMessage::add('Am șters intrarea.', 'success');
  util_redirect(util_getWwwRoot());
}

if ($save) {
  $e->description = util_getRequestParameter('description');

  $errors = $e->validate();
  if ($errors) {
    SmartyWrap::assign('errors', $errors);
  } else {
    $e->save();

    FlashMessage::add('Am salvat intrarea.', 'success');
    util_redirect("?id={$e->id}");
  }
} else {
  // Viewing the page, not saving
}

SmartyWrap::assign('e', $e);
SmartyWrap::assign('suggestNoBanner', true);
SmartyWrap::assign('suggestHiddenSearchForm', true);
SmartyWrap::addCss('bootstrap');
SmartyWrap::addJs('bootstrap');
SmartyWrap::display('editEntry.tpl');

?>
