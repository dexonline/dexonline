<?php
require_once("../../phplib/Core.php"); 
ini_set('max_execution_time', '3600');
ini_set('memory_limit','256M');
User::mustHave(User::PRIV_LOC);
Util::assertNotMirror();
DebugInfo::disable();

$modelType = Request::get('modelType');
$modelNumber = Request::get('modelNumber');
$newModelNumber = Request::get('newModelNumber');
$lexemIds = Request::getArray('lexemId');
$saveButton = Request::has('saveButton');

if ($saveButton) {
  // Disallow duplicate model numbers
  $m = FlexModel::loadCanonicalByTypeNumber($modelType, $newModelNumber);
  if ($m) {
    FlashMessage::add("Modelul $modelType$newModelNumber există deja.");
  }
  if (!$newModelNumber) {
    FlashMessage::add('Numărul modelului nu poate fi vid.');
  }

  if (!FlashMessage::hasErrors()) {
    // Clone the model
    $model = Model::factory('FlexModel')
           ->where('modelType', $modelType)
           ->where('number', $modelNumber)
           ->find_one();
    $cloneModel = FlexModel::create($modelType, $newModelNumber,
                                    "Clonat după $modelType$modelNumber", $model->exponent);
    $cloneModel->save();

    Log::notice("Cloning model {$model->id} ({$model}) as {$cloneModel->id} ({$cloneModel})");
    FlashMessage::add('Am clonat modelul.', 'success');

    // Clone the model descriptions
    $mds = Model::factory('ModelDescription')
         ->where('modelId', $model->id)
         ->order_by_asc('inflectionId')
         ->order_by_asc('variant')
         ->order_by_asc('applOrder')
         ->find_many();
    foreach ($mds as $md) {
      $newMd = $md->parisClone();
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
    foreach ($lexemIds as $lexemId) {
      $l = Lexem::get_by_id($lexemId);
      $l->modelNumber = $newModelNumber;
      $l->save();
      // It is not necessary to regenerate the paradigm for now, since
      // the inflected forms are identical.
    }
    Util::redirect("editModel.php?id={$cloneModel->id}");
  }
} else {
  $newModelNumber = $modelNumber . '.1';
}

$lexems = Lexem::loadByCanonicalModel($modelType, $modelNumber);

SmartyWrap::assign('modelType', $modelType);
SmartyWrap::assign('modelNumber', $modelNumber);
SmartyWrap::assign('newModelNumber', $newModelNumber);
SmartyWrap::assign('lexems', $lexems);
SmartyWrap::addCss('admin');
SmartyWrap::display('admin/cloneModel.tpl');

?>
