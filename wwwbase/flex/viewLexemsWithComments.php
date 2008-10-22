<?
require_once("../../phplib/util.php"); 
util_assertModeratorStatus();
util_assertNotMirror();

RecentLink::createOrUpdate('Lexeme cu comentarii');

smarty_assign('sectionTitle', 'Lexeme cu comentarii');
smarty_assign('recentLinks', RecentLink::loadForUser());
smarty_assign('lexems', Lexem::loadHavingComments());
smarty_displayWithoutSkin('flex/viewLexemsWithComments.ihtml');

?>
