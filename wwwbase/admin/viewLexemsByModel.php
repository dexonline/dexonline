<?php
require_once("../../phplib/util.php"); 
ini_set('memory_limit', '256M');

$modelType = util_getRequestParameter('modelType');
$modelNumber = util_getRequestParameter('modelNumber');
$lexems = Lexem::loadByCanonicalModel($modelType, $modelNumber);
RecentLink::createOrUpdate("Model: $modelType$modelNumber");

SmartyWrap::assign('lexems', $lexems);
SmartyWrap::assign('modelType', $modelType);
SmartyWrap::assign('modelNumber', $modelNumber);
SmartyWrap::assign('recentLinks', RecentLink::loadForUser());
SmartyWrap::displayAdminPage('admin/viewLexemsByModel.tpl');

?>
