<?php
require_once("../phplib/util.php");
util_assertModerator(PRIV_ADMIN);

$submitButton = util_getRequestParameter('submitButton');
$moveUp = util_getRequestParameter('moveUp');
$moveDown = util_getRequestParameter('moveDown');
$ids = util_getRequestParameter('ids');

if ($moveUp) {
  switchSources($moveUp, $moveUp - 1);
  util_redirect('surse');
}

if ($moveDown) {
  switchSources($moveDown, $moveDown + 1);
  util_redirect('surse');
}

if ($submitButton) {
  foreach($ids as $id) {
    $src = ($id == 'new') ? new Source : Source::get("id = {$id}");
    $src->name = util_getRequestParameter("name_{$id}");
    $src->shortName = util_getRequestParameter("shortName_{$id}");
    $src->urlName = util_getRequestParameter("urlName_{$id}");
    $src->author = util_getRequestParameter("author_{$id}");
    $src->publisher = util_getRequestParameter("publisher_{$id}");
    $src->year = util_getRequestParameter("year_{$id}");
    $src->canContribute = util_getRequestParameterWithDefault("canContribute_{$id}", 0);
    $src->canModerate = util_getRequestParameterWithDefault("canModerate_{$id}", 0);
    $src->isOfficial = util_getRequestParameterWithDefault("isOfficial_{$id}", 0);
    $src->canDistribute = util_getRequestParameterWithDefault("canDistribute_{$id}", 0);
    if ($src->name) {
      if ($id == 'new') {
        $src->displayOrder = count($ids); // set the highest displayOrder for the new source
      }
      $src->save();
    }
  }
  util_redirect('surse');
}

smarty_assign('page_title', 'Surse');
smarty_displayCommonPageWithSkin('surse.ihtml');

function switchSources($ord1, $ord2) {
  $src1 = Source::get("displayOrder = {$ord1}");
  $src2 = Source::get("displayOrder = {$ord2}");
  if ($src1 && $src2) {
    $src1->displayOrder = $ord2;
    $src2->displayOrder = $ord1;
    $src1->save();
    $src2->save();
  }
}

?>
