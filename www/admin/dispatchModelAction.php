<?php
require_once '../../phplib/Core.php'; 
User::mustHave(User::PRIV_EDIT);

$modelType = Request::get('modelType');
$modelNumber = Request::get('modelNumber');
$showLexemesButton = Request::has('showLexemes');
$editModelButton = Request::has('editModel');
$cloneModelButton = Request::has('cloneModel');
$deleteModelButton = Request::has('deleteModel');

$args = sprintf("modelType=%s&modelNumber=%s",
                urlencode($modelType),
                urlencode($modelNumber));

if ($showLexemesButton) {
  Util::redirect("viewLexemesByModel.php?$args");
} else if ($editModelButton) {
  $modelType = ModelType::canonicalize($modelType);
  $m = FlexModel::get_by_modelType_number($modelType, $modelNumber);
  Util::redirect("editModel.php?id={$m->id}");
} else if ($cloneModelButton) {
  Util::redirect("cloneModel.php?$args");
} else if ($deleteModelButton) {
  Util::redirect("deleteModel.php?$args");
}
