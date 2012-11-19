<?php
require_once("../../phplib/util.php"); 
ini_set('max_execution_time', '3600');
util_assertModerator(PRIV_LOC);
util_assertNotMirror();
DebugInfo::disable();

$modelType = util_getRequestParameter('modelType');
$modelNumber = util_getRequestParameter('modelNumber');
$deleteButton = util_getRequestParameter('deleteButton');

$model = Model::factory('FlexModel')->where('modelType', $modelType)->where('number', $modelNumber)->find_one();
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
}

RecentLink::createOrUpdate("Ștergere model: {$model}");
SmartyWrap::assign('modelType', $modelType);
SmartyWrap::assign('modelNumber', $modelNumber);
SmartyWrap::assign('lexems', $lexems);
SmartyWrap::assign('recentLinks', RecentLink::loadForUser());
SmartyWrap::assign('sectionTitle', "Ștergere model {$modelType}{$modelNumber}");
SmartyWrap::displayAdminPage('flex/deleteModel.ihtml');

?>
