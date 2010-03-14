<?
require_once("../../phplib/util.php"); 
util_assertModeratorStatus();
util_assertNotMirror();

RecentLink::createOrUpdate('Lexeme fără accent');

smarty_assign('sectionTitle', 'Lexeme fără accent');
smarty_assign('recentLinks', RecentLink::loadForUser());
smarty_assign('lexems', db_find(new Lexem(), "form not rlike '\'' and not noAccent"));
smarty_displayWithoutSkin('admin/lexemList.ihtml');

?>
