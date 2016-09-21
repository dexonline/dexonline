<?php
require_once("../phplib/util.php"); 

$locVersion = Request::get('locVersion');
$modelType = Request::get('modelType');

$modelType = ModelType::get_by_code($modelType); // Use the ModelType object from this point on

if (!$locVersion || !$modelType) {
  FlashMessage::add('Date incorecte.');
  util_redirect('scrabble');
}

LocVersion::changeDatabase($locVersion);
$models = FlexModel::loadByType($modelType->code);

$lexems = [];
foreach ($models as $m) {
  $lexems[] = getLexem($m->exponent, $modelType->code, $m->number);
}
  
SmartyWrap::addCss('paradigm');
SmartyWrap::assign('models', $models);
SmartyWrap::assign('lexems', $lexems);
SmartyWrap::assign('locVersion', $locVersion);
SmartyWrap::assign('modelType', $modelType);
SmartyWrap::display('modele-flexiune.tpl');

/*************************************************************************/

/**
 * Returns a lexem for a given word and model. Creates one if one doesn't exist.
 **/
function getLexem($form, $modelType, $modelNumber) {
  // Load by canonical model, so if $modelType is V, look for a lexem with type V or VT.
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
    $l = Lexem::create($form, $modelType, $modelNumber);
    $l->setAnimate(true);
    $l->generateInflectedFormMap();
  }
  return $l;
}

?>
