<?php

require_once '../lib/Core.php';

const LEXEMES_LIMIT = 100;

$model = Request::get('model');

$model = FlexModel::loadCanonical($model);

if (!$model) {
  FlashMessage::add('Date incorecte.');
  Util::redirect('scrabble');
}

$exponent = $model->getExponentWithParadigm();
$lexemes = Lexeme::loadByCanonicalModel($model->modelType, $model->number, LEXEMES_LIMIT);

Smart::addCss('paradigm');
Smart::assign('model', $model);
Smart::assign('exponent', $exponent);
Smart::assign('lexemes', $lexemes);
Smart::display('model-flexiune.tpl');
