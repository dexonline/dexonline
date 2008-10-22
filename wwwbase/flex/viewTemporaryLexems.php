<?
require_once("../../phplib/util.php"); 
util_assertModeratorStatus();
util_assertNotMirror();

RecentLink::createOrUpdate('Lexeme neetichetate');

smarty_assign('sectionTitle', 'Lexeme neetichetate');
smarty_assign('recentLinks', RecentLink::loadForUser());
smarty_assign('lexems', Lexem::loadTemporary());
smarty_displayWithoutSkin('admin/lexemList.ihtml');

?>
