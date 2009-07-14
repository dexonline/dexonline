<?php
require_once("../phplib/util.php");
smarty_assign('page_title', 'DEX online - Greseli frecvente in limba romana');
smarty_assign('show_search_box', 0);
smarty_assign('slick_selected', 'corect');

// There may be some actions to perform here
$action = util_getRequestParameter('action');
$id = util_getRequestIntParameter('guideEntryId');
$guideEntry = $id ? GuideEntry::load($id) : new GuideEntry();
if ($action == 'delete') {
  if (!session_userIsModerator()) {
    smarty_assign('confirmationMessage', 'Nu aveți drept de moderator :(');
  } else {
    $guideEntry->status = ST_DELETED;
    $guideEntry->save();
    smarty_assign('confirmationMessage', 'Înregistrarea a fost ștearsă.');
  }
} else if ($action == 'edit') {
  smarty_assign('editableGuideEntryId', $id);
} else if ($action == 'save') {
  $saveButton = util_getRequestParameter('saveButton');
  if (!session_userIsModerator()) {
    smarty_assign('confirmationMessage', 'Nu aveți drept de moderator :(');
  } else if ($saveButton) {
    $guideEntry->correct = util_getRequestParameter('Correct');
    $guideEntry->wrong = util_getRequestParameter('Wrong');
    $guideEntry->comments = util_getRequestParameter('Comments');
    $guideEntry->normalizeAndSave();
    smarty_assign('confirmationMessage', 'Modificare reușită.');
  } else {
    smarty_assign('confirmationMessage', 'Modificare anulată.');
  }
} else if ($action == 'viewAddForm') {
  smarty_assign('isAddFormVisible', TRUE);
} else if ($action == 'saveAdd') {
  $saveButton = util_getRequestParameter('saveButton');
  if (!session_userIsModerator()) {
    smarty_assign('confirmationMessage', 'Nu aveți drept de moderator :(');
  } else if ($saveButton) {
    $guideEntry->correct = util_getRequestParameter('Correct');
    $guideEntry->wrong = util_getRequestParameter('Wrong');
    $guideEntry->comments = util_getRequestParameter('Comments');
    $guideEntry->normalizeAndSave();
    smarty_assign('confirmationMessage', 'Adăugare reușită.');
  } else {
    smarty_assign('confirmationMessage', 'Adăugare anulată.');
  }
}

smarty_assign('guideEntries', GuideEntry::loadAllActive());
smarty_displayCommonPageWithSkin('corect.ihtml');
?>
