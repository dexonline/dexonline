<?
require_once("../../phplib/util.php"); 
ini_set('max_execution_time', '3600');
util_assertModerator(PRIV_LOC);
util_assertNotMirror();
debug_off();

$modelType = util_getRequestParameter('modelType');
$modelNumber = util_getRequestParameter('modelNumber');
$deleteButton = util_getRequestParameter('deleteButton');

$model = Model::get("modelType = '{$modelType}' and number = '{$modelNumber}'");
$lexems = Lexem::loadByCanonicalModel($modelType, $modelNumber);

if ($deleteButton) {
  foreach ($lexems as $lexem) {
    $lexem->modelType = 'T';
    $lexem->modelNumber = '1';
    $lexem->restriction = '';
    $lexem->save();
    $lexem->regenerateParadigm();
  }
  $model->delete();
  util_redirect('../admin/index.php');
  exit;
}

RecentLink::createOrUpdate("Ștergere model: {$model}");
smarty_assign('modelType', $modelType);
smarty_assign('modelNumber', $modelNumber);
smarty_assign('lexems', $lexems);
smarty_assign('recentLinks', RecentLink::loadForUser());
smarty_displayWithoutSkin('flex/deleteModel.ihtml');

?>
