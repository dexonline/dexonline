<?php
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
  Util::redirect(Router::link('model/listLexemes') . "?$args");
} else if ($editModelButton) {
  $modelType = ModelType::canonicalize($modelType);
  $m = FlexModel::get_by_modelType_number($modelType, $modelNumber);
  Util::redirect(Router::link('model/edit') . "?id={$m->id}");
} else if ($cloneModelButton) {
  Util::redirect(Router::link('model/clone') . "?$args");
} else if ($deleteModelButton) {
  Util::redirect(Router::link('model/delete') . "?$args");
}
