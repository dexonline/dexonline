<?
require_once("../../phplib/util.php"); 
util_assertModeratorStatus();
util_assertNotMirror();

RecentLink::createOrUpdate('Lexeme ambigue');

smarty_assign('sectionTitle', 'Lexeme ambigue (cu nume şi descriere identice)');
smarty_assign('recentLinks', RecentLink::loadForUser());
smarty_assign('lexems', Lexem::loadAmbiguous());
smarty_displayWithoutSkin('admin/lexemList.ihtml');

?>
