<?php
User::mustHave(User::PRIV_EDIT);

$modelType = Request::get('modelType');
$modelNumber = Request::get('modelNumber');
$showLexemesButton = Request::has('showLexemes');
$goToModelButton = Request::has('goToModel');
$cloneModelButton = Request::has('cloneModel');
$deleteModelButton = Request::has('deleteModel');

$args = sprintf("modelType=%s&modelNumber=%s",
                urlencode($modelType),
                urlencode($modelNumber));

if ($showLexemesButton) {
  Util::redirect(Router::link('model/listLexemes') . "?$args");
} else if ($goToModelButton) {
  $modelType = ModelType::canonicalize($modelType);
  $m = FlexModel::get_by_modelType_number($modelType, $modelNumber);
  Util::redirect(Router::link('model/view') . "/{$m}");
} else if ($cloneModelButton) {
  Util::redirect(Router::link('model/clone') . "?$args");
} else if ($deleteModelButton) {
  Util::redirect(Router::link('model/delete') . "?$args");
}
