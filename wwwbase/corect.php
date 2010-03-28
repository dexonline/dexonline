<?php
require_once("../phplib/util.php");
smarty_assign('page_title', 'DEX online - Ghid de exprimare');
smarty_assign('show_search_box', 0);
smarty_assign('slick_selected', 'corect');

// There may be some actions to perform here
$action = util_getRequestParameter('action');
$id = util_getRequestIntParameter('guideEntryId');
$guideEntry = new GuideEntry();
$guideEntry->status = ST_ACTIVE;
if ($id) {
  $guideEntry->load("id={$id}");
}
if ($action == 'delete') {
  if (!session_userIsModerator()) {
    session_setFlash('Nu aveți drept de moderator.');
  } else {
    $guideEntry->status = ST_DELETED;
    $guideEntry->save();
    session_setFlash('Înregistrarea a fost ștearsă.', 'info');
    util_redirect('corect.php');
  }
} else if ($action == 'edit') {
  smarty_assign('editableGuideEntryId', $id);
} else if ($action == 'save') {
  $saveButton = util_getRequestParameter('saveButton');
  if (!session_userIsModerator()) {
    session_setFlash('Nu aveți drept de moderator.');
  } else if ($saveButton) {
    $guideEntry->correct = util_getRequestParameter('Correct');
    $guideEntry->wrong = util_getRequestParameter('Wrong');
    $guideEntry->comments = util_getRequestParameter('Comments');
    $guideEntry->normalize();
    $guideEntry->save();
    session_setFlash('Modificare reușită.', 'info');
  } else {
    session_setFlash('Modificare anulată.');
  }
} else if ($action == 'viewAddForm') {
  smarty_assign('isAddFormVisible', TRUE);
} else if ($action == 'saveAdd') {
  $saveButton = util_getRequestParameter('saveButton');
  if (!session_userIsModerator()) {
    session_setFlash('Nu aveți drept de moderator.');
  } else if ($saveButton) {
    $guideEntry->correct = util_getRequestParameter('Correct');
    $guideEntry->wrong = util_getRequestParameter('Wrong');
    $guideEntry->comments = util_getRequestParameter('Comments');
    $guideEntry->normalize();
    $guideEntry->save();
    session_setFlash('Adăugare reușită.', 'info');
  } else {
    session_setFlash('Adăugare anulată.');
  }
}

smarty_assign('guideEntries', db_find(new GuideEntry(), "status = 0"));
smarty_displayCommonPageWithSkin('corect.ihtml');
?>
