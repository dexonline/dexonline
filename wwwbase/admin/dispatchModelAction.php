<?php
require_once("../../phplib/util.php"); 
util_assertModerator(PRIV_LOC);
util_assertNotMirror();

$modelType = util_getRequestParameter('modelType');
$modelNumber = util_getRequestParameter('modelNumber');
$showLexemsButton = util_getRequestParameter('showLexems');
$editModelButton = util_getRequestParameter('editModel');
$cloneModelButton = util_getRequestParameter('cloneModel');
$deleteModelButton = util_getRequestParameter('deleteModel');

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
