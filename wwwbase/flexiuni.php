<?php
require_once '../phplib/Core.php';
User::mustHave(User::PRIV_ADMIN);

$saveButton = Request::has('saveButton');
$inflectionIds = Request::getArray('inflectionIds');
$newDescription = Request::get('newDescription');
$newModelType = Request::get('newModelType');
$deleteInflectionId = Request::get('deleteInflectionId');

if ($deleteInflectionId) {
  $infl = Inflection::get_by_id($deleteInflectionId);
  Log::warning("Deleting inflection {$infl->id} ({$infl->description})");
  $infl->delete();
  Util::redirect('flexiuni');
}

if ($saveButton) {
  // Re-rank the inflections according to the order in $inflectionIds
  $modelTypeMap = [];
  foreach ($inflectionIds as $inflId) {
    $infl = Inflection::get_by_id($inflId);
    $rank = array_key_exists($infl->modelType, $modelTypeMap) ? $modelTypeMap[$infl->modelType] + 1 : 1;
    $modelTypeMap[$infl->modelType] = $rank;
    $infl->rank = $rank;
    $infl->save();
  }
  Log::info('Reordered inflections');

  // Add a new inflection if one is given
  if ($newDescription) {
    $infl = Model::factory('Inflection')->create();
    $infl->description = $newDescription;
    $infl->modelType = $newModelType;
    $infl->rank = $modelTypeMap[$newModelType] + 1;
    $infl->save();
    Log::info("Created inflection {$infl->id} ({$infl->description})");
  }

  Util::redirect('flexiuni');
}

// Tag inflections which can be safely deleted (only those that aren't being used by any model)
$inflections = Model::factory('Inflection')->order_by_asc('modelType')->order_by_asc('rank')->find_many();
$usedInflectionIds = DB::getArray('select distinct inflectionId from ModelDescription');
foreach ($inflections as $infl) {
  $infl->canDelete = !in_array($infl->id, $usedInflectionIds);
}

SmartyWrap::assign('inflections', $inflections);
SmartyWrap::assign('modelTypes', ModelType::loadCanonical());
SmartyWrap::addCss('admin');
SmartyWrap::addJs('jqTableDnd');
SmartyWrap::display('flexiuni.tpl');
