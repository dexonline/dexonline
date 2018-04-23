<?php

require_once("../phplib/Core.php");

define('LEXEMES_LIMIT', 100);

$model = Request::get('model');

$model = FlexModel::loadCanonical($model);

if (!$model) {
  FlashMessage::add('Date incorecte.');
  Util::redirect('scrabble');
}

$exponent = getExponent($model);
$lexemes = Lexeme::loadByCanonicalModel($model->modelType, $model->number, LEXEMES_LIMIT);

SmartyWrap::addCss('paradigm');
SmartyWrap::assign('model', $model);
SmartyWrap::assign('exponent', $exponent);
SmartyWrap::assign('lexemes', $lexemes);
SmartyWrap::display('model-flexiune.tpl');

/*************************************************************************/

/**
 * Returns a lexeme for a given word and model. Creates one if one doesn't exist.
 **/
function getExponent($model) {
  // Load by canonical model, so if $modelType is V, look for a lexeme with type V or VT.
  $l = Model::factory('Lexeme')
     ->table_alias('l')
     ->select('l.*')
     ->join('ModelType', 'modelType = code', 'mt')
     ->where('mt.canonical', $model->modelType)
     ->where('l.modelNumber', $model->number)
     ->where('l.form', $model->exponent)
     ->find_one();
  if ($l) {
    $l->loadInflectedFormMap();
  } else {
    $l = Lexeme::create($model->exponent, $model->modelType, $model->number);
    $l->setAnimate(true);
    $l->generateInflectedFormMap();
  }
  return $l;
}
