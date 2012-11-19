<?php
require_once("../../phplib/util.php"); 
ini_set('memory_limit', '256M');

$modelType = util_getRequestParameter('modelType');
$modelNumber = util_getRequestParameter('modelNumber');
$lexems = Lexem::loadByCanonicalModel($modelType, $modelNumber);
RecentLink::createOrUpdate("Model: $modelType$modelNumber");

smarty_assign('lexems', $lexems);
smarty_assign('sectionTitle', "Lexeme pentru modelul $modelType$modelNumber");
smarty_assign('sectionCount', count($lexems));
smarty_assign('recentLinks', RecentLink::loadForUser());
smarty_displayAdminPage('admin/lexemList.ihtml');

?>
