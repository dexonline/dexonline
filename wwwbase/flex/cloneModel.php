<?
require_once("../../phplib/util.php"); 
ini_set('max_execution_time', '3600');
util_assertFlexModeratorStatus();
util_assertNotMirror();
debug_off();

$modelType = util_getRequestParameter('modelType');
$modelNumber = util_getRequestParameter('modelNumber');
$newModelNumber = util_getRequestParameter('newModelNumber');
$chooseLexems = util_getRequestParameter('chooseLexems');
$lexemIds = util_getRequestParameter('lexemId');
$cloneButton = util_getRequestParameter('cloneButton');

$errorMessages = array();

if ($cloneButton) {
  // Disallow duplicate model numbers
  $m = Model::loadCanonicalByTypeNumber($modelType, $newModelNumber);
  if ($m) {
    $errorMessages[] = "Modelul $modelType$newModelNumber există deja.";
  }
  if (!$newModelNumber) {
    $errorMessages[] = "Numărul modelului nu poate fi vid.";
  }

  if (!count($errorMessages)) {
    // Clone the model
    $model = Model::get("modelType = '{$modelType}' and number = '{$modelNumber}'");
    $cloneModel = new Model($modelType, $newModelNumber, "Clonat după $modelType$modelNumber", $model->exponent);
    $cloneModel->save();

    // Clone the model descriptions
    $mds = db_find(new ModelDescription(), "modelId = '{$model->id}' order by inflectionId, variant, applOrder");
    foreach ($mds as $md) {
      $newMd = new ModelDescription($md);
      $newMd->modelId = $cloneModel->id;
      $newMd->save();
    }

    // Clone the participle model
    if ($modelType == 'V') {
      $pm = ParticipleModel::loadByVerbModel($modelNumber);
      $clonePm = new ParticipleModel();
      $clonePm->verbModel = $newModelNumber;
      $clonePm->adjectiveModel = $pm->adjectiveModel;
      $clonePm->save();
    }

    // Migrate the selected lexems.
    if ($chooseLexems && $lexemIds) {
      foreach ($lexemIds as $lexemId) {
        $l = Lexem::load($lexemId);
        $l->modelNumber = $newModelNumber;
        $l->save();
        // It is not necessary to regenerate the paradigm for now, since
        // the inflected forms are identical.
      }
    }
    util_redirect('../admin/index.php');
    exit;
  }
} else {
  $newModelNumber = $modelNumber . ".1";
}

$lexems = Lexem::loadByCanonicalModel($modelType, $modelNumber);

smarty_assign('modelType', $modelType);
smarty_assign('modelNumber', $modelNumber);
smarty_assign('newModelNumber', $newModelNumber);
smarty_assign('lexems', $lexems);
smarty_assign('errorMessage', $errorMessages);
smarty_assign('recentLinks', RecentLink::loadForUser());
smarty_displayWithoutSkin('flex/cloneModel.ihtml');

?>
