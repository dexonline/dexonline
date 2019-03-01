<?php

require_once '../lib/Core.php';

const LEXEMES_LIMIT = 100;

$model = Request::get('model');

$model = FlexModel::loadCanonical($model);

if (!$model) {
  FlashMessage::add('Date incorecte.');
  Util::redirectToRoute('games/scrabble');
}

$exponent = $model->getExponentWithParadigm();
$lexemes = Lexeme::loadByCanonicalModel($model->modelType, $model->number, LEXEMES_LIMIT);

Smart::addResources('paradigm');
Smart::assign([
  'model' => $model,
  'exponent' => $exponent,
  'lexemes' => $lexemes,
]);
Smart::display('model-flexiune.tpl');
