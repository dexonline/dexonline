<?
require_once("../../phplib/util.php"); 
util_assertModerator(PRIV_LOC);
util_assertNotMirror();

$modelType = util_getRequestParameter('modelType');
$modelNumber = util_getRequestParameter('modelNumber');
$showLexemsButton = util_getRequestParameter('showLexems');
$editModelButton = util_getRequestParameter('editModel');
$cloneModelButton = util_getRequestParameter('cloneModel');
$deleteModelButton = util_getRequestParameter('deleteModel');

$args = "modelType=$modelType&modelNumber=$modelNumber";

if ($showLexemsButton) {
  util_redirect("viewLexemsByModel.php?$args");
} else if ($editModelButton) {
  util_redirect("editModel.php?$args");
} else if ($cloneModelButton) {
  util_redirect("cloneModel.php?$args");
} else if ($deleteModelButton) {
  util_redirect("deleteModel.php?$args");
}

?>
