<?php
require_once("../../phplib/util.php");
util_assertModerator(PRIV_LOC);

$submitButton = util_getRequestParameter('submitButton');
$inflectionIds = util_getRequestParameter('inflectionIds');
$newDescription = util_getRequestParameter('newDescription');
$newModelType = util_getRequestParameter('newModelType');
$deleteInflectionId = util_getRequestParameter('deleteInflectionId');

if ($deleteInflectionId) {
  $infl = Inflection::get_by_id($deleteInflectionId);
  $infl->delete();
  util_redirect('flexiuni');
}

if ($submitButton) {
  // Re-rank the inflections according to the order in $inflectionIds
  $modelTypeMap = array();
  foreach ($inflectionIds as $inflId) {
    $infl = Inflection::get_by_id($inflId);
    $rank = array_key_exists($infl->modelType, $modelTypeMap) ? $modelTypeMap[$infl->modelType] + 1 : 1;
    $modelTypeMap[$infl->modelType] = $rank;
    $infl->rank = $rank;
    $infl->save();
  }

  // Add a new inflection if one is given
  if ($newDescription) {
    $infl = Model::factory('Inflection')->create();
    $infl->description = $newDescription;
    $infl->modelType = $newModelType;
    $infl->rank = $modelTypeMap[$newModelType] + 1;
    $infl->save();
  }

  util_redirect('flexiuni');
}

// Tag inflections which can be safely deleted (only those that aren't being used by any model)
$inflections = Model::factory('Inflection')->order_by_asc('modelType')->order_by_asc('rank')->find_many();
$usedInflectionIds = db_getArray('select distinct inflectionId from ModelDescription');
foreach ($inflections as $infl) {
  $infl->canDelete = !in_array($infl->id, $usedInflectionIds);
}

SmartyWrap::assign('page_title', 'Editare flexiuni');
SmartyWrap::assign('suggestHiddenSearchForm', true);
SmartyWrap::assign('inflections', $inflections);
SmartyWrap::assign('modelTypes', ModelType::loadCanonical());
SmartyWrap::addJs('jqTableDnd');
SmartyWrap::displayCommonPageWithSkin('editor-modele/flexiuni.ihtml');

?>
