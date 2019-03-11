<?php

$modelType = Request::get('modelType');

$modelType = ModelType::get_by_code($modelType); // Use the ModelType object from this point on

if (!$modelType) {
  FlashMessage::add('Date incorecte.');
  Util::redirectToRoute('games/scrabble');
}
$models = FlexModel::loadByType($modelType->code);

$lexemes = [];
foreach ($models as $m) {
  $lexemes[] = $m->getExponentWithParadigm();
}

Smart::addResources('paradigm');
Smart::assign([
  'models' => $models,
  'lexemes' => $lexemes,
  'modelType' => $modelType,
]);
Smart::display('model/list.tpl');
