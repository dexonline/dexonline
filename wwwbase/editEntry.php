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
  $lexemIds = util_getRequestParameter('lexemIds');

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

    FlashMessage::add('Am salvat intrarea.', 'success');
    util_redirect("?id={$e->id}");
  }
} else {
  // Viewing the page, not saving
  $lexemIds = $e->getLexemIds();
}

SmartyWrap::assign('e', $e);
SmartyWrap::assign('lexemIds', $lexemIds);
SmartyWrap::assign('suggestNoBanner', true);
SmartyWrap::assign('suggestHiddenSearchForm', true);
SmartyWrap::addCss('bootstrap', 'select2');
SmartyWrap::addJs('bootstrap', 'select2', 'select2Dev');
SmartyWrap::display('editEntry.tpl');

?>
