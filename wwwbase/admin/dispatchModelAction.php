<?php
require_once("../../phplib/util.php"); 
User::require(User::PRIV_EDIT);
util_assertNotMirror();

$modelType = Request::get('modelType');
$modelNumber = Request::get('modelNumber');
$showLexemsButton = Request::has('showLexems');
$editModelButton = Request::has('editModel');
$cloneModelButton = Request::has('cloneModel');
$deleteModelButton = Request::has('deleteModel');

$args = sprintf("modelType=%s&modelNumber=%s",
                urlencode($modelType),
                urlencode($modelNumber));

if ($showLexemsButton) {
  util_redirect("viewLexemsByModel.php?$args");
} else if ($editModelButton) {
  $modelType = ModelType::canonicalize($modelType);
  $m = FlexModel::get_by_modelType_number($modelType, $modelNumber);
  util_redirect("editModel.php?id={$m->id}");
} else if ($cloneModelButton) {
  util_redirect("cloneModel.php?$args");
} else if ($deleteModelButton) {
  util_redirect("deleteModel.php?$args");
}

?>
