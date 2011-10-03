<?php
require_once("../../phplib/util.php"); 
util_assertModerator(PRIV_EDIT);
util_assertNotMirror();

RecentLink::createOrUpdate('Lexeme cu comentarii');

smarty_assign('sectionTitle', 'Lexeme cu comentarii');
smarty_assign('recentLinks', RecentLink::loadForUser());
smarty_assign('lexems', db_find(new Lexem(), "comment != '' order by formNoAccent"));
smarty_displayWithoutSkin('flex/viewLexemsWithComments.ihtml');

?>
