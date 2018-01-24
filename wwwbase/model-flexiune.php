<?php
require_once("../phplib/Core.php");

$modelType = Request::get('t');
$modelNumber = Request::get('n');

$model = FlexModel::loadCanonicalByTypeNumber($modelType, $modelNumber);

if (!$model) {
    FlashMessage::add('Date incorecte.');
    Util::redirect('scrabble');
}

$lexeme = getLexem($model->exponent, $model->modelType, $model->number);

SmartyWrap::addCss('paradigm');
SmartyWrap::assign('model', $model);
SmartyWrap::assign('lexem', $lexem);
SmartyWrap::display('model-flexiune.tpl');

/*************************************************************************/

/**
 * Returns a lexeme for a given word and model. Creates one if one doesn't exist.
 **/
function getLexem($form, $modelType, $modelNumber) {
    // Load by canonical model, so if $modelType is V, look for a lexeme with type V or VT.
    $l = Model::factory('Lexem')
        ->table_alias('l')
        ->select('l.*')
        ->join('ModelType', 'modelType = code', 'mt')
        ->where('mt.canonical', $modelType)
        ->where('l.modelNumber', $modelNumber)
        ->where('l.form', $form)
        ->limit(1)
        ->find_one();
    if ($l) {
        $l->loadInflectedFormMap();
    } else {
        $l = Lexeme::create($form, $modelType, $modelNumber);
        $l->setAnimate(true);
        $l->generateInflectedFormMap();
    }
    return $l;
}