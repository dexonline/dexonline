<?
require_once("../phplib/util.php"); 
setlocale(LC_ALL, "ro_RO");
debug_off();

$locVersion = util_getRequestParameter('locVersion');
$modelType = util_getRequestParameter('modelType');
$modelNumber = util_getRequestParameter('modelNumber');

$locVersions = array_reverse(pref_getFrozenLocVersions());

if ($locVersion && $modelType && $modelNumber) {
  smarty_assign('selectedLocVersion', $locVersion);
  smarty_assign('selectedModelType', $modelType);
  smarty_assign('selectedModelNumber', $modelNumber);

  $lv = new LocVersion();
  $lv->name = $locVersion;
  $dbName = pref_getLocPrefix() . $lv->getDbName();
  db_changeDatabase($dbName);

  if ($modelNumber == -1) {
    $modelsToDisplay = Model::loadByType($modelType);
  } else {
    $modelsToDisplay = array(Model::get("modelType = '{$modelType}' and number= '{$modelNumber}'"));
  }
  $lexems = array();
  $paradigms = array();

  foreach ($modelsToDisplay as $m) {
    // Load by canonical model, so if $modelType is V, look for a lexem with type V or VT.
    $slashExponent = addslashes($m->exponent);
    $dbResult = db_execute("select Lexem.* from Lexem, ModelType where modelType = code and canonical = '{$modelType}' " .
                           "and modelNumber = '{$m->number}' and form = '{$slashExponent}' limit 1");
    $tmpLexems = db_getObjects(new Lexem(), $dbResult);
    $l = count($tmpLexems) ? $tmpLexems[0] : null;

    if ($l) {
      $paradigm = InflectedForm::loadByLexemIdMapByInflectionId($l->id);
    } else {
      $l = new Lexem($m->exponent, $modelType, $m->number, '');
      $l->isLoc = true;
      $ifArray = $l->generateParadigm();
      $paradigm = InflectedForm::mapByInflectionId($ifArray);
    }
    $lexems[] = $l;
    $paradigms[] = $paradigm;
  }
  
  smarty_assign('modelsToDisplay', $modelsToDisplay);
  smarty_assign('lexems', $lexems);
  smarty_assign('paradigms', $paradigms);
} else {
  $dbName = pref_getLocPrefix() . $locVersions[0]->getDbName();
  db_changeDatabase($dbName);
}

$modelTypes = ModelType::loadCanonical();
$models = Model::loadByType($modelType ? $modelType : $modelTypes[0]->code);

smarty_assign('locVersions', $locVersions);
smarty_assign('modelTypes', $modelTypes);
smarty_assign('models', $models);
smarty_displayCommonPageWithSkin('modele-flexiune.ihtml');

?>
