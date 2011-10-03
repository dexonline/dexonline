<?php
require_once("../../phplib/util.php"); 

$modelType = util_getRequestParameter('modelType');
$modelNumber = util_getRequestParameter('modelNumber');
$lexems = Lexem::loadByCanonicalModel($modelType, $modelNumber);

RecentLink::createOrUpdate("Model: $modelType$modelNumber");

smarty_assign('lexems', $lexems);
smarty_assign('sectionTitle', "Lexeme pentru modelul $modelType$modelNumber");
smarty_assign('recentLinks', RecentLink::loadForUser());
smarty_displayWithoutSkin('admin/lexemList.ihtml');

?>
