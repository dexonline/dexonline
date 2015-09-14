<?php
require_once("../../phplib/util.php"); 
ini_set('memory_limit', '256M');

$modelType = util_getRequestParameter('modelType');
$modelNumber = util_getRequestParameter('modelNumber');
$lexems = Lexem::loadByCanonicalModel($modelType, $modelNumber);
RecentLink::createOrUpdate("Model: $modelType$modelNumber");

SmartyWrap::assign('lexems', $lexems);
SmartyWrap::assign('sectionTitle', "Lexeme pentru modelul $modelType$modelNumber");
SmartyWrap::assign('sectionCount', count($lexems));
SmartyWrap::assign('recentLinks', RecentLink::loadForUser());
SmartyWrap::displayAdminPage('admin/lexemList.tpl');

?>
