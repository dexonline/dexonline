<?php
require_once("../../phplib/util.php"); 
ini_set('max_execution_time', '3600');
ini_set('memory_limit','256M');
util_assertModerator(PRIV_LOC);
util_assertNotMirror();
DebugInfo::disable();

$modelType = util_getRequestParameter('modelType');
$modelNumber = util_getRequestParameter('modelNumber');
$newModelNumber = util_getRequestParameter('newModelNumber');
$chooseLexems = util_getRequestParameter('chooseLexems');
$lexemModelIds = util_getRequestParameter('lexemModelId');
$cloneButton = util_getRequestParameter('cloneButton');

if ($cloneButton) {
  // Disallow duplicate model numbers
  $m = FlexModel::loadCanonicalByTypeNumber($modelType, $newModelNumber);
  if ($m) {
    FlashMessage::add("Modelul $modelType$newModelNumber există deja.");
  }
  if (!$newModelNumber) {
    FlashMessage::add('Numărul modelului nu poate fi vid.');
  }

  if (!FlashMessage::hasMessages()) {
    // Clone the model
    $model = Model::factory('FlexModel')->where('modelType', $modelType)->where('number', $modelNumber)->find_one();
    $cloneModel = FlexModel::create($modelType, $newModelNumber, "Clonat după $modelType$modelNumber", $model->exponent);
    $cloneModel->save();

    // Clone the model descriptions
    $mds = Model::factory('ModelDescription')->where('modelId', $model->id)->order_by_asc('inflectionId')
      ->order_by_asc('variant')->order_by_asc('applOrder')->find_many();
    foreach ($mds as $md) {
      $newMd = Model::factory('ModelDescription')->create();
      $newMd->copyFrom($md);
      $newMd->modelId = $cloneModel->id;
      $newMd->save();
    }

    // Clone the participle model
    if ($modelType == 'V') {
      $pm = ParticipleModel::loadByVerbModel($modelNumber);
      $clonePm = Model::factory('ParticipleModel')->create();
      $clonePm->verbModel = $newModelNumber;
      $clonePm->adjectiveModel = $pm->adjectiveModel;
      $clonePm->save();
    }

    // Migrate the selected lexems.
    if ($chooseLexems && $lexemModelIds) {
      foreach ($lexemModelIds as $lexemModelId) {
        $lm = LexemModel::get_by_id($lexemModelId);
        $lm->modelNumber = $newModelNumber;
        $lm->save();
        // It is not necessary to regenerate the paradigm for now, since
        // the inflected forms are identical.
      }
    }
    util_redirect('../admin/index.php');
    exit;
  }
} else {
  $newModelNumber = $modelNumber . '.1';
}

$lexemModels = LexemModel::loadByCanonicalModel($modelType, $modelNumber);

SmartyWrap::assign('modelType', $modelType);
SmartyWrap::assign('modelNumber', $modelNumber);
SmartyWrap::assign('newModelNumber', $newModelNumber);
SmartyWrap::assign('lexemModels', $lexemModels);
SmartyWrap::assign('recentLinks', RecentLink::loadForUser());
SmartyWrap::displayAdminPage('admin/cloneModel.tpl');

?>
