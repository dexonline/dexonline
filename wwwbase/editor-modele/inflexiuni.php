<?php
require_once("../../phplib/util.php");
util_assertModerator(PRIV_LOC);

$submitButton = util_getRequestParameter('submitButton');
$inflectionIds = util_getRequestParameter('inflectionIds');
$newDescription = util_getRequestParameter('newDescription');
$newModelType = util_getRequestParameter('newModelType');
$deleteInflectionId = util_getRequestParameter('deleteInflectionId');

if ($deleteInflectionId) {
  $infl = Inflection::get("id = {$deleteInflectionId}");
  $infl->delete();
  util_redirect('inflexiuni');
}

if ($submitButton) {
  // Re-rank the inflections according to the order in $inflectionIds
  $modelTypeMap = array();
  foreach ($inflectionIds as $inflId) {
    $infl = Inflection::get("id = {$inflId}");
    $rank = array_key_exists($infl->modelType, $modelTypeMap) ? $modelTypeMap[$infl->modelType] + 1 : 1;
    $modelTypeMap[$infl->modelType] = $rank;
    $infl->rank = $rank;
    $infl->save();
  }

  // Add a new inflection if one is given
  if ($newDescription) {
    $infl = new Inflection();
    $infl->description = $newDescription;
    $infl->modelType = $newModelType;
    $infl->rank = $modelTypeMap[$newModelType] + 1;
    $infl->save();
  }

  util_redirect('inflexiuni');
}

// Tag inflections which can be safely deleted (only those that aren't being used by any model)
$inflections = db_find(new Inflection(), "1 order by modelType, rank");
$usedInflectionIds = db_getArray(db_execute("select distinct inflectionId from ModelDescription"));
foreach ($inflections as $infl) {
  $infl->canDelete = !in_array($infl->id, $usedInflectionIds);
}

smarty_assign('page_title', 'Editare inflexiuni');
smarty_assign('suggestHiddenSearchForm', true);
smarty_assign('inflections', $inflections);
smarty_assign('modelTypes', ModelType::loadCanonical());
smarty_displayCommonPageWithSkin('editor-modele/inflexiuni.ihtml');

?>
