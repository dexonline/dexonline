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

SmartyWrap::addCss('paradigm');
SmartyWrap::assign('model', $model);
SmartyWrap::assign('exponent', $exponent);
SmartyWrap::assign('lexemes', $lexemes);
SmartyWrap::display('model-flexiune.tpl');
