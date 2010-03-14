<?
require_once("../../phplib/util.php"); 
util_assertModeratorStatus();
util_assertNotMirror();

RecentLink::createOrUpdate('Lexeme ambigue');

smarty_assign('sectionTitle', 'Lexeme ambigue (cu nume și descriere identice)');
smarty_assign('recentLinks', RecentLink::loadForUser());
smarty_assign('lexems', db_find(new Lexem(), "description = '' group by form having count(*) > 1"));
smarty_displayWithoutSkin('admin/lexemList.ihtml');

?>
